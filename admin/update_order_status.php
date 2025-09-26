<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../includes/email_helper.php';
require_once __DIR__ . '/../config/email.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: orders.php');
    exit;
}

$orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$newStatus = isset($_POST['status']) ? trim($_POST['status']) : '';
$notify = isset($_POST['notify']) && $_POST['notify'] == '1';

$database = new Database();
$db = $database->getConnection();
$orderModel = new Order($db);

try {
    $valid = $orderModel->getValidStatuses();
    if (!in_array($newStatus, $valid, true)) {
        throw new InvalidArgumentException('Invalid status');
    }
    $order = $orderModel->findById($orderId);
    if (!$order) {
        throw new RuntimeException('Order not found');
    }

    $orderModel->updateStatus($orderId, $newStatus);

    if ($notify && !empty($order['customer_email'])) {
        $siteName = defined('SITE_NAME') ? SITE_NAME : 'Printer Trooper Inc';
        $subject = "Update on your order {$order['order_number']}: " . ucfirst($newStatus);
        $body = '<div style="font-family:Arial,sans-serif;line-height:1.6;">'
            . '<p>Hello,</p>'
            . '<p>Your order <strong>' . htmlspecialchars($order['order_number']) . '</strong> status has been updated to <strong>' . htmlspecialchars(ucfirst($newStatus)) . '</strong>.</p>'
            . '<p>You can track your order here: <a href="' . htmlspecialchars(get_base_url() . '/track_order.php?order=' . urlencode($order['order_number']) . '&email=' . urlencode($order['customer_email'])) . '">Track Order</a></p>'
            . '<p>— ' . htmlspecialchars($siteName) . '</p>'
            . '</div>';
        @send_email_generic($order['customer_email'], $subject, $body);
    }

    header('Location: orders.php');
} catch (Exception $e) {
    header('Location: orders.php');
}

function get_base_url() {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    return $scheme . '://' . $host . preg_replace('#/admin$#','',$dir);
}

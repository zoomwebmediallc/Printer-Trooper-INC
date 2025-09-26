<?php
require_once 'includes/session.php';
require_once 'config/database.php';
require_once 'models/Cart.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $cart = new Cart($db);
    $cleared = $cart->clearCart(getUserId());
    echo json_encode(['success' => (bool)$cleared]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to clear cart']);
}

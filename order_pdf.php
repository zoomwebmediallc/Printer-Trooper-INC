<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Order.php';
require_once __DIR__ . '/models/User.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();
$userId = getUserId();

$orderNumber = isset($_GET['order']) ? preg_replace('/[^A-Za-z0-9\-]/', '', $_GET['order']) : '';
if (!$orderNumber) {
    http_response_code(400);
    echo 'Missing order parameter';
    exit;
}

// Verify the order belongs to the logged-in user and fetch details
$sql = "SELECT * FROM orders WHERE order_number = :onum AND user_id = :uid LIMIT 1";
$stmt = $db->prepare($sql);
$stmt->execute([':onum' => $orderNumber, ':uid' => $userId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$order) {
    http_response_code(404);
    echo 'Order not found';
    exit;
}

$itemsSql = "SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE order_id = :oid";
$itemsStmt = $db->prepare($itemsSql);
$itemsStmt->execute([':oid' => $order['id']]);
$items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order <?php echo h($order['order_number']); ?> - PDF</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Print-friendly styles */
        .pdf-container { max-width: 800px; margin: 20px auto; background: #fff; padding: 24px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
        .pdf-header { display:flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .pdf-title { font-size: 1.5rem; font-weight: 800; color:#2c3e50; }
        .pdf-meta { color:#6c757d; font-weight:600; }
        .pdf-section { margin-top: 18px; }
        .pdf-section h3 { margin-bottom: 8px; color:#2c3e50; }
        table.pdf-table { width: 100%; border-collapse: collapse; }
        table.pdf-table th, table.pdf-table td { padding: 10px; border-bottom: 1px solid #eee; }
        table.pdf-table th { text-align: left; }
        table.pdf-table td.amount, table.pdf-table th.amount { text-align: right; }
        .totals { margin-top: 10px; text-align: right; font-weight: 800; color:#2c3e50; }
        .actions { margin-top: 16px; display:flex; gap:10px; }
        @media print {
            .actions { display: none; }
            body { background: #fff; }
            .pdf-container { box-shadow: none; border: none; margin: 0; width: 100%; max-width: 100%; border-radius: 0; }
        }
    </style>
</head>
<body>
    <div class="pdf-container">
        <div class="pdf-header">
            <div>
                <div class="pdf-title">Order Summary</div>
                <div class="pdf-meta">Order #: <?php echo h($order['order_number']); ?></div>
            </div>
            <div class="pdf-meta">
                <div>Date: <?php echo h($order['order_date']); ?></div>
                <div>Status: <?php echo h(ucfirst($order['status'])); ?></div>
            </div>
        </div>

        <div class="pdf-section">
            <h3>Billing & Shipping</h3>
            <div><?php echo nl2br(h($order['shipping_address'])); ?></div>
        </div>

        <div class="pdf-section">
            <h3>Items</h3>
            <table class="pdf-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th class="amount">Price</th>
                        <th class="amount">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $subtotal = 0.0; foreach ($items as $it): $line = (float)$it['price'] * (int)$it['quantity']; $subtotal += $line; ?>
                        <tr>
                            <td><?php echo h($it['name']); ?></td>
                            <td><?php echo (int)$it['quantity']; ?></td>
                            <td class="amount">$<?php echo number_format((float)$it['price'], 2); ?></td>
                            <td class="amount">$<?php echo number_format($line, 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="totals">Grand Total: $<?php echo number_format((float)$order['total_amount'], 2); ?></div>
        </div>

        <div class="actions">
            <button class="btn-primary" onclick="window.print()">Print / Save as PDF</button>
            <a class="btn-primary" href="orders.php">Back to Orders</a>
        </div>
    </div>
</body>
</html>

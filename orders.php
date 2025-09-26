<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Order.php';
require_once __DIR__ . '/models/Cart.php';
require_once __DIR__ . '/models/User.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();
$orderModel = new Order($db);
$cart = new Cart($db);
$cart_count = $cart->getCartCount(getUserId());

// Load current user
$userModel = new User($db);
$userId = getUserId();
if (!$userModel->getById($userId)) {
    header('Location: login.php');
    exit;
}

// Fetch orders for this user
$stmt = $db->prepare("SELECT id, order_number, total_amount, status, order_date, customer_email FROM orders WHERE user_id = :uid ORDER BY order_date DESC, id DESC");
$stmt->execute([':uid' => $userId]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Printer Trooper Inc</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="preloader" id="preloader">
        <div class="spinner"></div>
    </div>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <h1 class="page-title">My Orders</h1>

            <?php if (empty($orders)): ?>
                <div class="empty-cart">
                    <h2>No orders yet</h2>
                    <p>When you place an order, it will show up here.</p>
                    <a href="printers.php" class="btn-primary">Shop Now</a>
                </div>
            <?php else: ?>
                <div style="background:#fff;padding:1rem;border-radius:12px;box-shadow:var(--shadow-sm);overflow:auto;">
                    <table style="width:100%;border-collapse:collapse;min-width:720px;">
                        <thead>
                            <tr>
                                <th style="text-align:left;border-bottom:1px solid #eee;padding:10px;">Order #</th>
                                <th style="text-align:left;border-bottom:1px solid #eee;padding:10px;">Date</th>
                                <th style="text-align:right;border-bottom:1px solid #eee;padding:10px;">Total</th>
                                <th style="text-align:left;border-bottom:1px solid #eee;padding:10px;">Status</th>
                                <th style="text-align:right;border-bottom:1px solid #eee;padding:10px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $o): ?>
                                <tr>
                                    <td style="padding:10px;border-bottom:1px solid #f2f2f2;font-weight:600;color:#2c3e50;">
                                        <?php echo h($o['order_number']); ?>
                                    </td>
                                    <td style="padding:10px;border-bottom:1px solid #f2f2f2;">
                                        <?php echo h($o['order_date']); ?>
                                    </td>
                                    <td style="padding:10px;border-bottom:1px solid #f2f2f2;text-align:right;">
                                        $<?php echo number_format((float)$o['total_amount'], 2); ?>
                                    </td>
                                    <td style="padding:10px;border-bottom:1px solid #f2f2f2;">
                                        <?php echo h(ucfirst($o['status'])); ?>
                                    </td>
                                    <td style="padding:10px;border-bottom:1px solid #f2f2f2;text-align:right;">
                                        <a class="btn-primary" style="padding:.5rem 1rem;" href="order_details.php?order=<?php echo urlencode($o['order_number']); ?>">
                                            <i class="fa-solid fa-eye"></i> See details
                                        </a>
                                        <a class="btn-primary" style="padding:.5rem 1rem;margin-left:.5rem;" href="track_order.php?order=<?php echo urlencode($o['order_number']); ?>&email=<?php echo urlencode($o['customer_email']); ?>">
                                            <i class="fa-solid fa-truck"></i> Track
                                        </a>
                                        <a class="btn-primary" style="padding:.5rem 1rem;margin-left:.5rem;" href="order_pdf.php?order=<?php echo urlencode($o['order_number']); ?>" target="_blank">
                                            <i class="fa-solid fa-file-pdf"></i> Generate PDF
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="./assets/js/script.js"></script>
</body>
</html>

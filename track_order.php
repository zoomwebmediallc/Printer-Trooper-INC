<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Order.php';
require_once 'models/Cart.php';

$database = new Database();
$db = $database->getConnection();
$orderModel = new Order($db);
$cart = new Cart($db);
$cart_count = $cart->getCartCount(getUserId());

$order = null;
$message = '';
$orderParam = isset($_GET['order']) ? preg_replace('/[^A-Za-z0-9\-]/', '', $_GET['order']) : '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderNo = preg_replace('/[^A-Za-z0-9\-]/', '', $_POST['order_number'] ?? '');
    $email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL) ?: '';
    if ($orderNo && $email) {
        $order = $orderModel->findByOrderNumberAndEmail($orderNo, $email);
        if (!$order) {
            $message = 'No order found with that combination.';
        }
    } else {
        $message = 'Please enter both Order Number and a valid Email.';
    }
} elseif (!empty($orderParam) && !empty($_GET['email'])) {
    $email = filter_var(trim((string) $_GET['email']), FILTER_VALIDATE_EMAIL) ?: '';
    if ($email) {
        $order = $orderModel->findByOrderNumberAndEmail($orderParam, $email);
        if (!$order) {
            $message = 'No order found with that combination.';
        }
    }
}

function h($v)
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order - Printer Trooper Inc</title>
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
            <h1 class="page-title">Track Your Order</h1>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">
                <form method="POST" action="track_order.php"
                    style="background:white;padding:1.5rem;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);">
                    <div class="form-group">
                        <label for="order_number">Order Number *</label>
                        <input type="text" id="order_number" name="order_number" required
                            value="<?php echo h($_POST['order_number'] ?? $orderParam); ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required
                            value="<?php echo h($_POST['email'] ?? ($_GET['email'] ?? '')); ?>">
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Track</button>
                    </div>
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-error" style="margin-top:1rem;">
                            <?php echo h($message); ?>
                        </div>
                    <?php endif; ?>
                </form>

                <?php if ($order): ?>
                    <div style="background:white;padding:1.5rem;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);">
                        <h3 style="margin-bottom:1rem;color:#2c3e50;">Order Details</h3>
                        <p><strong>Order Number:</strong> <?php echo h($order['order_number']); ?></p>
                        <p><strong>Status:</strong> <?php echo h(ucfirst($order['status'])); ?></p>
                        <p><strong>Placed On:</strong> <?php echo h($order['order_date']); ?></p>
                        <p><strong>Total:</strong> $<?php echo number_format((float) $order['total_amount'], 2); ?></p>
                        <p><strong>Shipping Address:</strong><br><?php echo nl2br(h($order['shipping_address'])); ?></p>

                        <h4 style="margin-top:1rem;">Items</h4>
                        <div style="overflow:auto;">
                            <table style="width:100%;border-collapse:collapse;">
                                <thead>
                                    <tr>
                                        <th style="text-align:left;border-bottom:1px solid #eee;padding:8px;">Product</th>
                                        <th style="border-bottom:1px solid #eee;padding:8px;">Qty</th>
                                        <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Price</th>
                                        <th style="text-align:right;border-bottom:1px solid #eee;padding:8px;">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order['items'] as $it): ?>
                                        <tr>
                                            <td style="padding:8px;border-bottom:1px solid #f2f2f2;">
                                                <?php echo h($it['name']); ?>
                                            </td>
                                            <td style="padding:8px;text-align:center;border-bottom:1px solid #f2f2f2;">
                                                <?php echo (int) $it['quantity']; ?>
                                            </td>
                                            <td style="padding:8px;text-align:right;border-bottom:1px solid #f2f2f2;">
                                                $<?php echo number_format((float) $it['price'], 2); ?>
                                            </td>
                                            <td style="padding:8px;text-align:right;border-bottom:1px solid #f2f2f2;">
                                                $<?php echo number_format((float) $it['price'] * (int) $it['quantity'], 2); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="./assets/js/script.js"></script>
</body>

</html>
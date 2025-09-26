<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Order.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Cart.php';
require_once __DIR__ . '/includes/email_helper.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();
$userId = getUserId();

$orderNumber = isset($_GET['order']) ? preg_replace('/[^A-Za-z0-9\-]/', '', $_GET['order']) : '';
if (!$orderNumber) {
    header('Location: orders.php');
    exit;
}

// Fetch the order for this user
$sql = "SELECT * FROM orders WHERE order_number = :onum AND user_id = :uid LIMIT 1";
$stmt = $db->prepare($sql);
$stmt->execute([':onum' => $orderNumber, ':uid' => $userId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$order) {
    header('Location: orders.php');
    exit;
}

// Fetch items with product info (image if available)
$itemsSql = "SELECT oi.*, p.name, p.image_url FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE order_id = :oid";
$itemsStmt = $db->prepare($itemsSql);
$itemsStmt->execute([':oid' => $order['id']]);
$items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch recommended add-ons (Ink Cartridges category_id = 5)
$addons = [];
try {
    $recSql = "SELECT id, name, price, image_url FROM products WHERE category_id = :cat ORDER BY created_at DESC LIMIT 3";
    $recStmt = $db->prepare($recSql);
    $recStmt->execute([':cat' => 5]);
    $addons = $recStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Exception $e) {
    $addons = [];
}

$cart = new Cart($db);
$cart_count = $cart->getCartCount($userId);

function h($v)
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

$statusSteps = ['pending' => 1, 'processing' => 2, 'shipped' => 3, 'delivered' => 4, 'cancelled' => 0];
$currentStep = $statusSteps[strtolower($order['status'] ?? 'pending')] ?? 1;

// Resend email handler
$noticeSuccess = '';
$noticeError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'resend_email') {
    $to = $order['customer_email'] ?: getUserInfo('email');
    if (filter_var($to, FILTER_VALIDATE_EMAIL)) {
        // Build a simple HTML email summary
        ob_start();
        ?>
        <h2 style="margin:0 0 10px;">Order Receipt</h2>
        <p><strong>Order #:</strong> <?php echo h($order['order_number']); ?><br>
        <strong>Date:</strong> <?php echo h($order['order_date']); ?><br>
        <strong>Status:</strong> <?php echo h(ucfirst($order['status'])); ?><br>
        <strong>Total:</strong> $<?php echo number_format((float)$order['total_amount'], 2); ?></p>
        <p><strong>Shipping Address:</strong><br><?php echo nl2br(h($order['shipping_address'])); ?></p>
        <h3 style="margin:10px 0 6px;">Items</h3>
        <table width="100%" cellpadding="6" cellspacing="0" border="0" style="border-collapse:collapse;border:1px solid #eee;">
            <thead>
                <tr>
                    <th align="left" style="border-bottom:1px solid #eee;">Product</th>
                    <th align="center" style="border-bottom:1px solid #eee;">Qty</th>
                    <th align="right" style="border-bottom:1px solid #eee;">Price</th>
                    <th align="right" style="border-bottom:1px solid #eee;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $it): $line = (float)$it['price'] * (int)$it['quantity']; ?>
                    <tr>
                        <td style="border-bottom:1px solid #f5f5f5;"><?php echo h($it['name']); ?></td>
                        <td align="center" style="border-bottom:1px solid #f5f5f5;"><?php echo (int)$it['quantity']; ?></td>
                        <td align="right" style="border-bottom:1px solid #f5f5f5;">$<?php echo number_format((float)$it['price'], 2); ?></td>
                        <td align="right" style="border-bottom:1px solid #f5f5f5;">$<?php echo number_format($line, 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        $body = ob_get_clean();
        $subject = 'Your Order Receipt - ' . ($order['order_number'] ?? '');
        if (send_email_generic($to, $subject, $body)) {
            $noticeSuccess = 'We have re-sent your order receipt to ' . h($to) . '.';
        } else {
            $lastErr = isset($GLOBALS['EMAIL_LAST_ERROR']) ? (string)$GLOBALS['EMAIL_LAST_ERROR'] : '';
            $noticeError = 'Failed to send email. ' . h($lastErr);
        }
    } else {
        $noticeError = 'Could not determine a valid email address for this order.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - <?php echo h($order['order_number']); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Layout shell */
        .od-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.25rem
        }

        .card {
            background: #fff;
            border: 1px solid var(--border-light);
            border-radius: 14px;
            box-shadow: var(--shadow-sm)
        }

        .card-body {
            padding: 1rem
        }

        /* Header */
        .od-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            border-bottom: 1px solid #eee;
            padding: 1rem
        }

        .od-meta {
            display: flex;
            gap: 1.25rem;
            flex-wrap: wrap
        }

        .od-meta .kv {
            display: flex;
            flex-direction: column
        }

        .od-meta .kv .k {
            color: #2c3e50;
            font-weight: 700;
        }

        .od-meta .kv .v {
            font-weight: 500;
            color: #6c757d;
            font-size: .85rem
        }

        .od-actions {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap
        }

        /* Shipment + Items (left column) */
        .section-title {
            font-weight: 700;
            color: #2c3e50;
            margin: 1rem 0 .75rem;
            font-size: 1.5rem;
            text-align: start;
        }

        .shipment-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fafafa;
            border: 1px solid #eee;
            border-radius: 10px;
            padding: .75rem 1rem
        }

        .progress {
            display: flex;
            gap: .5rem;
            align-items: center
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #e0e0e0
        }

        .dot.active {
            background: var(--primary-color)
        }

        .items-card {
            padding: 1rem
        }

        .item {
            display: grid;
            grid-template-columns: 80px 1fr auto;
            gap: 1rem;
            align-items: center;
            border-bottom: 1px solid #f2f2f2;
            padding: .75rem 0
        }

        .item:last-child {
            border-bottom: none
        }

        .item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #eee
        }

        /* Right sidebar */
        .sidebar .block {
            padding: 1rem;
            border-bottom: 1px solid #f2f2f2;
            width: 100%;
        }

        .sidebar .block .btn-primary{
            width: 100%;
        }

        .sidebar .block:last-child {
            border-bottom: none
        }

        .mini-list {
            display: grid;
            gap: .75rem
        }

        .mini-item {
            display: grid;
            grid-template-columns: 56px 1fr auto;
            gap: .5rem;
            align-items: center
        }

        .mini-item img {
            width: 56px;
            height: 56px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #eee
        }

        @media(max-width:992px) {
            .od-grid {
                grid-template-columns: 1fr
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <a href="orders.php" class="link" style="text-decoration:none;color:var(--primary-light);font-weight:600;display:inline-flex;gap:.25rem;align-items:center;margin-bottom:.5rem"><i class="fa-solid fa-angle-left"></i> See all orders</a>
            <h1 class="page-title">Order Details</h1>
            <?php if (!empty($noticeSuccess)): ?>
                <div class="alert alert-success"><?php echo $noticeSuccess; ?></div>
            <?php endif; ?>
            <?php if (!empty($noticeError)): ?>
                <div class="alert alert-error"><?php echo $noticeError; ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="od-header">
                    <div class="od-meta">
                        <div class="kv">
                            <span class="k">Purchase Date</span>
                            <span class="v"><?php echo h($order['order_date']); ?></span>
                        </div>
                        <div class="kv">
                            <span class="k">Order Number</span>
                            <span class="v"><?php echo h($order['order_number']); ?></span>
                        </div>
                        <div class="kv">
                            <span class="k">Total</span>
                            <span class="v">$<?php echo number_format((float)$order['total_amount'], 2); ?></span>
                        </div>
                        <div class="kv">
                            <span class="k">Payment</span>
                            <span class="v"><?php echo h(ucfirst($order['payment_method'] ?? '')); ?></span>
                        </div>
                    </div>
                    <div class="od-actions">
                        <a class="btn-primary" href="order_pdf.php?order=<?php echo urlencode($order['order_number']); ?>" target="_blank"><i class="fa-solid fa-print"></i> Print Receipt</a>
                        <a class="btn-primary" href="track_order.php?order=<?php echo urlencode($order['order_number']); ?>&email=<?php echo urlencode($order['customer_email']); ?>"><i class="fa-solid fa-truck"></i> Track Order</a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="od-grid">
                        <div>
                            <div class="section-title">Shipment</div>
                            <div class="shipment-bar">
                                <div>
                                    <div style="font-weight:700;color:#2c3e50;">Status: <?php echo h(ucfirst($order['status'])); ?></div>
                                    <div class="progress">
                                        <?php $labels = ['Pending', 'Processing', 'Shipped', 'Delivered'];
                                        for ($i = 1; $i <= 4; $i++): ?>
                                            <span class="dot <?php echo ($currentStep >= $i ? 'active' : ''); ?>" title="<?php echo $labels[$i - 1]; ?>"></span>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div style="text-align:right;">
                                    <div style="font-weight:600;font-size:.9rem">Shipping Address</div>
                                    <div style="max-width:320px; color:#6c757d;"><?php echo nl2br(h($order['shipping_address'])); ?></div>
                                </div>
                            </div>

                            <div class="card items-card" style="margin-top:1rem;">
                                <h3 class="section-title" style="margin-top:.25rem;">Items</h3>
                                <?php foreach ($items as $it): ?>
                                    <div class="item">
                                        <div>
                                            <?php if (!empty($it['image_url'])): ?>
                                                <img src="<?php echo h($it['image_url']); ?>" alt="<?php echo h($it['name']); ?>">
                                            <?php else: ?>
                                                <div style="width:80px;height:80px;border-radius:8px;background:#f3f4f6;border:1px solid #eee;display:flex;align-items:center;justify-content:center;color:#999">No Image</div>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <div style="font-weight:700;line-height:1.35;"><?php echo h($it['name']); ?></div>
                                            <div style="color:#6c757d;font-size:.9rem;">Qty: <?php echo (int)$it['quantity']; ?></div>
                                        </div>
                                        <div style="text-align:right;white-space:nowrap;">
                                            <div>$<?php echo number_format((float)$it['price'], 2); ?></div>
                                            <div style="font-weight:700;">$<?php echo number_format((float)$it['price'] * (int)$it['quantity'], 2); ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <aside class="sidebar card">
                            <div class="block">
                                <div style="font-weight:700;color:#2c3e50;margin-bottom:.25rem;">Get everything you need?</div>
                                <div class="mini-list">
                                    <?php if (!empty($addons)): foreach ($addons as $p): ?>
                                        <div class="mini-item">
                                            <img src="<?php echo h($p['image_url'] ?: 'assets/images/placeholder-ink.jpg'); ?>" alt="<?php echo h($p['name']); ?>">
                                            <div>
                                                <div style="font-size: 0.9rem; font-weight:700;"><?php echo h($p['name']); ?></div>
                                                <div style="color:#6c757d;font-size:.9rem;">$<?php echo number_format((float)$p['price'], 2); ?></div>
                                            </div>
                                            <form method="POST" action="cart_actions.php" style="margin:0">
                                                <input type="hidden" name="action" value="add">
                                                <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
                                                <input type="hidden" name="quantity" value="1">
                                                <button class="btn-primary" style="padding:.4rem .8rem;">Add</button>
                                            </form>
                                        </div>
                                    <?php endforeach; else: ?>
                                        <div style="color:#6c757d;">No recommended add-ons available right now.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="block">
                                <div style="font-weight:700;color:#2c3e50;margin-bottom:.5rem;">Need help?</div>
                                <a class="btn-primary" href="contact.php">Contact Support</a>
                            </div>
                            <div class="block">
                                <div style="font-weight:700;color:#2c3e50;margin-bottom:.5rem;">Didn’t get your email receipt?</div>
                                <form method="POST" action="order_details.php?order=<?php echo urlencode($order['order_number']); ?>">
                                    <input type="hidden" name="action" value="resend_email" />
                                    <button type="submit" class="btn-primary" style="padding:.7rem .8rem; width:100%;">Resend Email Receipt</button>
                                </form>
                            </div>
                        </aside>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="./assets/js/script.js"></script>
</body>

</html>
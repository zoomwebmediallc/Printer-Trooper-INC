<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Cart.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();
$cart = new Cart($db);
$cart_count = $cart->getCartCount(getUserId());

// Offer definition
$voucherCode = 'FIRST20';
$discountPct = 20;
$desc = 'Enjoy 20% OFF on your first purchase.';
$accessVoucher = 'ACCESS10';
$accessDesc = 'Get 10% OFF on accessories in your cart.';
$terms = [
    'Valid for first-time orders only',
    'Applies to items sold by Printer Trooper Inc',
    'Cannot be combined with other promotions',
    'Single-use per customer',
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Member Offers - 20% OFF First Purchase</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .offer-card{background:#fff;border:1px solid var(--border-light);border-radius:14px;box-shadow:var(--shadow-sm);padding:1.25rem;max-width:820px;margin:0 auto}
    .voucher{display:flex;justify-content:space-between;align-items:center;gap:1rem;background:#fff9e6;border:1px dashed #f0c36d;border-radius:12px;padding:1rem 1.25rem;margin:1rem 0}
    .voucher-code{font-weight:900;font-size:1.5rem;letter-spacing:1px;color:#b7791f}
    .voucher-desc{color:#6c757d}
    .terms{margin-top:1rem;color:#6c757d}
    .terms li{margin-left:1rem}
  </style>
</head>
<body>
  <?php include 'includes/header.php'; ?>

  <main class="main-content">
    <div class="container">
      <h1 class="page-title">Member Offers</h1>

      <div class="offer-card">
        <h2 style="margin-bottom:.25rem;color:#2c3e50;">Welcome Voucher</h2>
        <p class="voucher-desc"><?php echo htmlspecialchars($desc); ?></p>

        <div class="voucher">
          <div>
            <div class="voucher-code"><i class="fa-solid fa-ticket"></i> <?php echo htmlspecialchars($voucherCode); ?></div>
            <div style="color:#2c3e50;font-weight:700;">Save <?php echo (int)$discountPct; ?>%</div>
          </div>
          <div>
            <button class="btn-primary" style="padding: 0.7rem 1rem;" onclick="navigator.clipboard.writeText('<?php echo $voucherCode; ?>'); this.innerText='Copied!'; setTimeout(()=>this.innerText='Copy Code',1500)">Copy Code</button>
            <a class="btn-primary" style="margin-left:.5rem;" href="cart.php">Go to Cart</a>
          </div>
        </div>

        <div class="terms">
          <strong>Terms:</strong>
          <ul>
            <?php foreach($terms as $t): ?>
              <li><?php echo htmlspecialchars($t); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>

      <div class="offer-card" style="margin-top:1rem;">
        <h2 style="margin-bottom:.25rem;color:#2c3e50;">Accessories Perk</h2>
        <p class="voucher-desc"><?php echo htmlspecialchars($accessDesc); ?></p>
        <div class="voucher">
          <div>
            <div class="voucher-code"><i class="fa-solid fa-ticket"></i> <?php echo htmlspecialchars($accessVoucher); ?></div>
            <div style="color:#2c3e50;font-weight:700;">Save 10% on accessories</div>
          </div>
          <div>
            <button class="btn-primary" style="padding: 0.7rem 1rem;" onclick="navigator.clipboard.writeText('<?php echo $accessVoucher; ?>'); this.innerText='Copied!'; setTimeout(()=>this.innerText='Copy Code',1500)">Copy Code</button>
            <a class="btn-primary" style="margin-left:.5rem;" href="cart.php">Go to Cart</a>
          </div>
        </div>
        <div class="terms">
          <strong>How it works:</strong>
          <ul>
            <li>Add accessories to your cart (e.g., paper, ink, cables)</li>
            <li>Enter code ACCESS10 at checkout</li>
            <li>10% applies to the accessories subtotal only</li>
          </ul>
        </div>
      </div>
    </div>
  </main>

  <?php include 'includes/footer.php'; ?>
  <script src="./assets/js/script.js"></script>
</body>
</html>

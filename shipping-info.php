<?php
require_once 'includes/session.php';
require_once 'config/database.php';
require_once 'models/Product.php';
require_once 'models/Cart.php';

$database = new Database();
$db = $database->getConnection();

$product = new Product($db);
$cart = new Cart($db);
$featured_products = $product->getAll();

$cart_count = 0;
// if (isLoggedIn()) {
$cart_count = $cart->getCartCount(getUserId());
// }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Printer Trooper Inc - <?php echo $page_title; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- <link rel="icon" type="image/png" href="./assets/images/favicon.png"> -->
</head>

<body>
    <div class="preloader" id="preloader">
        <div class="spinner"></div>
    </div>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <section id="policies">
            <div class="container">
                <h2>Our Shipping Policy</h2>
                <ul>
                    <li><strong>FREE standard shipping</strong> on all orders (2 working days for Canada, 5 working days
                        for
                        the USA).</li>
                    <li><strong>EXPEDITED shipping</strong> available for an additional charge (delivers in 2–4 business
                        days).</li>
                    <li>We ship via <strong>USPS</strong> or <strong>FedEx</strong> based on product weight and shipping
                        address.</li>
                    <li>Shipping days: <strong>Monday – Friday</strong> (Holidays not included).</li>
                    <li>We cannot change the declared value for international shipments.</li>
                    <li>We reserve the right to cancel any order.</li>
                    <li>All packages are shipped with <strong>tracking numbers</strong>.</li>
                    <li>Some packages may require <strong>signature confirmation</strong>.</li>
                </ul>

                <h2>Our Return Policy</h2>
                <ul>
                    <li>If an item is defective, please <strong>contact us within 15 days</strong> for a replacement or
                        refund.</li>
                    <li>We ensure <strong>quick & easy returns</strong> within 15–20 days for most products.</li>
                    <li><strong>No restocking fees</strong> when the item is returned in its original condition.</li>
                    <li>Replacement products will be provided if available; otherwise, you may choose another product or
                        request a refund.</li>
                    <li>All return/replacement items will include a <strong>tracking number</strong>.</li>
                    <li>All items are inspected and tested before refunds are issued.</li>
                    <li>We provide a <strong>full refund</strong> for defective original items that are returned to us.
                    </li>
                </ul>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="./assets/js/script.js"></script>
</body>

</html>
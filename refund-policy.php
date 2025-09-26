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
                <h2>Returns Policy</h2>

                <p>Our policy lasts <strong>30 days</strong>. If 30 days have gone by since your purchase,
                    unfortunately, we
                    can’t offer you a refund or exchange.</p>

                <p>To be eligible for a return, your item must be <strong>unused</strong> and in the same condition that
                    you
                    received it. It must also be in the <strong>original packaging</strong>.</p>

                <p>Several types of goods are exempt from being returned.</p>

                <p>To complete your return, we require a <strong>receipt or proof of purchase</strong>.</p>

                <p><strong>Please do not send your purchase back to the manufacturer.</strong></p>

                <h3>Partial Refunds (if applicable)</h3>
                <ul>
                    <li>Any item not in its original condition, damaged, or missing parts for reasons not due to our
                        error.
                    </li>
                    <li>Any item that is returned more than 30 days after delivery.</li>
                </ul>

                <h3>Refunds (if applicable)</h3>
                <p>Once your return is received and inspected, we will send you an email to notify you that we have
                    received
                    your returned item.
                    We will also notify you of the approval or rejection of your refund.</p>

                <p>If you are approved, then your refund will be processed, and a credit will automatically be applied
                    to
                    your
                    credit card or original method of payment within a certain number of days.</p>

                <h3>Late or Missing Refunds (if applicable)</h3>
                <ul>
                    <li>Check your bank account again.</li>
                    <li>Then contact your credit card company, it may take some time before your refund is officially
                        posted.
                    </li>
                    <li>Next, contact your bank. There is often some processing time before a refund is posted.</li>
                    <li>If you’ve done all of this and you still have not received your refund, please contact us at <a
                            href="mailto:support@printertrooperinc.com">support@printertrooperinc.com</a>.</li>
                </ul>

                <h3>Sale Items (if applicable)</h3>
                <p>Only regular priced items may be refunded, unfortunately <strong>sale items cannot be
                        refunded</strong>.
                </p>

                <h3>Exchanges (if applicable)</h3>
                <p>We only replace items if they are defective or damaged. If you need to exchange it for the same item,
                    send us an email at <a href="mailto:support@printertrooperinc.com">support@printertrooperinc.com</a> and
                    send
                    your
                    item to:</p>
                <address>
                    94 Leacrest St., Brampton, AL, L6S3K6, United States.
                </address>

                <h3>Shipping</h3>
                <p>To return your product, you should mail it to:</p>
                <address>
                    94 Leacrest St., Brampton, AL, L6S3K6, United States.
                </address>

                <ul>
                    <li>You will be responsible for paying your own shipping costs for returning your item.</li>
                    <li>Shipping costs are non-refundable. If you receive a refund, the cost of return shipping will be
                        deducted
                        from your refund.</li>
                    <li>Depending on where you live, the time it may take for your exchanged product to reach you may
                        vary.
                    </li>
                    <li>If you are shipping an item over $75, consider using a <strong>trackable shipping
                            service</strong>
                        or
                        purchasing <strong>shipping insurance</strong>. We don’t guarantee that we will receive your
                        returned
                        item.</li>
                </ul>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="./assets/js/script.js"></script>
</body>

</html>
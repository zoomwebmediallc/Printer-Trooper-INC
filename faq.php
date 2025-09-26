<?php
require_once 'includes/session.php';
require_once 'config/database.php';
require_once 'models/Cart.php';

$database = new Database();
$db = $database->getConnection();
$cart = new Cart($db);
$cart_count = $cart->getCartCount(getUserId());
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Printer Trooper Inc - Frequently Asked Questions</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/style.css">
    <!-- <link rel="icon" type="image/png" href="./assets/images/favicon.png"> -->
</head>

<body>
    <div class="preloader" id="preloader">
        <div class="spinner"></div>
    </div>

    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <section class="faq" id="faq">
            <div class="container">
                <h2 class="section-title">Frequently Asked Questions</h2>
                <p class="section-subtitle">Got questions? We've got answers to help you make the best choice</p>
                <div class="faq-container">
                    <div class="faq-item">
                        <div class="faq-question">
                            <span>What types of printers do you offer?</span>
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </div>
                        <div class="faq-answer">
                            We offer a comprehensive range of printers including inkjet printers for high-quality photos
                            and
                            documents, laser printers for fast office printing, all-in-one devices that print, scan,
                            copy,
                            and fax, and specialized photo printers for professional-grade photo printing. All our
                            products
                            are from trusted brands like Canon, HP, Epson, and Brother.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span>Do you offer warranty on your printers?</span>
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </div>
                        <div class="faq-answer">
                            Yes! All our printers come with a comprehensive 2-year warranty that covers manufacturing
                            defects and technical issues. We also offer extended warranty options for additional peace
                            of
                            mind. Our warranty covers parts, labor, and technical support.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span>What is your shipping policy?</span>
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </div>
                        <div class="faq-answer">
                            We offer free shipping on all orders over $99. For orders under $99, standard shipping rates
                            apply. We provide expedited shipping options for urgent orders, and all shipments include
                            tracking information. Most orders are processed within 1-2 business days.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span>Can I return a printer if I'm not satisfied?</span>
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </div>
                        <div class="faq-answer">
                            Absolutely! We offer a 30-day return policy on all printers. If you're not completely
                            satisfied
                            with your purchase, you can return it for a full refund within 30 days of delivery. The
                            printer
                            must be in original condition with all accessories and packaging.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span>Do you provide technical support?</span>
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </div>
                        <div class="faq-answer">
                            Yes! We provide 24/7 technical support through multiple channels including phone, email, and
                            live chat. Our expert technicians can help with setup, troubleshooting, and maintenance. We
                            also
                            have extensive online resources including setup guides and troubleshooting articles.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span>What payment methods do you accept?</span>
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </div>
                        <div class="faq-answer">
                            We accept all major credit cards (Visa, MasterCard, American Express, Discover), PayPal,
                            Apple
                            Pay, Google Pay, and bank transfers. All transactions are secured with SSL encryption to
                            protect
                            your personal and financial information.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span>Do you offer business discounts?</span>
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </div>
                        <div class="faq-answer">
                            Yes! We offer special pricing for businesses, schools, and bulk orders. Contact our business
                            sales team for custom quotes and volume discounts. We also provide dedicated account
                            management
                            for large organizations and ongoing support for fleet management.
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="./assets/js/script.js"></script>
</body>

</html>
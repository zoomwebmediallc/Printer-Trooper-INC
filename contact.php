<?php
require_once 'includes/session.php';
require_once 'config/database.php';
require_once 'models/Cart.php';

$database = new Database();
$db = $database->getConnection();
$cart = new Cart($db);
$cart_count = $cart->getCartCount(getUserId());

$success = isset($_GET['success']);
$msg = isset($_GET['msg']) ? urldecode($_GET['msg']) : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Printer Trooper Inc - Contact Us</title>
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
        <div class="container">
            <h1 class="page-title">Contact Us</h1>

            <?php if ($msg): ?>
                <div class="alert <?php echo $success ? 'alert-success' : 'alert-error'; ?>">
                    <?php echo htmlspecialchars($msg); ?>
                </div>
            <?php endif; ?>

            <div class="contact-grid">
                <div class="contact-form-card">
                    <h3>Send us a message</h3>
                    <form method="POST" action="contact_submit.php" class="contact-form">
                        <div class="form-group">
                            <label for="name">Your Name</label>
                            <input type="text" id="name" name="name" required placeholder="Enter your name">
                        </div>
                        <div class="form-group">
                            <label for="email">Your Email</label>
                            <input type="email" id="email" name="email" required placeholder="you@example.com">
                        </div>
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" required placeholder="Subject">
                        </div>
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" rows="6" required
                                placeholder="How can we help?"></textarea>
                        </div>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </form>
                </div>
                <div class="contact-info-card">
                    <h3>Contact Information</h3>
                    <ul class="contact-info-list">
                        <li><i class="fas fa-phone"></i> 1-800-PRINTER</li>
                        <li><i class="fas fa-envelope"></i> info@printertrooperinc.com</li>
                        <li><i class="fas fa-clock"></i> 24/7 Support Available</li>
                        <li><i class="fas fa-map-marker-alt"></i> Nationwide Delivery</li>
                    </ul>
                    <p>Our support team will get back to you as soon as possible.</p>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="./assets/js/script.js"></script>
</body>

</html>
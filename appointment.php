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
    <title>Printer Trooper Inc - Premium Printers & Technology</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/style.css">
    <!-- <link rel="icon" type="image/png" href="./assets/images/favicon.png"> -->
</head>

<body>
    <div class="preloader" id="preloader">
        <div class="spinner"></div>
    </div>
    <?php include 'includes/header.php'; ?>

    <div id="appointmentPage" class="appointment-page">
        <div class="container">
            <div class="appointment-form-container">
                <?php if (isset($_GET['msg'])): ?>
                    <div class="alert <?php echo isset($_GET['success']) ? 'alert-success' : 'alert-error'; ?>"
                        style="margin-bottom: 1rem;">
                        <?php echo htmlspecialchars($_GET['msg']); ?>
                    </div>
                <?php endif; ?>
                <a href="index.php" class="back-btn">
                    ← Back to Home
                </a>

                <form class="appointment-form" id="appointmentForm" method="POST" action="appointment_submit.php">
                    <h2>Book Your Appointment</h2>

                    <div class="form-group">
                        <label for="fullName">Full Name *</label>
                        <input type="text" id="fullName" name="fullName" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>

                    <div class="form-group">
                        <label for="service">Service Type *</label>
                        <select id="service" name="service" required>
                            <option value="">Select a service</option>
                            <option value="consultation">Consultation</option>
                            <option value="premium">Premium Services</option>
                            <option value="followup">Follow-up Care</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="date">Preferred Date *</label>
                        <input type="date" id="date" name="date" required>
                    </div>

                    <div class="form-group">
                        <label for="time">Preferred Time *</label>
                        <select id="time" name="time" required>
                            <option value="">Select a time</option>
                            <option value="09:00">9:00 AM</option>
                            <option value="10:00">10:00 AM</option>
                            <option value="11:00">11:00 AM</option>
                            <option value="12:00">12:00 PM</option>
                            <option value="13:00">1:00 PM</option>
                            <option value="14:00">2:00 PM</option>
                            <option value="15:00">3:00 PM</option>
                            <option value="16:00">4:00 PM</option>
                            <option value="17:00">5:00 PM</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="message">Additional Notes</label>
                        <textarea id="message" name="message"
                            placeholder="Tell us more about your needs or any special requirements..."></textarea>
                    </div>

                    <button type="submit" class="submit-btn">Book Appointment</button>


                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="./assets/js/script.js"></script>
</body>

</html>
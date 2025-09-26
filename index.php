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
<body>

    <div class="preloader" id="preloader">
        <div class="spinner"></div>
    </div>
    <?php include 'includes/header.php'; ?>

    <section class="hero">
        <div class="hero-slider">
            <div class="hero-slide active">
                <div class="hero-content">
                    <h1>Premium Printers for Every Need</h1>
                    <p>Printers, Inks, and Other Printing Supplies for Your Household and Office All-in-one, inkjet, and
                        laser printers are quick and easy to set up and install.</p>
                    <div class="hero-buttons">
                        <a href="printers.php" class="btn-primary">
                            <i class="fas fa-shopping-cart"></i>
                            Shop Now
                        </a>
                        <a href="#why-choose-us" class="btn-secondary">
                            <i class="fas fa-info-circle"></i>
                            Learn More
                        </a>
                    </div>
                </div>
            </div>
            <div class="hero-slide">
                <div class="hero-content">
                    <h1>Professional Photo Printing</h1>
                    <p>Discover the ease of using wireless, portable, all-in-one, inkjet, and laser printers that are
                        designed to meet your regular printing requirements.</p>
                    <div class="hero-buttons">
                        <a href="printers.php?category=4" class="btn-primary">
                            <i class="fas fa-camera"></i>
                            Photo Printers
                        </a>
                        <a href="#features" class="btn-secondary">
                            <i class="fas fa-palette"></i>
                            View Features
                        </a>
                    </div>
                </div>
            </div>
            <div class="hero-slide">
                <div class="hero-content">
                    <h1>Office Solutions & More</h1>
                    <p>Boost your productivity with high-speed laser printers and all-in-one devices designed for
                        modern businesses.</p>
                    <div class="hero-buttons">
                        <a href="printers.php?category=2" class="btn-primary">
                            <i class="fas fa-building"></i>
                            Business Solutions
                        </a>
                        <a href="#contact" class="btn-secondary">
                            <i class="fas fa-phone"></i>
                            Get Quote
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="hero-nav">
            <div class="hero-nav-dot active" data-slide="0"></div>
            <div class="hero-nav-dot" data-slide="1"></div>
            <div class="hero-nav-dot" data-slide="2"></div>
        </div>
    </section>

    <section class="why-choose-us" id="why-choose-us">
        <div class="container">
            <h2 class="section-title">Why Choose Printer Trooper Inc?</h2>
            <p class="section-subtitle">We provide exceptional service and quality products that exceed expectations
            </p>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="assets/images/free_shipping.png" alt="Free Fast Shipping">
                    </div>
                    <h3>Wide Brand Selection</h3>
                    <p>Choose from top printer brands like HP, Epson, Canon, and Brother. We have a model to match every
                        budget and need.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="assets/images/2_year_warranty.png" alt="2 Year Warranty">
                    </div>
                    <h3>2 Year Warranty</h3>
                    <p>All our printers come with comprehensive warranty coverage for your peace of mind. We stand
                        behind our products.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="assets/images/24_7_support.png" alt="24/7 Expert Support">
                    </div>
                    <h3>24/7 Expert Support</h3>
                    <p>Our technical experts are available round the clock to help with any questions or issues.
                        Multiple contact methods available.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="assets/images/certified_products.png" alt="Certified Products">
                    </div>
                    <h3>Certified Products</h3>
                    <p>All our printers are genuine, certified products from authorized manufacturers. Quality
                        guaranteed.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="assets/images/best_price_guarantee.png" alt="Best Price Guarantee">
                    </div>
                    <h3>Best Price Guarantee</h3>
                    <p>We offer competitive prices and price matching. If you find it cheaper elsewhere, we'll match
                        it.
                    </p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="assets/images/eco_friendly.png" alt="Eco-Friendly Options">
                    </div>
                    <h3>Eco-Friendly Options</h3>
                    <p>Choose from our selection of eco-friendly printers and recycling programs for old devices.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="categories">
        <div class="container">
            <h2 class="section-title">Shop by Category</h2>
            <p class="section-subtitle">Find the perfect printer for your specific needs and requirements</p>
            <div class="category-grid">
                <div class="category-card">
                    <div class="category-icon">
                        <img src="assets/images/inkjet_printer.png" alt="Inkjet Printers">
                    </div>
                    <h3>Inkjet Printers</h3>
                    <p>Perfect for high-quality photo printing and everyday document printing with vibrant colors.
                    </p>
                    <a href="printers.php?category=1" class="btn-category">
                        <i class="fas fa-arrow-right"></i>
                        View Products
                    </a>
                </div>
                <div class="category-card">
                    <div class="category-icon">
                        <img src="assets/images/laser_printer.png" alt="Laser Printers">
                    </div>
                    <h3>Laser Printers</h3>
                    <p>Fast, efficient, and cost-effective printing solution ideal for office environments.</p>
                    <a href="printers.php?category=2" class="btn-category">
                        <i class="fas fa-arrow-right"></i>
                        View Products
                    </a>
                </div>
                <div class="category-card">
                    <div class="category-icon">
                        <img src="assets/images/all_in_one.png" alt="All-in-One Printers">
                    </div>
                    <h3>All-in-One</h3>
                    <p>Versatile devices that print, scan, copy, and fax - everything you need in one machine.</p>
                    <a href="printers.php?category=3" class="btn-category">
                        <i class="fas fa-arrow-right"></i>
                        View Products
                    </a>
                </div>
                <div class="category-card">
                    <div class="category-icon">
                        <img src="assets/images/photo_printer.png" alt="Photo Printers">
                    </div>
                    <h3>Photo Printers</h3>
                    <p>Professional-grade photo printing with exceptional detail and color reproduction.</p>
                    <a href="printers.php?category=4" class="btn-category">
                        <i class="fas fa-arrow-right"></i>
                        View Products
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="featured-products">
        <div class="container">
            <h2 class="section-title">Featured Products</h2>
            <p class="section-subtitle">Discover our most popular and highly-rated printer models</p>
            <div class="product-carousel" id="productCarousel">
                <div class="product-track" id="productTrack">
                    <?php
                    $count = 0;
                    while ($row = $featured_products->fetch(PDO::FETCH_ASSOC) and $count < 6):
                        extract($row);
                        $count++;
                        ?>
                        <div class="product-card">
                            <div class="product-image">
                                <img src="<?php echo htmlspecialchars($image_url); ?>"
                                    alt="<?php echo htmlspecialchars($name); ?>">
                            </div>
                            <div class="product-info">
                                <div class="product-brand"><?php echo htmlspecialchars($brand); ?></div>
                                <h3 class="product-name"><a href="product_details.php?id=<?php echo (int) $id; ?>">
                                        <?php echo htmlspecialchars($name); ?>
                                    </a></h3>
                                <p class="product-brand"><?php echo htmlspecialchars($model); ?></p>
                                <div class="product-price">$<?php echo number_format($price, 2); ?></div>
                                <?php if ($stock_quantity > 0): ?>
                                    <form class="add-to-cart-form" method="POST" action="cart_actions.php">
                                        <input type="hidden" name="action" value="add">
                                        <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                                        <input type="number" name="quantity" value="1" min="1"
                                            max="<?php echo $stock_quantity; ?>" class="quantity-input">
                                        <button type="submit" class="add-to-cart-btn">
                                            <i class="fas fa-cart-plus"></i>
                                            Add to Cart
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn-disabled" disabled>Out of Stock</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                <button class="carousel-nav carousel-prev" id="prevBtn">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="carousel-nav carousel-next" id="nextBtn">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </section>

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

    <?php include 'includes/footer.php'; ?>
    <script src="./assets/js/script.js"></script>

</body>

</html>
<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Cart.php';

$database = new Database();
$db = $database->getConnection();
$cart = new Cart($db);
$cart_count = $cart->getCartCount(getUserId());

$current_search = htmlspecialchars($_GET['search'] ?? '');
$is_logged_in = isLoggedIn();
$username = getUserInfo('username');
?>

<header class="site-header" id="header">
    <div class="header-bottom">
        <div class="header-quote">
            <small>Buy with an adviser</small>
        </div>
        <div class="header-actions">
            <a href="/printer-store/track_order.php" class="link"><i class="fas fa-truck"></i> Track Order Status</a>
            <a href="/printer-store/orders.php" class="link"><i class="fas fa-folder"></i> My Orders</a>
        </div>
    </div>
    <div class="header-divider"></div>
    <div class="header-top">
        <div class="header-left">
            <a href="/printer-store/index.php" class="logo">
                <i class="fas fa-print"></i>
                Printer Trooper Inc
            </a>
        </div>
        <div class="header-center">
            <form class="header-search" method="GET" action="/printer-store/printers.php">
                <input type="text" name="search" placeholder="Search printers..."
                    value="<?php echo $current_search; ?>" />
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
        <div class="header-right">
            <a href="/printer-store/printers.php" class="cart-link"><span>Shop Now</span></a>
            <a href="/printer-store/cart.php" class="cart-link">
                <i class="fas fa-shopping-cart"></i>
                <span>Cart</span>
                <span class="cart-badge"><?php echo (int) $cart_count; ?></span>
            </a>
            <?php if ($is_logged_in): ?>
                <div class="user-menu">
                    <button class="user-toggle" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-user"></i>
                        <span>Hi, <?php echo htmlspecialchars($username ?: 'Account'); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="user-dropdown">
                        <a href="/printer-store/profile.php"><i class="fa-regular fa-user"></i> Account</a>
                        <a href="/printer-store/orders.php"><i class="fa-solid fa-bag-shopping"></i> Purchase</a>
                        <a href="/printer-store/member_offers.php"><i class="fa-solid fa-gift"></i> Member offers</a>
                        <a href="/printer-store/payment_methods.php"><i class="fa-regular fa-credit-card"></i> Payment
                            Method</a>
                        <a href="/printer-store/logout.php"><i class="fas fa-sign-out-alt"></i> Sign out</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="auth-links">
                    <a href="/printer-store/register.php" class="link">Sign up</a>
                    <span class="sep">/</span>
                    <?php
                    $redirect = urlencode($_SERVER['REQUEST_URI'] ?? '/printer-store/index.php');
                    ?>
                    <a href="/printer-store/login.php?redirect=<?php echo $redirect; ?>" class="link">Login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>

<script>
    // Simple dropdown toggle
    (function () {
        const toggle = document.querySelector('.user-toggle');
        const menu = document.querySelector('.user-dropdown');
        if (toggle && menu) {
            toggle.addEventListener('click', function () {
                menu.classList.toggle('open');
            });
            document.addEventListener('click', function (e) {
                if (!menu.contains(e.target) && !toggle.contains(e.target)) {
                    menu.classList.remove('open');
                }
            });
        }
    })();
</script>
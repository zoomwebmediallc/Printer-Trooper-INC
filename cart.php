<?php
require_once 'includes/session.php';
require_once 'config/database.php';
require_once 'models/Cart.php';

// requireLogin();
$database = new Database();
$db = $database->getConnection();

$cart = new Cart($db);
$cart_items = $cart->getCartItems(getUserId());
$cart_total = $cart->getCartTotal(getUserId());
$cart_count = $cart->getCartCount(getUserId());
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Printer Trooper Inc - <?php echo $page_title; ?></title>
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
            <h1 class="page-title">Shopping Cart</h1>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <?php
                    switch ($_GET['success']) {
                        case 'added':
                            echo 'Product added to cart successfully!';
                            break;
                        case 'updated':
                            echo 'Cart updated successfully!';
                            break;
                        case 'removed':
                            echo 'Item removed from cart successfully!';
                            break;
                        case 'cleared':
                            echo 'Cart cleared successfully!';
                            break;
                        default:
                            echo 'Action completed successfully!';
                    }
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <?php if ($cart_items->rowCount() > 0): ?>
                <div class="cart-content">
                    <div class="cart-items">
                        <?php while ($item = $cart_items->fetch(PDO::FETCH_ASSOC)): ?>
                            <div class="cart-item">
                                <div class="item-image">
                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>"
                                        alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                                <div class="item-details">
                                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <p class="item-price">$<?php echo number_format($item['price'], 2); ?> each</p>
                                    <p class="item-stock">Stock available: <?php echo $item['stock_quantity']; ?></p>
                                </div>
                                <div class="item-quantity">
                                    <form method="POST" action="cart_actions.php" class="quantity-form">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                        <label for="quantity_<?php echo $item['id']; ?>">Qty:</label>
                                        <input type="number" id="quantity_<?php echo $item['id']; ?>" name="quantity"
                                            value="<?php echo $item['quantity']; ?>" min="1"
                                            max="<?php echo $item['stock_quantity']; ?>">
                                        <button type="submit" class="btn-update">Update</button>
                                    </form>
                                </div>
                                <div class="item-subtotal">
                                    <p>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                                </div>
                                <div class="item-remove">
                                    <form method="POST" action="cart_actions.php">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" class="btn-remove"
                                            onclick="return confirm('Are you sure you want to remove this item?')">
                                            Remove
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <div class="cart-summary">
                        <div class="summary-box">
                            <h3>Order Summary</h3>
                            <div class="summary-line">
                                <span>Items (<?php echo $cart_count; ?>):</span>
                                <span>$<?php echo number_format($cart_total, 2); ?></span>
                            </div>
                            <div class="summary-line">
                                <span>Shipping:</span>
                                <span>FREE</span>
                            </div>
                            <div class="summary-line total">
                                <strong>
                                    <span>Total:</span>
                                    <span>$<?php echo number_format($cart_total, 2); ?></span>
                                </strong>
                            </div>

                            <div class="cart-actions">
                                <a href="checkout.php" class="btn-primary">Proceed to Checkout</a>
                                <form method="POST" action="cart_actions.php" style="margin-top: 10px;">
                                    <input type="hidden" name="action" value="clear">
                                    <button type="submit" class="btn-secondary"
                                        onclick="return confirm('Are you sure you want to clear your cart?')">
                                        Clear Cart
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-cart">
                    <h2>Your cart is empty</h2>
                    <p>Looks like you haven't added any items to your cart yet.</p>
                    <a href="printers.php" class="btn-primary">Start Shopping</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="./assets/js/script.js"></script>
</body>

</html>
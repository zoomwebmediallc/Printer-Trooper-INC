<?php
require_once 'includes/session.php';
require_once 'config/database.php';
require_once 'models/Product.php';
require_once 'models/Cart.php';

$database = new Database();
$db = $database->getConnection();

$productModel = new Product($db);
$cart = new Cart($db);

// Validate and fetch product ID
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0 || !$productModel->getById($id)) {
    header('Location: printers.php?error=' . urlencode('Product not found'));
    exit();
}

// After getById(), $productModel has the product fields populated
$currentProduct = $productModel;

$cart_count = $cart->getCartCount(getUserId());
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Printer Trooper Inc - <?php echo htmlspecialchars($currentProduct->name); ?></title>
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
            <nav class="breadcrumb">
                <a href="index.php">Home</a> /
                <a href="printers.php">Printers</a> /
                <span><?php echo htmlspecialchars($currentProduct->name); ?></span>
            </nav>

            <section class="product-details">
                <div class="product-details-grid">
                    <div class="product-details-image">
                        <img src="<?php echo htmlspecialchars($currentProduct->image_url); ?>"
                            alt="<?php echo htmlspecialchars($currentProduct->name); ?>">
                    </div>
                    <div class="product-details-info">
                        <h1 class="product-title"><?php echo htmlspecialchars($currentProduct->name); ?></h1>
                        <p class="product-brand">Brand: <?php echo htmlspecialchars($currentProduct->brand); ?> | Model:
                            <?php echo htmlspecialchars($currentProduct->model); ?>
                        </p>
                        <p class="product-category">Category:
                            <?php echo htmlspecialchars($currentProduct->category_name); ?>
                        </p>
                        <p class="product-price">$<?php echo number_format((float) $currentProduct->price, 2); ?></p>
                        <p
                            class="product-stock <?php echo $currentProduct->stock_quantity > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                            <?php echo $currentProduct->stock_quantity > 0 ? ('In Stock: ' . (int) $currentProduct->stock_quantity) : 'Out of Stock'; ?>
                        </p>

                        <?php if ($currentProduct->stock_quantity > 0): ?>
                            <form class="add-to-cart-form" method="POST" action="cart_actions.php">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?php echo (int) $currentProduct->id; ?>">
                                <div class="quantity-controls">
                                    <label for="quantity_single">Quantity:</label>
                                    <input type="number" id="quantity_single" name="quantity" value="1" min="1"
                                        max="<?php echo (int) $currentProduct->stock_quantity; ?>">
                                </div>
                                <button type="submit" class="btn-primary">Add to Cart</button>
                            </form>
                        <?php else: ?>
                            <button class="btn-disabled" disabled>Out of Stock</button>
                        <?php endif; ?>

                        <?php if (!empty($currentProduct->description)): ?>
                            <div class="product-description">
                                <h3>Description</h3>
                                <p><?php echo nl2br(htmlspecialchars($currentProduct->description)); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($currentProduct->specifications)): ?>
                            <div class="product-specs">
                                <h3>Specifications</h3>
                                <p><?php echo nl2br(htmlspecialchars($currentProduct->specifications)); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <section class="related-products">
                <h2 class="section-title">Related Products</h2>
                <div class="products-grid">
                    <?php
                    // Fetch products from the same category and exclude current ID, limit 4
                    $relatedStmt = $productModel->getByCategory($currentProduct->category_id);
                    $shown = 0;
                    while ($row = $relatedStmt->fetch(PDO::FETCH_ASSOC)) {
                        if ((int) $row['id'] === (int) $currentProduct->id) {
                            continue;
                        }
                        ?>
                        <div class="product-card">
                            <div class="product-image">
                                <a href="product_details.php?id=<?php echo (int) $row['id']; ?>">
                                    <img src="<?php echo htmlspecialchars($row['image_url']); ?>"
                                        alt="<?php echo htmlspecialchars($row['name']); ?>">
                                </a>
                            </div>
                            <div class="product-info">
                                <h3>
                                    <a
                                        href="product_details.php?id=<?php echo (int) $row['id']; ?>"><?php echo htmlspecialchars($row['name']); ?></a>
                                </h3>
                                <p class="brand"><?php echo htmlspecialchars($row['brand']); ?> -
                                    <?php echo htmlspecialchars($row['model']); ?>
                                </p>
                                <p class="price">$<?php echo number_format((float) $row['price'], 2); ?></p>
                                <?php if ((int) $row['stock_quantity'] > 0): ?>
                                    <form class="add-to-cart-form" method="POST" action="cart_actions.php">
                                        <input type="hidden" name="action" value="add">
                                        <input type="hidden" name="product_id" value="<?php echo (int) $row['id']; ?>">
                                        <div class="quantity-controls">
                                            <label for="quantity_related_<?php echo (int) $row['id']; ?>">Qty:</label>
                                            <input type="number" id="quantity_related_<?php echo (int) $row['id']; ?>"
                                                name="quantity" value="1" min="1"
                                                max="<?php echo (int) $row['stock_quantity']; ?>">
                                        </div>
                                        <button type="submit" class="btn-primary">Add to Cart</button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn-disabled" disabled>Out of Stock</button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php
                        $shown++;
                        if ($shown >= 4)
                            break;
                    }
                    if ($shown === 0) {
                        echo '<div class="no-products"><p>No related products found.</p></div>';
                    }
                    ?>
                </div>
            </section>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="./assets/js/script.js"></script>
</body>

</html>
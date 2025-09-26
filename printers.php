<?php
require_once 'includes/session.php';
require_once 'config/database.php';
require_once 'models/Product.php';
require_once 'models/Cart.php';

$database = new Database();
$db = $database->getConnection();
$product = new Product($db);
$cart = new Cart($db);
$products = null;
$page_title = "All Printers";

// Pagination settings
$per_page = 12;
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$total_items = 0;
$total_pages = 1;

// Determine context: category, search, or all
if (isset($_GET['category']) && $_GET['category'] !== '') {
    $category_id = (int) $_GET['category'];

    // Count and fetch paginated products by category
    $total_items = $product->countByCategory($category_id);
    $total_pages = max(1, (int) ceil($total_items / $per_page));
    $page = min($page, $total_pages);
    $offset = ($page - 1) * $per_page;
    $products = $product->getByCategoryPaginated($category_id, $per_page, $offset);

    $categories = [
        1 => "Inkjet Printers",
        2 => "Laser Printers",
        3 => "All-in-One Printers",
        4 => "Photo Printers",
        5 => "Ink Cartridges"
    ];
    $page_title = $categories[$category_id] ?? "Printers";
} elseif (isset($_GET['search']) && $_GET['search'] !== '') {
    $search_term = $_GET['search'];

    // Count and fetch paginated products for search
    $total_items = $product->countSearch($search_term);
    $total_pages = max(1, (int) ceil($total_items / $per_page));
    $page = min($page, $total_pages);
    $offset = ($page - 1) * $per_page;
    $products = $product->searchPaginated($search_term, $per_page, $offset);

    $page_title = "Search Results for: " . htmlspecialchars($search_term);
} else {
    // Count and fetch paginated products for all
    $total_items = $product->countAll();
    $total_pages = max(1, (int) ceil($total_items / $per_page));
    $page = min($page, $total_pages);
    $offset = ($page - 1) * $per_page;
    $products = $product->getAllPaginated($per_page, $offset);
}

// For pagination UI, always show page count based on ALL products so that Next/Prev
// navigates across the complete catalog regardless of current filters
$pagination_total_pages = max(1, (int) ceil($product->countAll() / $per_page));

$cart_count = $cart->getCartCount(getUserId());
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Printer Trooper Inc - <?php echo $page_title; ?></title>
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
            <div class="filters-section">
                <div class="search-box">
                    <form method="GET" action="printers.php">
                        <input type="text" name="search" placeholder="Search printers..."
                            value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                        <button type="submit">Search</button>
                    </form>
                </div>

                <div class="category-filters">
                    <a href="printers.php" <?php echo !isset($_GET['category']) ? 'class="active"' : ''; ?>>All</a>
                    <a href="printers.php?category=1" <?php echo ($_GET['category'] ?? '') == '1' ? 'class="active"' : ''; ?>>Inkjet</a>
                    <a href="printers.php?category=2" <?php echo ($_GET['category'] ?? '') == '2' ? 'class="active"' : ''; ?>>Laser</a>
                    <a href="printers.php?category=3" <?php echo ($_GET['category'] ?? '') == '3' ? 'class="active"' : ''; ?>>All-in-One</a>
                    <a href="printers.php?category=4" <?php echo ($_GET['category'] ?? '') == '4' ? 'class="active"' : ''; ?>>Photo</a>
                    <a href="printers.php?category=5" <?php echo ($_GET['category'] ?? '') == '5' ? 'class="active"' : ''; ?>>Ink Cartridges</a>
                </div>
            </div>

            <h1 class="page-title"><?php echo $page_title; ?></h1>

            <div class="products-grid">
                <?php
                if ($products->rowCount() > 0) {
                    while ($row = $products->fetch(PDO::FETCH_ASSOC)):
                        extract($row);
                        ?>
                        <div class="product-card">
                            <div class="product-image">
                                <a href="product_details.php?id=<?php echo (int) $id; ?>">
                                    <img src="<?php echo htmlspecialchars($image_url); ?>"
                                        alt="<?php echo htmlspecialchars($name); ?>">
                                </a>
                            </div>
                            <div class="product-info">
                                <h3 class="product-name">
                                    <a href="product_details.php?id=<?php echo (int) $id; ?>">
                                        <?php echo htmlspecialchars($name); ?>
                                    </a>
                                </h3>
                                <p class="brand"><?php echo htmlspecialchars($brand); ?> -
                                    <?php echo htmlspecialchars($model); ?>
                                </p>
                                <p class="category"><?php echo htmlspecialchars($category_name); ?></p>
                                <p class="specifications"><?php echo htmlspecialchars($specifications); ?></p>
                                <div class="price-stock">
                                    <p class="price"><strong>Price -</strong> $<?php echo number_format($price, 2); ?></p>
                                    <!-- <p class="stock">Stock: <?php echo $stock_quantity; ?></p> -->
                                </div>

                                <?php if ($stock_quantity > 0): ?>
                                    <form class="add-to-cart-form" method="POST" action="cart_actions.php">
                                        <input type="hidden" name="action" value="add">
                                        <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                                        <div class="quantity-controls">
                                            <label for="quantity_<?php echo $id; ?>">Quantity:</label>
                                            <input type="number" id="quantity_<?php echo $id; ?>" name="quantity" value="1" min="1"
                                                max="<?php echo $stock_quantity; ?>">
                                        </div>
                                        <button type="submit" class="btn-primary">Add to Cart</button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn-disabled" disabled>Out of Stock</button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php
                    endwhile;
                } else {
                    echo '<div class="no-products"><p>No products found.</p></div>';
                }
                ?>
            </div>
            <?php

            $link_prefix = 'printers.php?page=';
            ?>
            <nav class="pagination" aria-label="Products pagination">
                <?php $prev_disabled = $page <= 1 ? 'disabled' : ''; ?>
                <a class="page-link <?php echo $prev_disabled; ?>"
                    href="<?php echo $page > 1 ? $link_prefix . ($page - 1) : 'javascript:void(0)'; ?>">&laquo; Prev</a>

                <?php for ($p = 1; $p <= $pagination_total_pages; $p++): ?>
                    <?php $active = $p == $page ? 'active' : ''; ?>
                    <a class="page-link <?php echo $active; ?>"
                        href="<?php echo $link_prefix . $p; ?>"><?php echo $p; ?></a>
                <?php endfor; ?>

                <?php $next_disabled = $page >= $pagination_total_pages ? 'disabled' : ''; ?>
                <a class="page-link <?php echo $next_disabled; ?>"
                    href="<?php echo $page < $pagination_total_pages ? $link_prefix . ($page + 1) : 'javascript:void(0)'; ?>">Next
                    &raquo;</a>
            </nav>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="./assets/js/script.js"></script>
</body>

</html>
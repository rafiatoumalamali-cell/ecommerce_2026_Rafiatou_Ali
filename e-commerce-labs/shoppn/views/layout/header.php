
<?php
// header.php — View layout layer. HTML + minimal session-based PHP only. No SQL here.
require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../controllers/CartController.php';

$headerCartController = new CartController();
$headerCartCount = $headerCartController->getCartCount(get_ip());
$headerCartTotal = $headerCartController->getCartTotal(get_ip());
$stylesheetPath = __DIR__ . '/../../css/style.css';
$stylesheetVersion = file_exists($stylesheetPath) ? (string) filemtime($stylesheetPath) : '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shoppn</title>
    <link rel="stylesheet" href="/e-commerce-labs/shoppn/css/style.css?v=<?php echo htmlspecialchars($stylesheetVersion, ENT_QUOTES, 'UTF-8'); ?>">
</head>
<body>

<header class="site-header">
    <div class="logo">
        <a href="/e-commerce-labs/shoppn/index.php">Shoppn</a>
    </div>

    <form class="search-form"
          action="/e-commerce-labs/shoppn/views/search_results.php"
          method="GET">
        <input type="text" name="user_query" placeholder="Search products..." value="<?= htmlspecialchars(is_string($_GET['user_query'] ?? null) ? $_GET['user_query'] : '', ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit">Search</button>
    </form>

    <nav class="main-nav">
        <ul>
            <li><a href="/e-commerce-labs/shoppn/index.php">Home</a></li>

            <?php if (is_logged_in()): ?>
                <li>Welcome, <?= htmlspecialchars($_SESSION['customer_name'] ?? '') ?></li>
                <li><a href="/e-commerce-labs/shoppn/views/account/my_account.php">My Account</a></li>

                <?php if (is_admin()): ?>
                    <li><a href="/e-commerce-labs/shoppn/views/admin/users.php">Users</a></li>
                    <li><a href="/e-commerce-labs/shoppn/views/admin/brand.php">Brands</a></li>
                    <li><a href="/e-commerce-labs/shoppn/views/admin/category.php">Categories</a></li>
                    <li><a href="/e-commerce-labs/shoppn/views/admin/product.php">Products</a></li>
                    <li><a href="/e-commerce-labs/shoppn/views/admin/inventory.php">Inventory</a></li>
                <?php endif; ?>

                <li><a href="/e-commerce-labs/shoppn/logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="/e-commerce-labs/shoppn/views/register.php">Register</a></li>
                <li><a href="/e-commerce-labs/shoppn/views/login.php">Login</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <div class="header-cart-summary">
        <span><strong>Shopping Cart</strong></span>
        <span>Total Items: <?php echo (int) $headerCartCount; ?></span>
        <span>Total Price: <?php echo htmlspecialchars(format_ghs_price($headerCartTotal)); ?></span>
        <a href="/e-commerce-labs/shoppn/views/cart.php">Go to Cart <span aria-hidden="true">→</span></a>
    </div>
</header>

<?php if (!empty($_SESSION['cart_notice']) && is_array($_SESSION['cart_notice'])): ?>
    <div class="cart-notice cart-notice-<?php echo htmlspecialchars($_SESSION['cart_notice']['type'] ?? 'info'); ?>" role="status">
        <?php echo htmlspecialchars($_SESSION['cart_notice']['message'] ?? ''); ?>
    </div>
    <?php unset($_SESSION['cart_notice']); ?>
<?php endif; ?>
<?php
// header.php — View layout layer. HTML + minimal session-based PHP only. No SQL here.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shoppn</title>
    <link rel="stylesheet" href="/e-commerce-labs/shoppn/css/style.css">
</head>
<body>

<header class="site-header">
    <div class="logo">
        <a href="/e-commerce-labs/shoppn/index.php">Shoppn</a>
    </div>

    <form class="search-form" action="/e-commerce-labs/shoppn/views/search_results.php" method="GET">
        <input type="text" name="user_query" placeholder="Search products...">
        <button type="submit">Search</button>
    </form>

    <nav class="main-nav">
        <ul>
            <li><a href="/e-commerce-labs/shoppn/index.php">Home</a></li>

            <?php if (is_logged_in()): ?>
                <li>Welcome, <?= htmlspecialchars($_SESSION['customer_name'] ?? '') ?></li>
                <li><a href="/e-commerce-labs/shoppn/views/account/my_account.php">My Account</a></li>

                <?php if (is_admin()): ?>
                    <li><a href="/e-commerce-labs/shoppn/views/admin/brand.php">Brands</a></li>
                    <li><a href="/e-commerce-labs/shoppn/views/admin/category.php">Categories</a></li>
                    <li><a href="/e-commerce-labs/shoppn/views/admin/product.php">Products</a></li>
                <?php endif; ?>

                <li><a href="/e-commerce-labs/shoppn/views/cart.php">Cart</a></li>
                <li><a href="/e-commerce-labs/shoppn/logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="/e-commerce-labs/shoppn/views/register.php">Register</a></li>
                <li><a href="/e-commerce-labs/shoppn/views/login.php">Login</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
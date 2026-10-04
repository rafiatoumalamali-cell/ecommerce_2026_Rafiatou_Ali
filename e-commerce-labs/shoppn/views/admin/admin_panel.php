<?php
require_once __DIR__ . '/../../core/core.php';
require_admin();
include __DIR__ . '/../layout/header.php';
?>

<main>
    <h2>Admin Panel</h2>

    <div class="admin-dashboard">
        <div class="admin-card">
            <span class="card-icon">👥</span>
            <h3><a href="/e-commerce-labs/shoppn/views/admin/users.php">Manage Users</a></h3>
            <p>Review customer accounts, edit profiles, and manage roles.</p>
        </div>

        <div class="admin-card">
            <span class="card-icon">🏷️</span>
            <h3><a href="/e-commerce-labs/shoppn/views/admin/brand.php">Manage Brands</a></h3>
            <p>Create, edit, or remove product brands.</p>
        </div>

        <div class="admin-card">
            <span class="card-icon">📂</span>
            <h3><a href="/e-commerce-labs/shoppn/views/admin/category.php">Manage Categories</a></h3>
            <p>Organize product categories and keep inventory structured.</p>
        </div>

        <div class="admin-card">
            <span class="card-icon">🛍️</span>
            <h3><a href="/e-commerce-labs/shoppn/views/admin/product.php">Manage Products</a></h3>
            <p>Control stock, pricing, and catalog visibility.</p>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../layout/footer.php'; ?>

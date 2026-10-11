<?php
require_once __DIR__ . '/../../core/core.php';
require_admin();
require_once __DIR__ . '/../../controllers/ProductController.php';

$products = (new ProductController())->getAllProducts();
$lowStockCount = 0;
$outOfStockCount = 0;
if (is_array($products)) {
    foreach ($products as $product) {
        $quantity = (int) $product['stock_quantity'];
        if ($quantity === 0) {
            $outOfStockCount++;
        } elseif ($quantity <= 5) {
            $lowStockCount++;
        }
    }
}

include __DIR__ . '/../layout/header.php';
?>

<main>
    <div class="page-header">
        <div>
            <p class="eyebrow">Admin tools</p>
            <h2>Inventory Management</h2>
            <p>Review stock levels and update available quantities.</p>
        </div>
        <a class="btn btn-secondary" href="/e-commerce-labs/shoppn/views/admin/product.php">Manage Products</a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <p class="flash-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <p class="flash-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
    <?php endif; ?>

    <?php if ($products === false): ?>
        <p class="flash-error">Inventory could not be loaded. Please refresh and try again.</p>
    <?php else: ?>
        <div class="inventory-summary">
            <p><strong><?php echo count($products); ?></strong> products</p>
            <p><strong class="inventory-low-stock"><?php echo $lowStockCount; ?></strong> low-stock products (5 or fewer)</p>
            <p><strong class="inventory-out-of-stock"><?php echo $outOfStockCount; ?></strong> out of stock</p>
        </div>

        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Brand</th>
                        <th>Current stock</th>
                        <th>Update stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($products !== []): ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['product_title']); ?></td>
                                <td><?php echo htmlspecialchars($product['cat_name']); ?></td>
                                <td><?php echo htmlspecialchars($product['brand_name']); ?></td>
                                <td>
                                    <?php echo (int) $product['stock_quantity']; ?>
                                    <?php if ((int) $product['stock_quantity'] === 0): ?>
                                        <strong class="inventory-out-of-stock">Out of stock</strong>
                                    <?php elseif ((int) $product['stock_quantity'] <= 5): ?>
                                        <strong class="inventory-low-stock">Low stock</strong>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form class="inventory-update-form" method="post" action="/e-commerce-labs/shoppn/actions/update_inventory_action.php">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                                        <input type="hidden" name="product_id" value="<?php echo (int) $product['product_id']; ?>">
                                        <label class="visually-hidden" for="stock-<?php echo (int) $product['product_id']; ?>">New stock quantity for <?php echo htmlspecialchars($product['product_title']); ?></label>
                                        <input id="stock-<?php echo (int) $product['product_id']; ?>" type="number" name="stock_quantity" min="0" max="2147483647" step="1" required value="<?php echo (int) $product['stock_quantity']; ?>">
                                        <button class="btn btn-secondary btn-compact" type="submit">Save</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5">No products are available to manage.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/../layout/footer.php'; ?>

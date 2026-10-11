<?php
require_once __DIR__ . '/../../core/core.php';
require_admin();
require_once __DIR__ . '/../../controllers/ProductController.php';

$controller = new ProductController();
$categories = $controller->getAllCategories();
$brands = $controller->getAllBrands();
$products = $controller->getAllProducts();
$editId = filter_var($_GET['edit_id'] ?? null, FILTER_VALIDATE_INT);
$editProduct = $editId !== false && $editId !== null && $editId > 0
    ? $controller->getProductById($editId)
    : null;

if ($editId !== false && $editId !== null && $editId > 0 && !$editProduct) {
    $_SESSION['error'] = 'Product not found.';
}

$formAction = $editProduct
    ? '/e-commerce-labs/shoppn/actions/update_product_action.php'
    : '/e-commerce-labs/shoppn/actions/add_product_action.php';

include __DIR__ . '/../layout/header.php';
?>

<main>
    <h2><?php echo $editProduct ? 'Edit Product' : 'Manage Products'; ?></h2>

    <?php if (isset($_SESSION['success'])): ?>
        <p class="flash-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <p class="flash-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
    <?php endif; ?>

    <?php if (empty($categories) || empty($brands)): ?>
        <p class="flash-error">Add at least one category and one brand before creating products.</p>
    <?php else: ?>
        <form class="auth-form admin-form" action="<?php echo $formAction; ?>" method="post" enctype="multipart/form-data" id="product-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
            <?php if ($editProduct): ?>
                <input type="hidden" name="product_id" value="<?php echo (int) $editProduct['product_id']; ?>">
                <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($editProduct['product_image'] ?? ''); ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="product_title">Product Title</label>
                <input type="text" id="product_title" name="product_title" maxlength="200" required value="<?php echo htmlspecialchars($editProduct['product_title'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="product_price">Price (GHS)</label>
                <input type="number" id="product_price" name="product_price" min="0" step="0.01" required value="<?php echo htmlspecialchars((string) ($editProduct['product_price'] ?? '')); ?>">
            </div>

            <div class="form-group">
                <label for="stock_quantity">Stock quantity</label>
                <input type="number" id="stock_quantity" name="stock_quantity" min="0" step="1" max="2147483647" required value="<?php echo htmlspecialchars((string) ($editProduct['stock_quantity'] ?? '0')); ?>">
                <small>Enter the number of units currently available. Use 0 to mark the product out of stock.</small>
            </div>

            <div class="form-group">
                <label for="product_desc">Description</label>
                <textarea id="product_desc" name="product_desc" maxlength="500" rows="4"><?php echo htmlspecialchars($editProduct['product_desc'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="product_keywords">Keywords</label>
                <input type="text" id="product_keywords" name="product_keywords" maxlength="100" value="<?php echo htmlspecialchars($editProduct['product_keywords'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="product_cat">Category</label>
                <select id="product_cat" name="product_cat" required>
                    <option value="">Select a category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo (int) $category['cat_id']; ?>" <?php echo isset($editProduct['product_cat']) && (int) $editProduct['product_cat'] === (int) $category['cat_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['cat_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="product_brand">Brand</label>
                <select id="product_brand" name="product_brand" required>
                    <option value="">Select a brand</option>
                    <?php foreach ($brands as $brand): ?>
                        <option value="<?php echo (int) $brand['brand_id']; ?>" <?php echo isset($editProduct['product_brand']) && (int) $editProduct['product_brand'] === (int) $brand['brand_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($brand['brand_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if ($editProduct && !empty($editProduct['product_image'])): ?>
                <div class="form-group">
                    <label>Current Image</label>
                    <img src="/e-commerce-labs/shoppn/images/products/<?php echo rawurlencode(basename($editProduct['product_image'])); ?>" alt="<?php echo htmlspecialchars($editProduct['product_title']); ?>" class="product-admin-preview">
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="product_image">Product Image<?php echo $editProduct ? ' (optional)' : ''; ?></label>
                <input type="file" id="product_image" name="product_image" accept="image/jpeg,image/png,image/gif,image/webp" <?php echo $editProduct ? '' : 'required'; ?>>
                <small>JPEG, PNG, GIF, or WebP; maximum 5 MB.</small>
            </div>

            <button type="submit"><?php echo $editProduct ? 'Save Product Changes' : 'Add Product'; ?></button>
            <?php if ($editProduct): ?>
                <a class="btn btn-secondary" href="/e-commerce-labs/shoppn/views/admin/product.php">Cancel editing</a>
            <?php endif; ?>
        </form>
    <?php endif; ?>

    <h3>Products</h3>
    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Brand</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <?php if (!empty($product['product_image'])): ?>
                                    <img src="/e-commerce-labs/shoppn/images/products/<?php echo rawurlencode(basename($product['product_image'])); ?>" alt="" class="product-admin-thumbnail">
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($product['product_title']); ?></td>
                            <td><?php echo htmlspecialchars($product['cat_name']); ?></td>
                            <td><?php echo htmlspecialchars($product['brand_name']); ?></td>
                            <td><?php echo htmlspecialchars(format_ghs_price($product['product_price'])); ?></td>
                            <td>
                                <?php echo (int) $product['stock_quantity']; ?>
                                <?php if ((int) $product['stock_quantity'] === 0): ?>
                                    <strong class="inventory-out-of-stock">Out of stock</strong>
                                <?php elseif ((int) $product['stock_quantity'] <= 5): ?>
                                    <strong class="inventory-low-stock">Low stock</strong>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="product.php?edit_id=<?php echo (int) $product['product_id']; ?>" class="btn btn-secondary btn-compact">Edit</a>
                                    <form method="post" action="/e-commerce-labs/shoppn/actions/delete_product_action.php" onsubmit="return confirm('Permanently delete this product? It will also be removed from active carts. Products in order history cannot be deleted.');">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                                        <input type="hidden" name="product_id" value="<?php echo (int) $product['product_id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-compact">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php elseif ($products === false): ?>
                    <tr><td colspan="7">Products could not be loaded. Please refresh and try again.</td></tr>
                <?php else: ?>
                    <tr><td colspan="7">No products found. Add your first product above.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<script src="/e-commerce-labs/shoppn/js/product-upload.js" defer></script>
<?php include __DIR__ . '/../layout/footer.php'; ?>

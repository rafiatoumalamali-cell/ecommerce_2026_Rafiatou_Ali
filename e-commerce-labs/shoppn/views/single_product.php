<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

$productId = filter_var($_GET['pro_id'] ?? null, FILTER_VALIDATE_INT);
if ($productId === false || $productId === null || $productId <= 0) {
    redirect('/e-commerce-labs/shoppn/index.php');
}

$controller = new ProductController();
$product = $controller->getProductById($productId);
if (!$product) {
    redirect('/e-commerce-labs/shoppn/index.php');
}

include __DIR__ . '/layout/header.php';
?>

<main>
    <?php include __DIR__ . '/layout/sidebar.php'; ?>
    <section class="single-product-panel">
        <div class="single-product-image">
            <?php if (!empty($product['product_image'])): ?>
                <img src="/e-commerce-labs/shoppn/images/products/<?php echo rawurlencode(basename($product['product_image'])); ?>" alt="<?php echo htmlspecialchars($product['product_title']); ?>">
            <?php else: ?>
                <span class="catalogue-no-image">Image unavailable</span>
            <?php endif; ?>
        </div>
        <div class="single-product-info">
            <p class="eyebrow"><?php echo htmlspecialchars($product['brand_name']); ?> · <?php echo htmlspecialchars($product['cat_name']); ?></p>
            <h2><?php echo htmlspecialchars($product['product_title']); ?></h2>
            <p class="single-product-price"><?php echo htmlspecialchars(format_ghs_price($product['product_price'])); ?></p>
            <?php if ((int) $product['stock_quantity'] > 0): ?>
                <p class="inventory-available"><?php echo (int) $product['stock_quantity']; ?> available</p>
            <?php else: ?>
                <p class="inventory-out-of-stock">Out of stock</p>
            <?php endif; ?>
            <h3>Description</h3>
            <p><?php echo nl2br(htmlspecialchars($product['product_desc'] ?? 'No description available.')); ?></p>
            <?php if (!empty($product['product_keywords'])): ?>
                <h3>Keywords</h3>
                <p class="catalogue-product-meta"><?php echo htmlspecialchars($product['product_keywords']); ?></p>
            <?php endif; ?>
            <div class="single-product-actions">
                <?php if ((int) $product['stock_quantity'] > 0): ?>
                    <a class="btn btn-cart" href="/e-commerce-labs/shoppn/actions/add_to_cart_action.php?add_cart=<?php echo (int) $product['product_id']; ?>">Add to Cart</a>
                <?php else: ?>
                    <span class="inventory-out-of-stock">Currently unavailable</span>
                <?php endif; ?>
                <a class="btn btn-secondary" href="/e-commerce-labs/shoppn/index.php">Continue shopping</a>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/layout/footer.php'; ?>

<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

$categoryFilter = filter_var($_GET['category_id'] ?? null, FILTER_VALIDATE_INT);
$brandFilter = filter_var($_GET['brand_id'] ?? null, FILTER_VALIDATE_INT);
$categoryId = $categoryFilter !== false && $categoryFilter !== null && $categoryFilter > 0 ? $categoryFilter : null;
$brandId = $brandFilter !== false && $brandFilter !== null && $brandFilter > 0 ? $brandFilter : null;
$invalidFilter = (isset($_GET['category_id']) && $categoryId === null)
    || (isset($_GET['brand_id']) && $brandId === null);

$controller = new ProductController();
$products = $invalidFilter
    ? []
    : ($categoryId !== null
        ? $controller->getProductsByCategory($categoryId)
        : ($brandId !== null ? $controller->getProductsByBrand($brandId) : $controller->getAllProducts()));
$categories = $controller->getAllCategories();
$brands = $controller->getAllBrands();
$pageTitle = 'All Products';

if ($categoryId !== null) {
    foreach ($categories as $category) {
        if ((int) $category['cat_id'] === $categoryId) {
            $pageTitle = $category['cat_name'] . ' Products';
            break;
        }
    }
}
if ($brandId !== null) {
    foreach ($brands as $brand) {
        if ((int) $brand['brand_id'] === $brandId) {
            $pageTitle = $brand['brand_name'] . ($categoryId !== null ? ' Products' : ' Products');
            break;
        }
    }
}

include __DIR__ . '/layout/header.php';
?>

<main>
    <?php include __DIR__ . '/layout/sidebar.php'; ?>
    <section class="catalogue-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">Shoppn catalogue</p>
                <h2><?php echo htmlspecialchars($pageTitle); ?></h2>
            </div>
            <span class="catalogue-count">
                <?php echo is_array($products) ? count($products) : 0; ?>
                <?php echo is_array($products) && count($products) === 1 ? ' product' : ' products'; ?>
            </span>
        </div>

        <?php if ($products === false): ?>
            <p class="flash-error">Products could not be loaded. Please refresh and try again.</p>
        <?php else: ?>
            <?php
            $emptyMessage = $invalidFilter
                ? 'The selected category or brand is invalid.'
                : 'No products found for this selection yet.';
            require __DIR__ . '/layout/product_cards.php';
            ?>
        <?php endif; ?>
    </section>
</main>

<?php include __DIR__ . '/layout/footer.php'; ?>

<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

$controller = new ProductController();
$categoryId = null;
$brandId = null;
$invalidFilter = false;

if (isset($_GET['cat'])) {
    $categoryId = filter_var($_GET['cat'], FILTER_VALIDATE_INT);
    $invalidFilter = $categoryId === false || $categoryId <= 0;
} elseif (isset($_GET['brand'])) {
    $brandId = filter_var($_GET['brand'], FILTER_VALIDATE_INT);
    $invalidFilter = $brandId === false || $brandId <= 0;
}

if ($invalidFilter) {
    $products = [];
    $listingTitle = 'Products';
} elseif ($categoryId !== null) {
    $products = $controller->getProductsByCategory($categoryId);
    $selectedCategory = $controller->getCategoryById($categoryId);
    $listingTitle = $selectedCategory ? $selectedCategory['cat_name'] . ' Products' : 'Products';
} elseif ($brandId !== null) {
    $products = $controller->getProductsByBrand($brandId);
    $selectedBrand = $controller->getBrandById($brandId);
    $listingTitle = $selectedBrand ? $selectedBrand['brand_name'] . ' Products' : 'Products';
} else {
    $products = $controller->getFeaturedProducts();
    $listingTitle = 'Featured Products';
}

require_once __DIR__ . '/layout/header.php';
?>

<main class="home-layout">
    <?php require __DIR__ . '/layout/sidebar.php'; ?>
    <section class="home-content">
        <div class="home-banner" role="img" aria-label="Shoppn fashion, home essentials and everyday products"></div>
        <div class="hero-copy">
            <div class="cta-section">
                <a href="/e-commerce-labs/shoppn/index.php" class="cta-button">Explore products</a>
                <a href="/e-commerce-labs/shoppn/views/register.php" class="hero-secondary-link">Join Shoppn <span aria-hidden="true">→</span></a>
            </div>
        </div>

        <section class="home-products" id="home-products" aria-labelledby="home-products-title">
            <div class="page-header">
                <div>
                    <p class="eyebrow">Find your next favorite</p>
                    <h2 id="home-products-title"><?php echo htmlspecialchars($listingTitle); ?></h2>
                </div>
                <?php if (!$invalidFilter): ?>
                    <span class="catalogue-count"><?php echo count($products); ?> <?php echo count($products) === 1 ? 'product' : 'products'; ?></span>
                <?php endif; ?>
            </div>
            <?php
            $emptyMessage = $invalidFilter
                ? 'The selected category or brand is invalid.'
                : 'No products found. Please check back soon.';
            require __DIR__ . '/layout/product_cards.php';
            ?>
        </section>
    </section>
</main>

<?php
require_once __DIR__ . '/layout/footer.php';
?>
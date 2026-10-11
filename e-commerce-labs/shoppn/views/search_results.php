<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

$queryValue = $_GET['user_query'] ?? '';
$query = is_string($queryValue) ? trim($queryValue) : '';
$controller = new ProductController();
$products = $query === '' ? [] : $controller->searchProducts($query);

include __DIR__ . '/layout/header.php';
?>

<main>
    <?php include __DIR__ . '/layout/sidebar.php'; ?>
    <section class="catalogue-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">Product search</p>
                <h2>Search Results</h2>
                <?php if ($query !== ''): ?>
                    <p class="catalogue-search-query">Showing results for “<?php echo htmlspecialchars($query); ?>”</p>
                <?php endif; ?>
            </div>
            <span class="catalogue-count"><?php echo count($products); ?> <?php echo count($products) === 1 ? 'product' : 'products'; ?></span>
        </div>
        <?php
        $emptyMessage = $query === ''
            ? 'Enter a product name or keyword in the search box.'
            : 'No products found. Try another name or keyword.';
        require __DIR__ . '/layout/product_cards.php';
        ?>
    </section>
</main>

<?php include __DIR__ . '/layout/footer.php'; ?>

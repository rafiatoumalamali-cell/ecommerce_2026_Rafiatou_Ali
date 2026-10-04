<?php
require_once __DIR__ . '/../../controllers/ProductController.php';

$browseController = new ProductController();
$browseCategories = $browseController->getAllCategories();
$browseBrands = $browseController->getAllBrands();
?>
<aside class="sidebar" aria-label="Shop navigation">
    <div class="sidebar-heading">
        <span class="sidebar-kicker">Explore</span>
        <h2>Shop by</h2>
    </div>

    <nav class="shop-sidebar-nav">
        <section class="sidebar-group products" aria-labelledby="sidebar-products-title">
            <h3 id="sidebar-products-title"><span aria-hidden="true">▦</span> Products</h3>
            <ul>
                <li>
                    <a class="sidebar-all-products" href="/e-commerce-labs/shoppn/views/all_products.php">
                        <span>Browse all products</span>
                        <span class="sidebar-chevron" aria-hidden="true">›</span>
                    </a>
                </li>
            </ul>
        </section>

        <section class="sidebar-group categories" aria-labelledby="sidebar-categories-title">
            <h3 id="sidebar-categories-title"><span aria-hidden="true">◇</span> Categories</h3>
            <ul>
                <?php if (!empty($browseCategories)): ?>
                    <?php foreach ($browseCategories as $category): ?>
                        <li>
                            <a href="/e-commerce-labs/shoppn/views/all_products.php?category_id=<?php echo (int) $category['cat_id']; ?>">
                                <?php echo htmlspecialchars($category['cat_name']); ?>
                                <span class="sidebar-chevron" aria-hidden="true">›</span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="sidebar-empty">No categories yet</li>
                <?php endif; ?>
            </ul>
        </section>

        <section class="sidebar-group brands" aria-labelledby="sidebar-brands-title">
            <h3 id="sidebar-brands-title"><span aria-hidden="true">✳</span> Brands</h3>
            <ul>
                <?php if (!empty($browseBrands)): ?>
                    <?php foreach ($browseBrands as $brand): ?>
                        <li>
                            <a href="/e-commerce-labs/shoppn/views/all_products.php?brand_id=<?php echo (int) $brand['brand_id']; ?>">
                                <?php echo htmlspecialchars($brand['brand_name']); ?>
                                <span class="sidebar-chevron" aria-hidden="true">›</span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="sidebar-empty">No brands yet</li>
                <?php endif; ?>
            </ul>
        </section>
    </nav>
</aside>

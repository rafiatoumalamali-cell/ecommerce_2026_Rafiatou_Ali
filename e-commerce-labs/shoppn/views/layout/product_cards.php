<?php if (!empty($products)): ?>
    <div class="product-grid">
        <?php foreach ($products as $product): ?>
            <article class="product-card catalogue-product-card">
                <a class="catalogue-product-image" href="/e-commerce-labs/shoppn/views/single_product.php?pro_id=<?php echo (int) $product['product_id']; ?>">
                    <?php if (!empty($product['product_image'])): ?>
                        <img src="/e-commerce-labs/shoppn/images/products/<?php echo rawurlencode(basename($product['product_image'])); ?>" alt="<?php echo htmlspecialchars($product['product_title']); ?>" loading="lazy">
                    <?php else: ?>
                        <span class="catalogue-no-image">Image unavailable</span>
                    <?php endif; ?>
                </a>
                <p class="catalogue-product-meta"><?php echo htmlspecialchars($product['brand_name']); ?> · <?php echo htmlspecialchars($product['cat_name']); ?></p>
                <h3>
                    <a class="catalogue-product-title" href="/e-commerce-labs/shoppn/views/single_product.php?pro_id=<?php echo (int) $product['product_id']; ?>">
                        <?php echo htmlspecialchars($product['product_title']); ?>
                    </a>
                </h3>
                <?php if (!empty($product['product_desc'])): ?>
                    <p class="catalogue-product-description"><?php echo htmlspecialchars($product['product_desc']); ?></p>
                <?php endif; ?>
                <p class="product-price"><?php echo htmlspecialchars(format_ghs_price($product['product_price'])); ?></p>
                <div class="catalogue-product-actions">
                    <a class="btn btn-secondary btn-compact" href="/e-commerce-labs/shoppn/views/single_product.php?pro_id=<?php echo (int) $product['product_id']; ?>">Details</a>
                    <?php if ((int) $product['stock_quantity'] > 0): ?>
                        <a class="btn btn-cart btn-compact" href="/e-commerce-labs/shoppn/actions/add_to_cart_action.php?add_cart=<?php echo (int) $product['product_id']; ?>">Add to Cart</a>
                    <?php else: ?>
                        <span class="inventory-out-of-stock">Out of stock</span>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p class="catalogue-empty"><?php echo htmlspecialchars($emptyMessage ?? 'No products found.'); ?></p>
<?php endif; ?>

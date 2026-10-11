<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';

$cartController = new CartController();
$cartItems = $cartController->getCartItems(get_ip());
$cartCount = $cartController->getCartCount(get_ip());
$cartTotal = $cartController->getCartTotal(get_ip());
$cartHasUnavailableQuantity = false;
foreach ($cartItems as $cartItem) {
    if ((int) $cartItem['qty'] > (int) $cartItem['stock_quantity']) {
        $cartHasUnavailableQuantity = true;
        break;
    }
}

include __DIR__ . '/layout/header.php';
?>

<main>
    <div class="page-header cart-page-header">
        <div>
            <p class="eyebrow">Your selection</p>
            <h2>Shopping Cart</h2>
        </div>
        <span class="catalogue-count"><?php echo (int) $cartCount; ?> <?php echo $cartCount === 1 ? 'item' : 'items'; ?></span>
    </div>

    <?php if (!empty($cartItems)): ?>
        <form id="cart-form" class="cart-table-wrap" method="post" action="/e-commerce-labs/shoppn/actions/remove_from_cart_action.php">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
            <div class="table-wrap">
                <table class="admin-table cart-table">
                    <thead>
                        <tr>
                            <th><span class="visually-hidden">Remove</span></th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Available</th>
                            <th>Unit Price</th>
                            <th>Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $index => $item): ?>
                            <tr>
                                <td>
                                    <label class="cart-remove-select" for="remove-<?php echo (int) $item['p_id']; ?>">
                                        <input type="checkbox" id="remove-<?php echo (int) $item['p_id']; ?>" name="remove[]" value="<?php echo (int) $item['p_id']; ?>">
                                        <span>Remove</span>
                                    </label>
                                </td>
                                <td>
                                    <div class="cart-product-cell">
                                        <?php if (!empty($item['product_image'])): ?>
                                            <img src="/e-commerce-labs/shoppn/images/products/<?php echo rawurlencode(basename($item['product_image'])); ?>" alt="" class="cart-product-image">
                                        <?php endif; ?>
                                        <a href="/e-commerce-labs/shoppn/views/single_product.php?pro_id=<?php echo (int) $item['p_id']; ?>">
                                            <?php echo htmlspecialchars($item['product_title']); ?>
                                        </a>
                                    </div>
                                </td>
                                <td>
                                    <input class="cart-quantity-input" type="number" name="qty[]" min="1" max="<?php echo max(1, (int) $item['stock_quantity']); ?>" step="1" required value="<?php echo (int) $item['qty']; ?>" aria-label="Quantity for <?php echo htmlspecialchars($item['product_title']); ?>" form="cart-form">
                                    <input type="hidden" name="update[]" value="<?php echo (int) $item['p_id']; ?>" form="cart-form">
                                </td>
                                <td>
                                    <?php echo (int) $item['stock_quantity']; ?>
                                    <?php if ((int) $item['stock_quantity'] === 0): ?>
                                        <strong class="inventory-out-of-stock">Out of stock</strong>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars(format_ghs_price($item['product_price'])); ?></td>
                                <td><?php echo htmlspecialchars(format_ghs_price((int) $item['qty'] * (float) $item['product_price'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5">Subtotal</th>
                            <th><?php echo htmlspecialchars(format_ghs_price($cartTotal)); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <?php if ($cartHasUnavailableQuantity): ?>
                <p class="flash-error">One or more quantities in your cart exceed current stock. Update or remove those items before checkout.</p>
            <?php endif; ?>
            <div class="cart-actions">
                <button class="btn btn-danger" type="submit">Remove Selected</button>
                <button class="btn btn-secondary" type="submit" formaction="/e-commerce-labs/shoppn/actions/update_qty_action.php">Update Quantities</button>
                <a class="btn btn-secondary" href="/e-commerce-labs/shoppn/index.php">Continue Shopping</a>
                <?php if ($cartHasUnavailableQuantity): ?>
                    <button class="btn btn-cart" type="button" disabled>Checkout</button>
                <?php else: ?>
                    <a class="btn btn-cart" href="/e-commerce-labs/shoppn/views/checkout.php">Checkout</a>
                <?php endif; ?>
            </div>
        </form>
    <?php else: ?>
        <section class="cart-empty-state">
            <span class="cart-empty-icon" aria-hidden="true">🛍</span>
            <h3>Your cart is empty</h3>
            <p>Browse the catalogue and add something you like.</p>
            <a class="btn btn-cart" href="/e-commerce-labs/shoppn/index.php">Explore Products</a>
        </section>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/layout/footer.php'; ?>

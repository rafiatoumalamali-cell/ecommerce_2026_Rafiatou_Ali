<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';

$cartController = new CartController();
$cartItems = $cartController->getCartItems(get_ip());
$cartTotal = $cartController->getCartTotal(get_ip());
$shippingDefaults = [
    'name' => $_SESSION['customer_name'] ?? '',
    'email' => $_SESSION['customer_email'] ?? '',
    'contact' => '',
    'address' => '',
    'city' => '',
    'country' => '',
];

if (is_logged_in()) {
    require_once __DIR__ . '/../controllers/CustomerController.php';
    $customerController = new CustomerController();
    $profileResult = $customerController->getCustomerProfile((int) $_SESSION['customer_id']);
    if (($profileResult['status'] ?? '') === 'success') {
        $profile = $profileResult['customer'];
        $shippingDefaults['contact'] = $profile['customer_contact'] ?? '';
        $shippingDefaults['city'] = $profile['customer_city'] ?? '';
        $shippingDefaults['country'] = $profile['customer_country'] ?? '';
    }
}

include __DIR__ . '/layout/header.php';
?>

<main>
    <div class="page-header cart-page-header">
        <div>
            <p class="eyebrow">Almost there</p>
            <h2>Checkout</h2>
        </div>
        <a class="btn btn-secondary" href="/e-commerce-labs/shoppn/views/cart.php">Back to Cart</a>
    </div>

    <?php if (empty($cartItems)): ?>
        <section class="cart-empty-state">
            <h3>Your cart is empty</h3>
            <p>Add products before continuing to checkout.</p>
            <a class="btn btn-cart" href="/e-commerce-labs/shoppn/index.php">Explore Products</a>
        </section>
    <?php else: ?>
        <div class="checkout-layout">
            <section class="checkout-form-panel">
                <h3>Delivery details</h3>
                <p class="checkout-intro">Enter where you would like your order delivered. You can check out as a guest.</p>

                <form class="auth-form admin-form checkout-form" action="/e-commerce-labs/shoppn/actions/process_checkout_action.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">

                    <div class="form-group">
                        <label for="shipping_name">Full name</label>
                        <input type="text" id="shipping_name" name="shipping_name" maxlength="100" autocomplete="name" required value="<?php echo htmlspecialchars($shippingDefaults['name']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="shipping_email">Email for order updates</label>
                        <input type="email" id="shipping_email" name="shipping_email" maxlength="100" autocomplete="email" required value="<?php echo htmlspecialchars($shippingDefaults['email']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="shipping_contact">Phone number</label>
                        <input type="tel" id="shipping_contact" name="shipping_contact" maxlength="30" autocomplete="tel" pattern="[0-9+() -]{7,30}" required value="<?php echo htmlspecialchars($shippingDefaults['contact']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="shipping_address">Delivery address</label>
                        <textarea id="shipping_address" name="shipping_address" maxlength="255" autocomplete="street-address" rows="3" required></textarea>
                    </div>

                    <div class="checkout-location-fields">
                        <div class="form-group">
                            <label for="shipping_city">City</label>
                            <input type="text" id="shipping_city" name="shipping_city" maxlength="100" autocomplete="address-level2" required value="<?php echo htmlspecialchars($shippingDefaults['city']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="shipping_country">Country</label>
                            <input type="text" id="shipping_country" name="shipping_country" maxlength="100" autocomplete="country-name" required value="<?php echo htmlspecialchars($shippingDefaults['country']); ?>">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-cart">Continue to Payment — <?php echo htmlspecialchars(format_ghs_price($cartTotal)); ?></button>
                </form>
            </section>

            <aside class="checkout-summary">
                <h3>Order summary</h3>
                <ul>
                    <?php foreach ($cartItems as $item): ?>
                        <li>
                            <span><?php echo htmlspecialchars($item['product_title']); ?> × <?php echo (int) $item['qty']; ?></span>
                            <strong><?php echo htmlspecialchars(format_ghs_price((int) $item['qty'] * (float) $item['product_price'])); ?></strong>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="checkout-summary-total">
                    <span>Order total</span>
                    <strong><?php echo htmlspecialchars(format_ghs_price($cartTotal)); ?></strong>
                </div>
                <p class="checkout-payment-note">You’ll review your order and pay securely with Paystack on the next step.</p>
            </aside>
        </div>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/layout/footer.php'; ?>

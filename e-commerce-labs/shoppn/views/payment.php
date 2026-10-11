<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';

$shipping = $_SESSION['checkout_shipping'] ?? null;
$cartSnapshot = $_SESSION['checkout_cart_snapshot'] ?? null;
if (!is_array($shipping) || !is_array($cartSnapshot) || $cartSnapshot === []) {
    $_SESSION['cart_notice'] = ['type' => 'error', 'message' => 'Enter your delivery details before proceeding to payment.'];
    redirect('/e-commerce-labs/shoppn/views/checkout.php');
}

$liveCartItems = (new CartController())->getCartItems(get_ip());
$liveItemsById = [];
foreach ($liveCartItems as $item) {
    $liveItemsById[(int) $item['p_id']] = $item;
}
foreach ($cartSnapshot as $item) {
    $productId = (int) ($item['product_id'] ?? 0);
    $currentItem = $liveItemsById[$productId] ?? null;
    if (
        !$currentItem
        || (int) $currentItem['qty'] !== (int) ($item['qty'] ?? 0)
        || (int) $currentItem['stock_quantity'] < (int) ($item['qty'] ?? 0)
    ) {
        $_SESSION['cart_notice'] = [
            'type' => 'error',
            'message' => 'Your cart or available stock changed. Review your cart before continuing to payment.',
        ];
        redirect('/e-commerce-labs/shoppn/views/cart.php');
    }
}

$cartTotal = 0.0;
foreach ($cartSnapshot as $item) {
    $cartTotal += (int) ($item['qty'] ?? 0) * (float) ($item['unit_price'] ?? 0);
}
$amountInPesewas = (int) round($cartTotal * 100);
$publicKey = app_setting('PAYSTACK_PUBLIC_KEY');
$secretKey = app_setting('PAYSTACK_SECRET_KEY');
$paystackConfigured = is_string($publicKey)
    && preg_match('/^pk_(test|live)_[A-Za-z0-9]+$/', $publicKey)
    && is_string($secretKey)
    && preg_match('/^sk_(test|live)_[A-Za-z0-9]+$/', $secretKey);
include __DIR__ . '/layout/header.php';
?>

<main>
    <div class="page-header cart-page-header">
        <div>
            <p class="eyebrow">Secure payment</p>
            <h2>Review &amp; Pay</h2>
        </div>
        <a class="btn btn-secondary" href="/e-commerce-labs/shoppn/views/checkout.php">Back to Delivery Details</a>
    </div>

    <div class="checkout-layout">
        <section class="checkout-form-panel">
            <h3>Delivery details</h3>
            <dl class="order-shipping-details">
                <div><dt>Name</dt><dd><?php echo htmlspecialchars($shipping['name']); ?></dd></div>
                <div><dt>Email</dt><dd><?php echo htmlspecialchars($shipping['email']); ?></dd></div>
                <div><dt>Contact</dt><dd><?php echo htmlspecialchars($shipping['contact']); ?></dd></div>
                <div><dt>Address</dt><dd><?php echo htmlspecialchars($shipping['address']); ?>, <?php echo htmlspecialchars($shipping['city']); ?>, <?php echo htmlspecialchars($shipping['country']); ?></dd></div>
            </dl>

            <?php if ($paystackConfigured && $amountInPesewas > 0): ?>
                <button type="button" class="btn btn-cart paystack-button" id="paystack-button">Pay <?php echo htmlspecialchars(format_ghs_price($cartTotal)); ?> with Paystack</button>
                <p class="checkout-payment-note">Your order is recorded as paid only after Shoppn verifies the transaction directly with Paystack.</p>
            <?php else: ?>
                <?php if (!$paystackConfigured): ?>
                    <p class="flash-error">Paystack is not configured. Set valid PAYSTACK_PUBLIC_KEY and PAYSTACK_SECRET_KEY values in your local .env file to enable payment.</p>
                <?php else: ?>
                    <p class="flash-error">The order total must be greater than zero before payment can continue.</p>
                <?php endif; ?>
            <?php endif; ?>
        </section>

        <aside class="checkout-summary">
            <h3>Order summary</h3>
            <ul>
                <?php foreach ($cartSnapshot as $item): ?>
                    <?php $title = $item['product_title'] ?? ('Product #' . (int) $item['product_id']); ?>
                    <li>
                        <span><?php echo htmlspecialchars($title); ?> × <?php echo (int) $item['qty']; ?></span>
                        <strong><?php echo htmlspecialchars(format_ghs_price((int) $item['qty'] * (float) $item['unit_price'])); ?></strong>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="checkout-summary-total">
                <span>Total (GHS)</span>
                <strong><?php echo htmlspecialchars(format_ghs_price($cartTotal)); ?></strong>
            </div>
        </aside>
    </div>
</main>

<?php if ($paystackConfigured && $amountInPesewas > 0): ?>
    <script src="https://js.paystack.co/v1/inline.js"></script>
    <script>
        document.getElementById('paystack-button').addEventListener('click', function () {
            const handler = PaystackPop.setup({
                key: <?php echo json_encode($publicKey, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
                email: <?php echo json_encode($shipping['email'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
                amount: <?php echo $amountInPesewas; ?>,
                currency: 'GHS',
                callback: function (response) {
                    if (!response || typeof response.reference !== 'string' || response.reference === '') {
                        window.location.href = '/e-commerce-labs/shoppn/views/payment_failed.php';
                        return;
                    }
                    window.location.href = '/e-commerce-labs/shoppn/actions/process_payment_action.php?ref=' + encodeURIComponent(response.reference);
                },
                onClose: function () {
                    window.location.href = '/e-commerce-labs/shoppn/views/payment_failed.php?cancelled=1';
                }
            });
            handler.openIframe();
        });
    </script>
<?php endif; ?>

<?php include __DIR__ . '/layout/footer.php'; ?>

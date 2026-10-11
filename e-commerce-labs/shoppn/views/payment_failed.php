<?php
require_once __DIR__ . '/../core/core.php';

$failure = $_SESSION['payment_failure'] ?? [
    'message' => 'Payment was cancelled or could not be completed.',
    'reference' => '',
    'payment_verified' => false,
];
unset($_SESSION['payment_failure']);

include __DIR__ . '/layout/header.php';
?>

<main>
    <section class="cart-empty-state payment-failure-panel">
        <p class="eyebrow">Payment not completed</p>
        <h2>We could not complete your payment</h2>
        <p><?php echo htmlspecialchars($failure['message']); ?></p>
        <?php if (!empty($failure['reference'])): ?>
            <p>Payment reference: <strong><?php echo htmlspecialchars($failure['reference']); ?></strong></p>
        <?php endif; ?>

        <?php if (!empty($failure['payment_verified'])): ?>
            <p class="checkout-payment-note">Do not make another payment for this order yet. Contact support with the reference above so the verified transaction can be reconciled.</p>
        <?php elseif (
            isset($_SESSION['checkout_shipping'], $_SESSION['checkout_cart_snapshot'])
            && is_array($_SESSION['checkout_shipping'])
            && is_array($_SESSION['checkout_cart_snapshot'])
        ): ?>
            <a class="btn btn-cart" href="/e-commerce-labs/shoppn/views/payment.php">Try Paystack Again</a>
        <?php else: ?>
            <a class="btn btn-secondary" href="/e-commerce-labs/shoppn/views/checkout.php">Return to Checkout</a>
        <?php endif; ?>

        <a class="btn btn-secondary" href="/e-commerce-labs/shoppn/views/cart.php">View Cart</a>
    </section>
</main>

<?php include __DIR__ . '/layout/footer.php'; ?>

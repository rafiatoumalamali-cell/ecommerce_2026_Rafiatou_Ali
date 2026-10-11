<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';

$orderId = filter_var($_GET['order_id'] ?? null, FILTER_VALIDATE_INT);
$sessionOrderId = filter_var($_SESSION['last_paid_order_id'] ?? null, FILTER_VALIDATE_INT);
if ($orderId === false || $orderId === null || $orderId <= 0 || $orderId !== $sessionOrderId) {
    redirect('/e-commerce-labs/shoppn/index.php');
}

$cartController = new CartController();
$order = $cartController->getOrderById($orderId);
if (!$order || $order['order_status'] !== 'paid') {
    redirect('/e-commerce-labs/shoppn/index.php');
}

$emailSent = !empty($_SESSION['last_paid_email_sent']);
include __DIR__ . '/layout/header.php';
?>

<main>
    <section class="order-confirmation-panel">
        <p class="eyebrow">Payment complete</p>
        <h2>Thank you, <?php echo htmlspecialchars($order['shipping_name']); ?>.</h2>
        <p class="order-confirmation-message">
            Paystack verified your payment. Invoice <strong>#<?php echo (int) $order['invoice_no']; ?></strong>
            was created on <?php echo htmlspecialchars($order['created_at']); ?>.
        </p>
        <?php if ($emailSent): ?>
            <p class="order-email-notice order-email-success">A payment confirmation was sent to <?php echo htmlspecialchars($order['shipping_email']); ?>.</p>
        <?php else: ?>
            <p class="order-email-notice">Your payment and order are confirmed, but we could not send the email. Please save or print this invoice.</p>
        <?php endif; ?>

        <dl class="order-shipping-details">
            <div><dt>Status</dt><dd><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $order['order_status']))); ?></dd></div>
            <div><dt>Delivery address</dt><dd><?php echo htmlspecialchars($order['shipping_address']); ?>, <?php echo htmlspecialchars($order['shipping_city']); ?>, <?php echo htmlspecialchars($order['shipping_country']); ?></dd></div>
            <div><dt>Contact</dt><dd><?php echo htmlspecialchars($order['shipping_contact']); ?></dd></div>
            <div><dt>Email</dt><dd><?php echo htmlspecialchars($order['shipping_email']); ?></dd></div>
        </dl>

        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr><th>Product</th><th>Quantity</th><th>Unit price</th><th>Total</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($order['items'] as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['product_title']); ?></td>
                            <td><?php echo (int) $item['qty']; ?></td>
                            <td><?php echo htmlspecialchars(format_ghs_price($item['unit_price'])); ?></td>
                            <td><?php echo htmlspecialchars(format_ghs_price((int) $item['qty'] * (float) $item['unit_price'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot><tr><th colspan="3">Invoice total (<?php echo htmlspecialchars($order['currency']); ?>)</th><th><?php echo htmlspecialchars(format_ghs_price($order['total_amount'])); ?></th></tr></tfoot>
            </table>
        </div>

        <button type="button" class="btn btn-secondary" onclick="window.print()">Print Invoice</button>
        <a class="btn btn-secondary" href="/e-commerce-labs/shoppn/views/download_invoice.php">Download PDF Invoice</a>
        <a class="btn btn-cart" href="/e-commerce-labs/shoppn/index.php">Continue Shopping</a>
    </section>
</main>

<?php include __DIR__ . '/layout/footer.php'; ?>

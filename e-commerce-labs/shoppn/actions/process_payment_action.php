<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';
require_once __DIR__ . '/../classes/OrderEmailClass.php';

$reference = $_GET['ref'] ?? '';
if (!is_string($reference) || !preg_match('/^[A-Za-z0-9._-]{1,100}$/', $reference)) {
    $_SESSION['payment_failure'] = [
        'message' => 'Paystack did not provide a valid transaction reference.',
        'reference' => '',
        'payment_verified' => false,
    ];
    redirect('/e-commerce-labs/shoppn/views/payment_failed.php');
}

if (
    isset($_SESSION['last_paid_reference'], $_SESSION['last_paid_order_id'])
    && hash_equals((string) $_SESSION['last_paid_reference'], $reference)
) {
    redirect('/e-commerce-labs/shoppn/views/payment_success.php?order_id=' . (int) $_SESSION['last_paid_order_id']);
}

$secretKey = app_setting('PAYSTACK_SECRET_KEY');
if (!is_string($secretKey) || !preg_match('/^sk_(test|live)_[A-Za-z0-9]+$/', $secretKey)) {
    error_log('Paystack verification cannot run because PAYSTACK_SECRET_KEY is not configured.');
    $_SESSION['payment_failure'] = [
        'message' => 'Payment verification is unavailable right now. Contact support before attempting another payment.',
        'reference' => $reference,
        'payment_verified' => false,
    ];
    redirect('/e-commerce-labs/shoppn/views/payment_failed.php');
}

$shipping = $_SESSION['checkout_shipping'] ?? null;
$cartSnapshot = $_SESSION['checkout_cart_snapshot'] ?? null;
if (!is_array($shipping) || !is_array($cartSnapshot) || $cartSnapshot === []) {
    $_SESSION['payment_failure'] = [
        'message' => 'Your checkout session is no longer available. Contact support with your payment reference before retrying.',
        'reference' => $reference,
        'payment_verified' => false,
    ];
    redirect('/e-commerce-labs/shoppn/views/payment_failed.php');
}

$ch = curl_init('https://api.paystack.co/transaction/verify/' . rawurlencode($reference));
if ($ch === false) {
    error_log('Unable to initialize cURL for Paystack verification.');
    $_SESSION['payment_failure'] = [
        'message' => 'Payment verification is unavailable right now. Contact support before attempting another payment.',
        'reference' => $reference,
        'payment_verified' => false,
    ];
    redirect('/e-commerce-labs/shoppn/views/payment_failed.php');
}

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $secretKey, 'Accept: application/json'],
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
]);
$responseBody = curl_exec($ch);
$curlError = curl_error($ch);
$httpStatus = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($responseBody === false || $httpStatus !== 200) {
    error_log('Paystack verification request failed. HTTP status: ' . $httpStatus . '; cURL: ' . $curlError);
    $_SESSION['payment_failure'] = [
        'message' => 'Paystack could not verify this payment. Contact support before attempting another payment.',
        'reference' => $reference,
        'payment_verified' => false,
    ];
    redirect('/e-commerce-labs/shoppn/views/payment_failed.php');
}

$verification = json_decode($responseBody, true);
$transaction = is_array($verification) ? ($verification['data'] ?? null) : null;
if (
    !is_array($transaction)
    || ($verification['status'] ?? false) !== true
    || ($transaction['status'] ?? '') !== 'success'
) {
    error_log('Paystack did not confirm a successful transaction for reference ' . $reference . '.');
    $_SESSION['payment_failure'] = [
        'message' => 'Paystack has not confirmed a successful payment. Your cart has not been converted into an order.',
        'reference' => $reference,
        'payment_verified' => false,
    ];
    redirect('/e-commerce-labs/shoppn/views/payment_failed.php');
}

$expectedTotal = 0.0;
foreach ($cartSnapshot as $item) {
    if (!is_array($item) || !isset($item['qty'], $item['unit_price'])) {
        $_SESSION['payment_failure'] = [
            'message' => 'Your checkout details could not be verified. Contact support with your payment reference.',
            'reference' => $reference,
            'payment_verified' => true,
        ];
        redirect('/e-commerce-labs/shoppn/views/payment_failed.php');
    }
    $expectedTotal += (int) $item['qty'] * (float) $item['unit_price'];
}

$expectedAmount = (int) round($expectedTotal * 100);
$verifiedAmount = filter_var($transaction['amount'] ?? null, FILTER_VALIDATE_INT);
$customerData = $transaction['customer'] ?? null;
$verifiedEmail = is_array($customerData) && is_string($customerData['email'] ?? null)
    ? strtolower($customerData['email'])
    : '';
if (
    $verifiedAmount === false
    || $verifiedAmount !== $expectedAmount
    || ($transaction['currency'] ?? '') !== 'GHS'
    || $verifiedEmail !== strtolower((string) ($shipping['email'] ?? ''))
) {
    error_log('Paystack transaction details did not match checkout for reference ' . $reference . '.');
    $_SESSION['payment_failure'] = [
        'message' => 'The verified transaction did not match the checkout amount, currency, or email. Contact support with your payment reference.',
        'reference' => $reference,
        'payment_verified' => true,
    ];
    redirect('/e-commerce-labs/shoppn/views/payment_failed.php');
}

$customerId = is_logged_in() ? (int) $_SESSION['customer_id'] : null;
$cartController = new CartController();
$result = $cartController->processPayment(
    $customerId,
    get_ip(),
    $reference,
    $verifiedAmount,
    $shipping,
    $cartSnapshot,
    $transaction['currency']
);

if (!$result['success']) {
    $_SESSION['payment_failure'] = [
        'message' => $result['error'],
        'reference' => $reference,
        'payment_verified' => true,
    ];
    redirect('/e-commerce-labs/shoppn/views/payment_failed.php');
}

$order = $cartController->getOrderById($result['order_id']);
if (!$order) {
    error_log('Verified Paystack transaction was recorded but invoice could not be loaded. Order ID: ' . (int) $result['order_id']);
    $_SESSION['payment_failure'] = [
        'message' => 'Your payment was verified and an order was created, but we could not display its invoice. Contact support with your payment reference.',
        'reference' => $reference,
        'payment_verified' => true,
    ];
    redirect('/e-commerce-labs/shoppn/views/payment_failed.php');
}

$emailSent = !empty($_SESSION['last_paid_email_sent']);
if (empty($result['already_processed'])) {
    $emailService = new OrderEmailClass();
    $emailSent = $emailService->sendOrderConfirmation($order);
}

$_SESSION['last_paid_order_id'] = (int) $order['order_id'];
$_SESSION['last_paid_reference'] = $reference;
$_SESSION['last_paid_email_sent'] = $emailSent;
unset($_SESSION['checkout_shipping'], $_SESSION['checkout_cart_snapshot']);

redirect('/e-commerce-labs/shoppn/views/payment_success.php?order_id=' . (int) $order['order_id']);

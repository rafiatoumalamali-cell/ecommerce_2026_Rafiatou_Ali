<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';

$fallbackUrl = '/e-commerce-labs/shoppn/index.php';
$returnUrl = $fallbackUrl;
$referer = $_SERVER['HTTP_REFERER'] ?? '';
if (is_string($referer) && $referer !== '') {
    $refererParts = parse_url($referer);
    $requestHost = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
    $refererHost = strtolower((string) ($refererParts['host'] ?? ''));
    $refererPath = $refererParts['path'] ?? '';
    if (
        $refererPath !== ''
        && strpos($refererPath, '/e-commerce-labs/shoppn/') === 0
        && ($refererHost === '' || $refererHost === $requestHost)
    ) {
        $returnUrl = $refererPath
            . (isset($refererParts['query']) ? '?' . $refererParts['query'] : '')
            . (isset($refererParts['fragment']) ? '#' . $refererParts['fragment'] : '');
    }
}

$productId = filter_var($_GET['add_cart'] ?? null, FILTER_VALIDATE_INT);
if ($productId === false || $productId === null || $productId <= 0) {
    $_SESSION['cart_notice'] = ['type' => 'error', 'message' => 'Choose a valid product to add to your cart.'];
    redirect($returnUrl);
}

$controller = new CartController();
if ($controller->isInCart($productId, get_ip())) {
    $_SESSION['cart_notice'] = [
        'type' => 'info',
        'message' => 'This item is already in your cart. Go to cart to update quantity.',
    ];
    redirect($returnUrl);
}

$customerId = isset($_SESSION['customer_id']) ? (int) $_SESSION['customer_id'] : null;
$result = $controller->addToCart($productId, 1, get_ip(), $customerId);
$_SESSION['cart_notice'] = $result['success']
    ? ['type' => 'success', 'message' => $result['message']]
    : ['type' => 'error', 'message' => $result['error']];

redirect($returnUrl);

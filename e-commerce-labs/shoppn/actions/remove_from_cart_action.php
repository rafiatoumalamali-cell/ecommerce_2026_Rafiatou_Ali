<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';

$cartUrl = '/e-commerce-labs/shoppn/views/cart.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect($cartUrl);
}

$submittedToken = $_POST['csrf_token'] ?? '';
if (!is_string($submittedToken) || !hash_equals(csrf_token(), $submittedToken)) {
    $_SESSION['cart_notice'] = ['type' => 'error', 'message' => 'Your session expired. Please try again.'];
    redirect($cartUrl);
}

$removeIds = $_POST['remove'] ?? [];
if (!is_array($removeIds) || $removeIds === []) {
    $_SESSION['cart_notice'] = ['type' => 'error', 'message' => 'Select at least one item to remove.'];
    redirect($cartUrl);
}

$productIds = [];
foreach ($removeIds as $removeId) {
    $productId = filter_var($removeId, FILTER_VALIDATE_INT);
    if ($productId === false || $productId <= 0) {
        $_SESSION['cart_notice'] = ['type' => 'error', 'message' => 'One or more selected cart items are invalid.'];
        redirect($cartUrl);
    }
    $productIds[] = $productId;
}

$controller = new CartController();
$errors = [];
foreach (array_unique($productIds) as $productId) {
    $result = $controller->removeFromCart($productId, get_ip());
    if (!$result['success']) {
        $errors[] = $result['error'];
    }
}

$_SESSION['cart_notice'] = $errors === []
    ? ['type' => 'success', 'message' => 'Selected item(s) removed from your cart.']
    : ['type' => 'error', 'message' => implode(' ', array_unique($errors))];

redirect($cartUrl);

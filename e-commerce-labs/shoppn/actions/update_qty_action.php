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

$updates = $_POST['update'] ?? [];
$quantities = $_POST['qty'] ?? [];
if (!is_array($updates) || !is_array($quantities) || $updates === [] || count($updates) !== count($quantities)) {
    $_SESSION['cart_notice'] = ['type' => 'error', 'message' => 'Cart quantities could not be read. Please try again.'];
    redirect($cartUrl);
}

$validatedUpdates = [];
foreach (array_values($updates) as $index => $updateId) {
    $productId = filter_var($updateId, FILTER_VALIDATE_INT);
    $quantity = filter_var($quantities[$index] ?? null, FILTER_VALIDATE_INT);
    if ($productId === false || $productId <= 0 || $quantity === false || $quantity < 1) {
        $_SESSION['cart_notice'] = ['type' => 'error', 'message' => 'Each cart quantity must be a whole number of at least 1.'];
        redirect($cartUrl);
    }
    $validatedUpdates[$productId] = $quantity;
}

$controller = new CartController();
$errors = [];
foreach ($validatedUpdates as $productId => $quantity) {
    $result = $controller->updateQty($productId, $quantity, get_ip());
    if (!$result['success']) {
        $errors[] = $result['error'];
    }
}

$_SESSION['cart_notice'] = $errors === []
    ? ['type' => 'success', 'message' => 'Cart quantities updated.']
    : ['type' => 'error', 'message' => implode(' ', array_unique($errors))];

redirect($cartUrl);

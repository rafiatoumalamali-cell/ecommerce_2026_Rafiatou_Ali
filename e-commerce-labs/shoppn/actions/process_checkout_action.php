<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';

$checkoutUrl = '/e-commerce-labs/shoppn/views/checkout.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect($checkoutUrl);
}

$submittedToken = $_POST['csrf_token'] ?? '';
if (!is_string($submittedToken) || !hash_equals(csrf_token(), $submittedToken)) {
    $_SESSION['cart_notice'] = ['type' => 'error', 'message' => 'Your session expired. Please try checkout again.'];
    redirect($checkoutUrl);
}

$controller = new CartController();
$shipping = [
    'name' => $_POST['shipping_name'] ?? '',
    'email' => $_POST['shipping_email'] ?? '',
    'contact' => $_POST['shipping_contact'] ?? '',
    'address' => $_POST['shipping_address'] ?? '',
    'city' => $_POST['shipping_city'] ?? '',
    'country' => $_POST['shipping_country'] ?? '',
];
$validation = $controller->validateCheckoutDetails($shipping);
if (!$validation['success']) {
    $_SESSION['cart_notice'] = ['type' => 'error', 'message' => $validation['error']];
    redirect($checkoutUrl);
}

$items = $controller->getCartItems(get_ip());
if ($items === []) {
    $_SESSION['cart_notice'] = ['type' => 'error', 'message' => 'Your cart is empty. Add products before checking out.'];
    redirect('/e-commerce-labs/shoppn/views/cart.php');
}

$cartSnapshot = [];
foreach ($items as $item) {
    if ((int) $item['qty'] > (int) $item['stock_quantity']) {
        $_SESSION['cart_notice'] = [
            'type' => 'error',
            'message' => 'The available stock changed. Update your cart quantities before continuing.',
        ];
        redirect('/e-commerce-labs/shoppn/views/cart.php');
    }
    $cartSnapshot[] = [
        'product_id' => (int) $item['p_id'],
        'qty' => (int) $item['qty'],
        'unit_price' => (float) $item['product_price'],
        'product_title' => $item['product_title'],
    ];
}

$_SESSION['checkout_shipping'] = $validation['details'];
$_SESSION['checkout_cart_snapshot'] = $cartSnapshot;
redirect('/e-commerce-labs/shoppn/views/payment.php');

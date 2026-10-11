<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../core/product_image.php';
require_admin();
require_once __DIR__ . '/../controllers/ProductController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/e-commerce-labs/shoppn/views/admin/product.php');
}

$submittedToken = $_POST['csrf_token'] ?? '';
if (!is_string($submittedToken) || !hash_equals(csrf_token(), $submittedToken)) {
    $_SESSION['error'] = 'Your session expired. Please try again.';
    redirect('/e-commerce-labs/shoppn/views/admin/product.php');
}

$imageResult = store_product_image($_FILES['product_image'] ?? null);
if (!$imageResult['success']) {
    $_SESSION['error'] = $imageResult['error'];
    redirect('/e-commerce-labs/shoppn/views/admin/product.php');
}

$controller = new ProductController();
$result = $controller->addProduct(
    $_POST['product_cat'] ?? 0,
    $_POST['product_brand'] ?? 0,
    $_POST['product_title'] ?? '',
    $_POST['product_price'] ?? '',
    $_POST['product_desc'] ?? '',
    $imageResult['filename'],
    $_POST['product_keywords'] ?? '',
    $_POST['stock_quantity'] ?? null
);

if ($result['success']) {
    $_SESSION['success'] = $result['message'];
} else {
    remove_product_image($imageResult['filename']);
    $_SESSION['error'] = $result['error'];
}

redirect('/e-commerce-labs/shoppn/views/admin/product.php');

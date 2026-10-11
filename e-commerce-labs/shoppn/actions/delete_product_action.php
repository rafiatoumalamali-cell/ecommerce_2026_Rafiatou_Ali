<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../core/product_image.php';
require_admin();
require_once __DIR__ . '/../controllers/ProductController.php';

$adminProductsUrl = '/e-commerce-labs/shoppn/views/admin/product.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect($adminProductsUrl);
}

$submittedToken = $_POST['csrf_token'] ?? '';
if (!is_string($submittedToken) || !hash_equals(csrf_token(), $submittedToken)) {
    $_SESSION['error'] = 'Your session expired. Please try again.';
    redirect($adminProductsUrl);
}

$id = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id <= 0) {
    $_SESSION['error'] = 'Invalid product ID.';
    redirect($adminProductsUrl);
}

$controller = new ProductController();
$result = $controller->deleteProduct($id);

if ($result['success']) {
    remove_product_image($result['image_filename']);
    $_SESSION['success'] = $result['message'];
} else {
    $_SESSION['error'] = $result['error'];
}

redirect($adminProductsUrl);

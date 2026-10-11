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

$id = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id <= 0) {
    $_SESSION['error'] = 'Invalid product ID.';
    redirect('/e-commerce-labs/shoppn/views/admin/product.php');
}

$controller = new ProductController();
$existingProduct = $controller->getProductById($id);
if (!$existingProduct) {
    $_SESSION['error'] = 'Product not found.';
    redirect('/e-commerce-labs/shoppn/views/admin/product.php');
}

$imageFilename = $existingProduct['product_image'];
$newImageFilename = null;
$upload = $_FILES['product_image'] ?? null;
if (is_array($upload) && ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
    $imageResult = store_product_image($upload);
    if (!$imageResult['success']) {
        $_SESSION['error'] = $imageResult['error'];
        redirect('/e-commerce-labs/shoppn/views/admin/product.php?edit_id=' . $id);
    }
    $imageFilename = $imageResult['filename'];
    $newImageFilename = $imageFilename;
}

$result = $controller->updateProduct(
    $id,
    $_POST['product_cat'] ?? 0,
    $_POST['product_brand'] ?? 0,
    $_POST['product_title'] ?? '',
    $_POST['product_price'] ?? '',
    $_POST['product_desc'] ?? '',
    $imageFilename,
    $_POST['product_keywords'] ?? '',
    $_POST['stock_quantity'] ?? null
);

if ($result['success']) {
    if ($newImageFilename !== null && $existingProduct['product_image'] !== $newImageFilename) {
        remove_product_image($existingProduct['product_image']);
    }
    $_SESSION['success'] = $result['message'];
} else {
    if ($newImageFilename !== null) {
        remove_product_image($newImageFilename);
    }
    $_SESSION['error'] = $result['error'];
}

redirect('/e-commerce-labs/shoppn/views/admin/product.php' . ($result['success'] ? '' : '?edit_id=' . $id));

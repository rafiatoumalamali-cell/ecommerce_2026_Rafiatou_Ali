<?php
require_once __DIR__ . '/../core/core.php';
require_admin();
require_once __DIR__ . '/../controllers/ProductController.php';

$inventoryUrl = '/e-commerce-labs/shoppn/views/admin/inventory.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect($inventoryUrl);
}

$submittedToken = $_POST['csrf_token'] ?? '';
if (!is_string($submittedToken) || !hash_equals(csrf_token(), $submittedToken)) {
    $_SESSION['error'] = 'Your session expired. Please try again.';
    redirect($inventoryUrl);
}

$controller = new ProductController();
$result = $controller->updateStockQuantity(
    $_POST['product_id'] ?? null,
    $_POST['stock_quantity'] ?? null
);

if ($result['success']) {
    $_SESSION['success'] = $result['message'];
} else {
    $_SESSION['error'] = $result['error'];
}

redirect($inventoryUrl);

<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    redirect('/e-commerce-labs/shoppn/views/admin/brand.php');
}

$id = $_GET['brand_id'] ?? $_GET['id'] ?? 0;

if (!is_numeric($id) || (int) $id <= 0) {
    $_SESSION['error'] = 'Invalid brand ID.';
    redirect('/e-commerce-labs/shoppn/views/admin/brand.php');
}

$controller = new ProductController();
$result = $controller->deleteBrand((int) $id);

if ($result['success'] === true) {
    $_SESSION['success'] = $result['message'];
    redirect('/e-commerce-labs/shoppn/views/admin/brand.php');
}

$_SESSION['error'] = $result['error'];
redirect('/e-commerce-labs/shoppn/views/admin/brand.php');

<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    redirect('/e-commerce-labs/shoppn/views/admin/category.php');
}

$id = $_GET['cat_id'] ?? $_GET['category_id'] ?? $_GET['id'] ?? 0;

if (!is_numeric($id) || (int) $id <= 0) {
    $_SESSION['error'] = 'Invalid category ID.';
    redirect('/e-commerce-labs/shoppn/views/admin/category.php');
}

$controller = new ProductController();
$result = $controller->deleteCategory((int) $id);

if ($result['success'] === true) {
    $_SESSION['success'] = $result['message'];
    redirect('/e-commerce-labs/shoppn/views/admin/category.php');
}

$_SESSION['error'] = $result['error'];
redirect('/e-commerce-labs/shoppn/views/admin/category.php');

<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/e-commerce-labs/shoppn/views/admin/category.php');
}

$id = $_POST['cat_id'] ?? $_POST['category_id'] ?? 0;
$category_name = trim(strip_tags($_POST['cat_name'] ?? $_POST['category_name'] ?? ''));

if (!is_numeric($id) || (int) $id <= 0) {
    $_SESSION['error'] = 'Invalid category ID.';
    redirect('/e-commerce-labs/shoppn/views/admin/category.php');
}

$controller = new ProductController();
$result = $controller->updateCategory((int) $id, $category_name);

if ($result['success'] === true) {
    $_SESSION['success'] = $result['message'];
    redirect('/e-commerce-labs/shoppn/views/admin/category.php');
}

$_SESSION['error'] = $result['error'];
redirect('/e-commerce-labs/shoppn/views/admin/category.php');

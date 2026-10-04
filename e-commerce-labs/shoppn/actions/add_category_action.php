<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/e-commerce-labs/shoppn/views/admin/category.php');
}

$category_name = trim(strip_tags($_POST['cat_name'] ?? $_POST['category_name'] ?? ''));
$controller = new ProductController();
$result = $controller->addCategory($category_name);

if ($result['success'] === true) {
    $_SESSION['success'] = $result['message'];
    redirect('/e-commerce-labs/shoppn/views/admin/category.php');
}

$_SESSION['error'] = $result['error'];
redirect('/e-commerce-labs/shoppn/views/admin/category.php');

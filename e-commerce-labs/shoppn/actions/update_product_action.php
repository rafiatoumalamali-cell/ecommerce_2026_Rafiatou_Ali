<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/e-commerce-labs/shoppn/index.php');
}

$controller = new ProductController();
$controller->updateProduct($_POST['id'] ?? 0, $_POST);
redirect('/e-commerce-labs/shoppn/index.php');

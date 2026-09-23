<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$controller = new ProductController();
$controller->updateCategory($_POST['id'] ?? 0, $_POST);
redirect('index.php');

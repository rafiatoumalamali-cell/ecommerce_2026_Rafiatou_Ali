<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/e-commerce-labs/shoppn/index.php');
}

$productId = $_POST['product_id'] ?? 0;

$controller = new CartController();
$controller->removeFromCart($productId);
redirect('/e-commerce-labs/shoppn/index.php');

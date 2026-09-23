<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$productId = $_POST['product_id'] ?? 0;
$quantity = $_POST['quantity'] ?? 1;

$controller = new CartController();
$controller->addToCart($productId, $quantity);
redirect('index.php');

<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CustomerController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/~rafiatou.ali/e-commerce-labs/shoppn/index.php');
}

$controller = new CustomerController();
$result = $controller->register($_POST);

if ($result['status'] === 'success') {
    $_SESSION['success'] = $result['message'];
    redirect('/~rafiatou.ali/e-commerce-labs/shoppn/views/login.php');
}

$_SESSION['error'] = $result['message'];
redirect('/~rafiatou.ali/e-commerce-labs/shoppn/views/register.php');
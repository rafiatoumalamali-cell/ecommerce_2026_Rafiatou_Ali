<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CustomerController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/~rafiatou.ali/e-commerce-labs/shoppn/index.php');
}

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$controller = new CustomerController();
$result = $controller->login($email, $password);

if ($result['status'] === 'success') {
    $_SESSION['success'] = $result['message'];

    // Redirect based on user role
    if ($result['user_role'] == 1) {
        redirect('/~rafiatou.ali/e-commerce-labs/shoppn/views/admin/admin_panel.php');
    } else {
        redirect('/~rafiatou.ali/e-commerce-labs/shoppn/index.php');
    }
}

$_SESSION['error'] = $result['message'];
redirect('/~rafiatou.ali/e-commerce-labs/shoppn/views/login.php');
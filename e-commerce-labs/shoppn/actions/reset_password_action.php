<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CustomerController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/e-commerce-labs/shoppn/index.php');
}

$email = $_POST['email'] ?? '';
$secret_keyword = $_POST['secret_keyword'] ?? '';
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

$controller = new CustomerController();
$result = $controller->resetPassword($email, $secret_keyword, $password, $confirm_password);

if ($result['status'] === 'success') {
    unset($_SESSION['reset_email']);
    $_SESSION['success'] = $result['message'];
    redirect('/e-commerce-labs/shoppn/views/login.php');
}

$_SESSION['error'] = $result['message'];
redirect('/e-commerce-labs/shoppn/views/reset_password.php');

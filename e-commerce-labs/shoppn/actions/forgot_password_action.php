<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CustomerController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/e-commerce-labs/shoppn/index.php');
}

$email = $_POST['email'] ?? '';

$controller = new CustomerController();
$result = $controller->initiatePasswordReset($email);

if ($result['status'] === 'success') {
    $_SESSION['reset_email'] = $email;
    redirect('/e-commerce-labs/shoppn/views/reset_password.php');
}

$_SESSION['error'] = $result['message'];
redirect('/e-commerce-labs/shoppn/views/forgot_password.php');

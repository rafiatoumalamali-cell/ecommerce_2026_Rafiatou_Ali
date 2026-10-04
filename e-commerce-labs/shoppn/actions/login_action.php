<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CustomerController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/e-commerce-labs/shoppn/index.php');
}

$email = trim(strip_tags($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';
$remember_me = isset($_POST['remember_me']) ? $_POST['remember_me'] : '';

$controller = new CustomerController();
$result = $controller->login($email, $password);

if ($result['success'] === true) {
    $customer = $result['customer'];
    $_SESSION['customer_id'] = $customer['customer_id'];
    $_SESSION['customer_name'] = $customer['customer_name'];
    $_SESSION['customer_email'] = $customer['customer_email'];
    $_SESSION['user_role'] = $customer['user_role'];
    $_SESSION['success'] = 'Login successful';

    if ($remember_me == '1') {
        $token_result = $controller->setRememberMeToken($customer['customer_id']);
        if ($token_result['status'] === 'success') {
            setcookie('remember_me_selector', $token_result['selector'], time() + (30 * 24 * 60 * 60), '/e-commerce-labs/shoppn/');
            setcookie('remember_me_token', $token_result['token'], time() + (30 * 24 * 60 * 60), '/e-commerce-labs/shoppn/');
            setcookie('remember_me_email', $email, time() + (30 * 24 * 60 * 60), '/e-commerce-labs/shoppn/');
        }
    } else {
        setcookie('remember_me_email', '', time() - 3600, '/e-commerce-labs/shoppn/');
    }

    if ($customer['user_role'] == 1) {
        redirect('/e-commerce-labs/shoppn/admin_panel.php');
    }

    redirect('/e-commerce-labs/shoppn/index.php');
}

$_SESSION['error'] = $result['error'];
redirect('/e-commerce-labs/shoppn/views/login.php');
<?php
require_once __DIR__ . '/core/core.php';

// Clear remember me cookies
setcookie('remember_me_selector', '', time() - 3600, '/e-commerce-labs/shoppn/');
setcookie('remember_me_token', '', time() - 3600, '/e-commerce-labs/shoppn/');
setcookie('remember_me_email', '', time() - 3600, '/e-commerce-labs/shoppn/');

// Delete remember me token from database if user is logged in
if (isset($_SESSION['customer_id'])) {
    require_once __DIR__ . '/controllers/CustomerController.php';
    $controller = new CustomerController();
    $controller->deleteRememberMeToken($_SESSION['customer_id']);
}

session_destroy();
redirect('/e-commerce-labs/shoppn/index.php');
?>
<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CustomerController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$controller = new CustomerController();
$result = $controller->login($email, $password);

if ($result['status'] === 'success') {
    redirect('index.php');
}

redirect('index.php?error=login_failed');

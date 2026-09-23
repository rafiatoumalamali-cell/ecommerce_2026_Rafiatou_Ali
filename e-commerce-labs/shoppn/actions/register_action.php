<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CustomerController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$controller = new CustomerController();
$result = $controller->register($_POST);

if ($result['status'] === 'success') {
    redirect('index.php');
}

redirect('index.php?error=registration_failed');

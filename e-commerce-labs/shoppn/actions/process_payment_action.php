<?php
require_once __DIR__ . '/../core/core.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

// Payment processing placeholder.
redirect('payment_success.php');

<?php
require_once __DIR__ . '/../core/core.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/e-commerce-labs/shoppn/index.php');
}

// Payment processing placeholder.
redirect('/e-commerce-labs/shoppn/views/payment_success.php');

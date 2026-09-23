<?php
// Start the session
session_start();

// Set default timezone
date_default_timezone_set('Africa/Accra');

// Error logging setup
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../error/error.log');
ini_set('display_errors', 0); // turn off in production; set to 1 while debugging if needed

// Require the database base class
require_once 'db_class.php';

// ── Helper Functions ─────────────────────────────

function get_ip() {
    return $_SERVER['REMOTE_ADDR'];
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function is_logged_in() {
    return isset($_SESSION['customer_id']);
}

function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1;
}

function require_login() {
    if (!is_logged_in()) {
        redirect('views/login.php');
    }
}

function require_admin() {
    if (!is_admin()) {
        $_SESSION['error'] = 'Access denied. Admins only.';
        redirect('index.php');
    }
}
?>
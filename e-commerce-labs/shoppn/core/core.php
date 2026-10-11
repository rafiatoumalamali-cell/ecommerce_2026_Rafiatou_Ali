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

// Check for remember me cookies if user is not logged in
if (!isset($_SESSION['customer_id']) && isset($_COOKIE['remember_me_selector']) && isset($_COOKIE['remember_me_token'])) {
    require_once __DIR__ . '/../controllers/CustomerController.php';
    $controller = new CustomerController();
    $result = $controller->validateRememberMeToken($_COOKIE['remember_me_selector'], $_COOKIE['remember_me_token']);

    if ($result['status'] === 'success') {
        // User is now logged in via remember me
        // Optionally refresh the token for security
        $token_result = $controller->setRememberMeToken($_SESSION['customer_id']);
        if ($token_result['status'] === 'success') {
            setcookie('remember_me_selector', $token_result['selector'], time() + (30 * 24 * 60 * 60), '/e-commerce-labs/shoppn/');
            setcookie('remember_me_token', $token_result['token'], time() + (30 * 24 * 60 * 60), '/e-commerce-labs/shoppn/');
        }
    } else {
        // Invalid token, clear cookies
        setcookie('remember_me_selector', '', time() - 3600, '/e-commerce-labs/shoppn/');
        setcookie('remember_me_token', '', time() - 3600, '/e-commerce-labs/shoppn/');
    }
}

// ── Helper Functions ─────────────────────────────

function get_ip() {
    return $_SERVER['REMOTE_ADDR'];
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function is_logged_in() {
    if (!isset($_SESSION['customer_id'])) {
        return false;
    }

    static $checkedCustomerId = null;
    static $active = false;
    $customerId = (int) $_SESSION['customer_id'];

    if ($checkedCustomerId === $customerId) {
        return $active;
    }

    $checkedCustomerId = $customerId;
    require_once __DIR__ . '/../controllers/CustomerController.php';
    $controller = new CustomerController();
    $access = $controller->getCustomerAccessStatus($customerId);

    if (!$access || (int) $access['is_active'] !== 1) {
        unset($_SESSION['customer_id'], $_SESSION['customer_name'], $_SESSION['customer_email'], $_SESSION['user_role']);
        $_SESSION['error'] = 'Your account is deactivated or no longer available.';
        return false;
    }

    $_SESSION['customer_name'] = $access['customer_name'];
    $_SESSION['customer_email'] = $access['customer_email'];
    $_SESSION['user_role'] = (int) $access['user_role'];
    $active = true;
    return true;
}

function is_admin() {
    return is_logged_in() && isset($_SESSION['user_role']) && (int) $_SESSION['user_role'] === 1;
}

function require_login() {
    if (!is_logged_in()) {
        redirect('/e-commerce-labs/shoppn/views/login.php');
    }
}

function require_admin() {
    if (!is_admin()) {
        $_SESSION['error'] = 'Access denied. Admins only.';
        redirect('/e-commerce-labs/shoppn/index.php');
    }
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function format_ghs_price($price) {
    $amount = (float) $price;
    $decimals = floor($amount) === $amount ? 0 : 2;
    return number_format($amount, $decimals, '.', ',') . ' GHS';
}

function app_setting($key) {
    static $settings = null;

    $environmentValue = getenv($key);
    if ($environmentValue !== false && $environmentValue !== '') {
        return $environmentValue;
    }

    if ($settings === null) {
        $settingsFile = __DIR__ . '/../.env';
        $settings = file_exists($settingsFile) ? parse_ini_file($settingsFile, false, INI_SCANNER_RAW) : [];
        if ($settings === false) {
            error_log('Unable to parse application .env settings.');
            $settings = [];
        }
    }

    $value = $settings[$key] ?? null;
    return is_string($value) ? trim($value, " \t\n\r\0\x0B\"'") : null;
}
?>
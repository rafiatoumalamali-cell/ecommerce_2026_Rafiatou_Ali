<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CustomerController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/e-commerce-labs/shoppn/index.php');
}

$name = trim(strip_tags($_POST['name'] ?? ''));
$email = trim(strip_tags($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$country = trim(strip_tags($_POST['country'] ?? ''));
$city = trim(strip_tags($_POST['city'] ?? ''));
$contact = trim(strip_tags($_POST['contact'] ?? ''));
$address = trim(strip_tags($_POST['address'] ?? ''));
$secret_keyword = $_POST['secret_keyword'] ?? null;
$confirm_secret_keyword = $_POST['confirm_secret_keyword'] ?? null;
$profile_image = null;

if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
    $_SESSION['error'] = 'Password must be at least 8 characters with uppercase, lowercase, number, and special character (@$!%*?&).';
    redirect('/e-commerce-labs/shoppn/views/register.php');
}

if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['profile_image'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
    $max_size = 5 * 1024 * 1024;

    if (!in_array($file['type'], $allowed_types)) {
        $_SESSION['error'] = 'Invalid file type. Only JPEG, PNG, and GIF are allowed.';
        redirect('/e-commerce-labs/shoppn/views/register.php');
    }

    if ($file['size'] > $max_size) {
        $_SESSION['error'] = 'File size exceeds 5MB limit.';
        redirect('/e-commerce-labs/shoppn/views/register.php');
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('profile_', true) . '.' . $extension;
    $upload_path = __DIR__ . '/../uploads/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        $_SESSION['error'] = 'Failed to upload profile image.';
        redirect('/e-commerce-labs/shoppn/views/register.php');
    }

    $profile_image = $filename;
}

if (strlen($email) > 100 || strlen($name) > 100 || strlen($country) > 30 || strlen($city) > 30 || strlen($contact) > 15) {
    $_SESSION['error'] = 'One or more fields are too long.';
    redirect('/e-commerce-labs/shoppn/views/register.php');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Invalid email format';
    redirect('/e-commerce-labs/shoppn/views/register.php');
}

$controller = new CustomerController();
$result = $controller->register([
    'name' => $name,
    'email' => $email,
    'password' => $password,
    'confirm_password' => $confirm_password,
    'country' => $country,
    'city' => $city,
    'contact' => $contact,
    'address' => $address,
    'secret_keyword' => $secret_keyword,
    'confirm_secret_keyword' => $confirm_secret_keyword,
    'profile_image' => $profile_image,
]);

if ($result['success'] === true) {
    $_SESSION['customer_id'] = $result['customer_id'];
    $_SESSION['customer_name'] = $result['customer_name'];
    $_SESSION['user_role'] = $result['user_role'];
    $_SESSION['success'] = $result['message'];
    redirect('/e-commerce-labs/shoppn/views/account/my_account.php');
}

$_SESSION['error'] = $result['error'];
redirect('/e-commerce-labs/shoppn/views/register.php');
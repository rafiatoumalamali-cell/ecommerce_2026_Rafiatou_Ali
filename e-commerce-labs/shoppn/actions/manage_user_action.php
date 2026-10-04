<?php
require_once __DIR__ . '/../core/core.php';
require_admin();
require_once __DIR__ . '/../controllers/CustomerController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/e-commerce-labs/shoppn/views/admin/users.php');
}

$submittedToken = $_POST['csrf_token'] ?? '';
if (!is_string($submittedToken) || !hash_equals(csrf_token(), $submittedToken)) {
    $_SESSION['error'] = 'Your session expired. Please try again.';
    redirect('/e-commerce-labs/shoppn/views/admin/users.php');
}

$returnFilters = [];
parse_str((string) ($_POST['return_query'] ?? ''), $returnFilters);
$returnQuery = http_build_query([
    'search' => trim((string) ($returnFilters['search'] ?? '')),
    'role' => in_array((string) ($returnFilters['role'] ?? '0'), ['0', '1', '2'], true) ? (string) $returnFilters['role'] : '0',
    'status' => in_array((string) ($returnFilters['status'] ?? '-1'), ['-1', '0', '1'], true) ? (string) $returnFilters['status'] : '-1',
]);
$returnUrl = '/e-commerce-labs/shoppn/views/admin/users.php?' . $returnQuery;

$id = filter_var($_POST['customer_id'] ?? null, FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id <= 0) {
    $_SESSION['error'] = 'Invalid user ID.';
    redirect($returnUrl);
}

$controller = new CustomerController();
$action = $_POST['action'] ?? '';

if ($action === 'role') {
    $role = filter_var($_POST['role'] ?? null, FILTER_VALIDATE_INT);
    $result = $controller->adminUpdateCustomerRole($id, $role === false ? 0 : $role);
} elseif ($action === 'status') {
    $active = filter_var($_POST['active'] ?? null, FILTER_VALIDATE_INT);
    $result = $controller->adminSetCustomerActive($id, $active === false ? -1 : $active);
} elseif ($action === 'delete') {
    $result = $controller->adminDeleteCustomer($id);
} else {
    $_SESSION['error'] = 'Unknown user management action.';
    redirect($returnUrl);
}

if ($result['success'] === true) {
    $_SESSION['success'] = $result['message'];
} else {
    $_SESSION['error'] = $result['error'];
}

redirect($returnUrl);

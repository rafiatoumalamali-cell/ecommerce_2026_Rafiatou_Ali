<?php
require_once __DIR__ . '/../../core/core.php';
require_login();
require_once __DIR__ . '/../../controllers/CustomerController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new CustomerController();
    $result = $controller->deleteAccount($_SESSION['customer_id']);

    if ($result['status'] === 'success') {
        setcookie('remember_me_selector', '', time() - 3600, '/e-commerce-labs/shoppn/');
        setcookie('remember_me_token', '', time() - 3600, '/e-commerce-labs/shoppn/');
        setcookie('remember_me_email', '', time() - 3600, '/e-commerce-labs/shoppn/');

        session_unset();
        session_destroy();

        $_SESSION = [];
        $_SESSION['success'] = $result['message'];
        redirect('/e-commerce-labs/shoppn/views/login.php');
    }

    $_SESSION['error'] = $result['message'];
    redirect('/e-commerce-labs/shoppn/views/account/delete_account.php');
}

include __DIR__ . '/../layout/header.php';
?>

<main>
    <h2>Delete Account</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <p class="flash-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
    <?php endif; ?>

    <div class="account-container">
        <p>Warning: this action will permanently remove your account and all related saved information.</p>

        <form action="delete_account.php" method="post" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
            <button type="submit" class="btn btn-danger">Delete My Account</button>
            <a href="my_account.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</main>

<?php include __DIR__ . '/../layout/footer.php'; ?>

<?php
require_once __DIR__ . '/../../core/core.php';
require_login();
require_once __DIR__ . '/../../controllers/CustomerController.php';

$controller = new CustomerController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $controller->changePassword(
        $_SESSION['customer_id'],
        $_POST['current_password'] ?? '',
        $_POST['new_password'] ?? '',
        $_POST['confirm_password'] ?? ''
    );

    if ($result['status'] === 'success') {
        $_SESSION['success'] = $result['message'];
        redirect('/e-commerce-labs/shoppn/views/account/my_account.php');
    }

    $_SESSION['error'] = $result['message'];
    redirect('/e-commerce-labs/shoppn/views/account/change_pass.php');
}

include __DIR__ . '/../layout/header.php';
?>

<main>
    <h2>Change Password</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <p class="flash-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <p class="flash-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
    <?php endif; ?>

    <form class="auth-form" action="change_pass.php" method="post">
        <div class="form-group">
            <label for="current_password">Current Password</label>
            <input type="password" id="current_password" name="current_password" required>
        </div>

        <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password" required>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>

        <div class="password-requirements">
            <small>Password must be at least 8 characters with uppercase, lowercase, number, and special character (@$!%*?&)</small>
        </div>

        <button type="submit">Update Password</button>
        <p><a href="my_account.php">Back to My Account</a></p>
    </form>
</main>

<?php include __DIR__ . '/../layout/footer.php'; ?>

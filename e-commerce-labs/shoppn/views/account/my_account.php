<?php include __DIR__ . '/../layout/header.php'; ?>

<?php
require_once __DIR__ . '/../../controllers/CustomerController.php';

$controller = new CustomerController();
$result = $controller->getCustomerProfile($_SESSION['customer_id'] ?? 0);

if ($result['status'] !== 'success') {
    echo '<p class="flash-error">' . htmlspecialchars($result['message']) . '</p>';
    include __DIR__ . '/../layout/footer.php';
    exit;
}

$customer = $result['customer'];
?>

<main>
    <h2>My Account</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <p class="flash-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <p class="flash-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
    <?php endif; ?>

    <div class="account-container">
        <div class="profile-section">
            <div class="profile-image">
                <?php if (!empty($customer['customer_image'])): ?>
                    <img src="/e-commerce-labs/shoppn/uploads/<?php echo htmlspecialchars($customer['customer_image']); ?>" alt="Profile Image">
                <?php else: ?>
                    <div class="default-avatar">
                        <span><?php echo strtoupper(substr($customer['customer_name'], 0, 1)); ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="profile-info">
                <h3><?php echo htmlspecialchars($customer['customer_name']); ?></h3>
                <p class="email"><?php echo htmlspecialchars($customer['customer_email']); ?></p>
                <p class="role">
                    <?php echo $customer['user_role'] == 1 ? 'Administrator' : 'Customer'; ?>
                </p>
            </div>
        </div>

        <div class="account-details">
            <h3>Account Details</h3>
            <div class="detail-item">
                <label>Full Name:</label>
                <span><?php echo htmlspecialchars($customer['customer_name']); ?></span>
            </div>
            <div class="detail-item">
                <label>Email:</label>
                <span><?php echo htmlspecialchars($customer['customer_email']); ?></span>
            </div>
            <div class="detail-item">
                <label>Country:</label>
                <span><?php echo htmlspecialchars($customer['customer_country']); ?></span>
            </div>
            <div class="detail-item">
                <label>City:</label>
                <span><?php echo htmlspecialchars($customer['customer_city']); ?></span>
            </div>
            <div class="detail-item">
                <label>Contact:</label>
                <span><?php echo htmlspecialchars($customer['customer_contact']); ?></span>
            </div>
        </div>

        <div class="account-actions">
            <a href="edit_account.php" class="btn btn-primary">Edit Profile</a>
            <a href="change_pass.php" class="btn btn-secondary">Change Password</a>
            <a href="delete_account.php" class="btn btn-danger">Delete Account</a>
            <?php if ($customer['user_role'] == 1): ?>
                <a href="/e-commerce-labs/shoppn/admin_panel.php" class="btn btn-admin">Admin Panel</a>
            <?php endif; ?>
            <a href="/e-commerce-labs/shoppn/views/cart.php" class="btn btn-cart">View Cart</a>
            <a href="/e-commerce-labs/shoppn/logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../layout/footer.php'; ?>

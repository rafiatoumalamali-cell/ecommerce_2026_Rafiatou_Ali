<?php
require_once __DIR__ . '/../../core/core.php';
require_login();
require_once __DIR__ . '/../../controllers/CustomerController.php';

$controller = new CustomerController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $profile_image = null;

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_image'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
        $max_size = 5 * 1024 * 1024;

        if (!in_array($file['type'], $allowed_types)) {
            $_SESSION['error'] = 'Invalid file type. Only JPEG, PNG, and GIF are allowed.';
            redirect('/e-commerce-labs/shoppn/views/account/edit_account.php');
        }

        if ($file['size'] > $max_size) {
            $_SESSION['error'] = 'File size exceeds 5MB limit.';
            redirect('/e-commerce-labs/shoppn/views/account/edit_account.php');
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('profile_', true) . '.' . $extension;
        $upload_path = __DIR__ . '/../../uploads/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
            $_SESSION['error'] = 'Failed to upload profile image.';
            redirect('/e-commerce-labs/shoppn/views/account/edit_account.php');
        }

        $profile_image = $filename;
    }

    $_POST['profile_image'] = $profile_image;
    $result = $controller->editProfile($_SESSION['customer_id'], $_POST);

    if ($result['status'] === 'success') {
        $_SESSION['success'] = $result['message'];
        redirect('/e-commerce-labs/shoppn/views/account/my_account.php');
    }

    $_SESSION['error'] = $result['message'];
    redirect('/e-commerce-labs/shoppn/views/account/edit_account.php');
}

$result = $controller->getCustomerProfile($_SESSION['customer_id']);
if ($result['status'] !== 'success') {
    $_SESSION['error'] = $result['message'];
    redirect('/e-commerce-labs/shoppn/views/account/my_account.php');
}

$customer = $result['customer'];
include __DIR__ . '/../layout/header.php';
?>

<main>
    <h2>Edit Profile</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <p class="flash-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
    <?php endif; ?>

    <form class="auth-form" action="edit_account.php" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($customer['customer_name']); ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($customer['customer_email']); ?>" required>
        </div>

        <div class="form-group">
            <label for="country">Country</label>
            <input type="text" id="country" name="country" value="<?php echo htmlspecialchars($customer['customer_country']); ?>" required>
        </div>

        <div class="form-group">
            <label for="city">City</label>
            <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($customer['customer_city']); ?>" required>
        </div>

        <div class="form-group">
            <label for="contact">Contact Number</label>
            <input type="text" id="contact" name="contact" value="<?php echo htmlspecialchars($customer['customer_contact']); ?>" required>
        </div>

        <div class="form-group">
            <label for="profile_image">Profile Image (optional)</label>
            <input type="file" id="profile_image" name="profile_image" accept="image/*">
            <small>Leave empty to keep the current image.</small>
        </div>

        <button type="submit">Save Changes</button>
        <p><a href="my_account.php">Back to My Account</a></p>
    </form>
</main>

<?php include __DIR__ . '/../layout/footer.php'; ?>

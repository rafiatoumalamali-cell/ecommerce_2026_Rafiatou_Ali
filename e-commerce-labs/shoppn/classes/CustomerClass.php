<?php
require_once __DIR__ . '/../core/db_class.php';

class CustomerClass extends DbClass
{
    private function validatePassword($password = '')
    {
        // Password requirements:
        // - Minimum 8 characters
        // - At least one uppercase letter
        // - At least one lowercase letter
        // - At least one number
        // - At least one special character
        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

        if (!preg_match($pattern, $password)) {
            return [
                'valid' => false,
                'message' => 'Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&)'
            ];
        }

        return ['valid' => true];
    }

    private function sanitizeInput($input = '')
    {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    public function emailExists($email = '')
    {
        $email = trim(strip_tags($email));

        if ($email === '') {
            return false;
        }

        $stmt = $this->conn->prepare("SELECT customer_email FROM customer WHERE customer_email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();

        return $exists;
    }

    public function addCustomer($name = '', $email = '', $pass = '', $country = '', $city = '', $contact = '', $secretKeyword = null, $profileImage = null)
    {
        $name = trim(strip_tags($name));
        $email = trim(strip_tags($email));
        $country = trim(strip_tags($country));
        $city = trim(strip_tags($city));
        $contact = trim(strip_tags($contact));

        $hashed_password = password_hash($pass, PASSWORD_BCRYPT);
        $hashed_secret_keyword = $secretKeyword !== null && $secretKeyword !== '' ? password_hash((string) $secretKeyword, PASSWORD_BCRYPT) : null;

        $stmt = $this->conn->prepare("INSERT INTO customer (customer_name, customer_email, customer_pass, customer_country, customer_city, customer_contact, user_role, secret_keyword, customer_image) VALUES (?, ?, ?, ?, ?, ?, 2, ?, ?)");
        $stmt->bind_param("ssssssss", $name, $email, $hashed_password, $country, $city, $contact, $hashed_secret_keyword, $profileImage);

        if ($stmt->execute()) {
            $customer_id = $this->conn->insert_id;
            $stmt->close();
            return ['success' => true, 'customer_id' => $customer_id];
        }

        $stmt->close();
        return ['success' => false, 'error' => 'Registration failed: ' . $this->conn->error];
    }

    public function register($data = [])
    {
        $name = trim(strip_tags($data['name'] ?? ''));
        $email = trim(strip_tags($data['email'] ?? ''));
        $password = $data['password'] ?? '';
        $confirm_password = $data['confirm_password'] ?? '';
        $country = trim(strip_tags($data['country'] ?? ''));
        $city = trim(strip_tags($data['city'] ?? ''));
        $contact = trim(strip_tags($data['contact'] ?? ''));
        $secret_keyword = $data['secret_keyword'] ?? null;
        $confirm_secret_keyword = $data['confirm_secret_keyword'] ?? null;
        $profile_image = $data['profile_image'] ?? null;

        $required = ['name', 'email', 'password', 'confirm_password', 'country', 'city', 'contact'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
                return ['success' => false, 'error' => "Field '$field' is required"];
            }
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Invalid email format'];
        }

        if (strlen($email) > 100) {
            return ['success' => false, 'error' => 'Email is too long'];
        }

        if (strlen($name) > 100) {
            return ['success' => false, 'error' => 'Name is too long'];
        }

        if (strlen($country) > 30 || strlen($city) > 30 || strlen($contact) > 15) {
            return ['success' => false, 'error' => 'One or more field values are too long'];
        }

        $passwordValidation = $this->validatePassword($password);
        if (!$passwordValidation['valid']) {
            return ['success' => false, 'error' => $passwordValidation['message']];
        }

        if ($password !== $confirm_password) {
            return ['success' => false, 'error' => 'Passwords do not match'];
        }

        if ($secret_keyword !== null && $confirm_secret_keyword !== null && $secret_keyword !== $confirm_secret_keyword) {
            return ['success' => false, 'error' => 'Secret keywords do not match'];
        }

        if ($this->emailExists($email)) {
            return ['success' => false, 'error' => 'Email already registered'];
        }

        $result = $this->addCustomer($name, $email, $password, $country, $city, $contact, $secret_keyword, $profile_image);
        if ($result['success'] === false) {
            return $result;
        }

        return [
            'success' => true,
            'customer_id' => $result['customer_id'],
            'customer_name' => $name,
            'user_role' => 2,
            'message' => 'Registration successful'
        ];
    }

    public function getCustomerByEmail($email = '')
    {
        $email = trim(strip_tags($email));

        if ($email === '') {
            return false;
        }

        $stmt = $this->conn->prepare("SELECT * FROM customer WHERE customer_email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            return false;
        }

        $customer = $result->fetch_assoc();
        $stmt->close();

        return $customer;
    }

    public function login($email = '', $password = '')
    {
        if (empty($email) || empty($password)) {
            return false;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $user = $this->getCustomerByEmail($email);
        if ($user === false) {
            return false;
        }

        if (!password_verify($password, $user['customer_pass'])) {
            return false;
        }

        if ((int) $user['is_active'] !== 1) {
            return ['account_deactivated' => true];
        }

        return $user;
    }

    public function setRememberMeToken($customer_id = 0)
    {
        // Generate a random token
        $token = bin2hex(random_bytes(32));
        $selector = bin2hex(random_bytes(16));
        $hashed_token = hash('sha256', $token);
        $expires = date('Y-m-d H:i:s', strtotime('+30 days'));

        // Delete any existing tokens for this user
        $delete_stmt = $this->conn->prepare("DELETE FROM remember_me_tokens WHERE customer_id = ?");
        $delete_stmt->bind_param("i", $customer_id);
        $delete_stmt->execute();
        $delete_stmt->close();

        // Insert new token
        $stmt = $this->conn->prepare("INSERT INTO remember_me_tokens (customer_id, selector, token, expires_at) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $customer_id, $selector, $hashed_token, $expires);

        if ($stmt->execute()) {
            $stmt->close();
            return ['status' => 'success', 'selector' => $selector, 'token' => $token];
        } else {
            $stmt->close();
            return ['status' => 'error', 'message' => 'Failed to set remember me token'];
        }
    }

    public function validateRememberMeToken($selector = '', $token = '')
    {
        if (empty($selector) || empty($token)) {
            return ['status' => 'error', 'message' => 'Invalid token'];
        }

        $hashed_token = hash('sha256', $token);

        // Get token from database
        $stmt = $this->conn->prepare("SELECT rmt.customer_id, c.customer_name, c.user_role FROM remember_me_tokens rmt JOIN customer c ON rmt.customer_id = c.customer_id WHERE rmt.selector = ? AND rmt.token = ? AND rmt.expires_at > NOW() AND c.is_active = 1");
        $stmt->bind_param("ss", $selector, $hashed_token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            return ['status' => 'error', 'message' => 'Invalid or expired token'];
        }

        $user = $result->fetch_assoc();
        $stmt->close();

        // Set session variables
        $_SESSION['customer_id'] = $user['customer_id'];
        $_SESSION['customer_name'] = $user['customer_name'];
        $_SESSION['user_role'] = $user['user_role'];

        return ['status' => 'success', 'message' => 'Auto-login successful', 'user_role' => $user['user_role']];
    }

    public function deleteRememberMeToken($customer_id = 0)
    {
        $stmt = $this->conn->prepare("DELETE FROM remember_me_tokens WHERE customer_id = ?");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $stmt->close();
    }

    public function initiatePasswordReset($email = '')
    {
        // Validate email
        if (empty($email)) {
            return ['status' => 'error', 'message' => 'Email is required'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['status' => 'error', 'message' => 'Invalid email format'];
        }

        // Check if email exists and has secret keyword
        $stmt = $this->conn->prepare("SELECT customer_id FROM customer WHERE customer_email = ? AND secret_keyword IS NOT NULL");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            return ['status' => 'error', 'message' => 'Email not found or no secret keyword set'];
        }

        $user = $result->fetch_assoc();
        $stmt->close();

        return ['status' => 'success', 'message' => 'Email verified. Please enter your secret keyword.', 'customer_id' => $user['customer_id']];
    }

    public function resetPassword($email = '', $secret_keyword = '', $password = '', $confirm_password = '')
    {
        // Validate inputs
        if (empty($email) || empty($secret_keyword) || empty($password) || empty($confirm_password)) {
            return ['status' => 'error', 'message' => 'All fields are required'];
        }

        // Validate password strength
        $passwordValidation = $this->validatePassword($password);
        if (!$passwordValidation['valid']) {
            return ['status' => 'error', 'message' => $passwordValidation['message']];
        }

        // Validate password confirmation
        if ($password !== $confirm_password) {
            return ['status' => 'error', 'message' => 'Passwords do not match'];
        }

        // Get user and validate secret keyword
        $stmt = $this->conn->prepare("SELECT customer_id, secret_keyword FROM customer WHERE customer_email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            return ['status' => 'error', 'message' => 'Email not found'];
        }

        $user = $result->fetch_assoc();
        $stmt->close();

        // Verify secret keyword
        if (!password_verify($secret_keyword, $user['secret_keyword'])) {
            return ['status' => 'error', 'message' => 'Invalid secret keyword'];
        }

        // Hash new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Update password
        $update_stmt = $this->conn->prepare("UPDATE customer SET customer_pass = ? WHERE customer_id = ?");
        $update_stmt->bind_param("si", $hashed_password, $user['customer_id']);

        if ($update_stmt->execute()) {
            $update_stmt->close();
            return ['status' => 'success', 'message' => 'Password reset successful. Please login with your new password.'];
        } else {
            $update_stmt->close();
            return ['status' => 'error', 'message' => 'Failed to reset password'];
        }
    }

    public function editProfile($id = 0, $data = [])
    {
        // Validate customer ID
        if (empty($id)) {
            return ['status' => 'error', 'message' => 'Customer ID is required'];
        }

        // Validate required fields
        $required = ['name', 'email', 'country', 'city', 'contact'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['status' => 'error', 'message' => "Field '$field' is required"];
            }
        }

        // Sanitize inputs
        $data['name'] = $this->sanitizeInput($data['name']);
        $data['email'] = $this->sanitizeInput($data['email']);
        $data['country'] = $this->sanitizeInput($data['country']);
        $data['city'] = $this->sanitizeInput($data['city']);
        $data['contact'] = $this->sanitizeInput($data['contact']);

        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['status' => 'error', 'message' => 'Invalid email format'];
        }

        // Check if email is being changed and if it already exists
        $check_email = $this->conn->prepare("SELECT customer_id FROM customer WHERE customer_email = ? AND customer_id != ?");
        $check_email->bind_param("si", $data['email'], $id);
        $check_email->execute();
        $check_email->store_result();

        if ($check_email->num_rows > 0) {
            $check_email->close();
            return ['status' => 'error', 'message' => 'Email already registered to another account'];
        }
        $check_email->close();

        // Handle profile image upload
        $profile_image = null;
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['profile_image'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
            $max_size = 5 * 1024 * 1024; // 5MB

            if (!in_array($file['type'], $allowed_types)) {
                return ['status' => 'error', 'message' => 'Invalid file type. Only JPEG, PNG, and GIF are allowed.'];
            }

            if ($file['size'] > $max_size) {
                return ['status' => 'error', 'message' => 'File size exceeds 5MB limit.'];
            }

            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('profile_', true) . '.' . $extension;
            $upload_path = __DIR__ . '/../uploads/' . $filename;

            if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
                return ['status' => 'error', 'message' => 'Failed to upload profile image.'];
            }

            $profile_image = $filename;
        }

        // Update customer profile
        if ($profile_image) {
            $stmt = $this->conn->prepare("UPDATE customer SET customer_name = ?, customer_email = ?, customer_country = ?, customer_city = ?, customer_contact = ?, customer_image = ? WHERE customer_id = ?");
            $stmt->bind_param("ssssssi", $data['name'], $data['email'], $data['country'], $data['city'], $data['contact'], $profile_image, $id);
        } else {
            $stmt = $this->conn->prepare("UPDATE customer SET customer_name = ?, customer_email = ?, customer_country = ?, customer_city = ?, customer_contact = ? WHERE customer_id = ?");
            $stmt->bind_param("sssssi", $data['name'], $data['email'], $data['country'], $data['city'], $data['contact'], $id);
        }

        if ($stmt->execute()) {
            $stmt->close();
            // Update session
            $_SESSION['customer_name'] = $data['name'];
            return ['status' => 'success', 'message' => 'Profile updated successfully'];
        } else {
            $stmt->close();
            return ['status' => 'error', 'message' => 'Failed to update profile: ' . $this->conn->error];
        }
    }

    public function deleteAccount($id = 0)
    {
        if (empty($id)) {
            return ['status' => 'error', 'message' => 'Customer ID is required'];
        }

        $check_stmt = $this->conn->prepare("SELECT customer_id FROM customer WHERE customer_id = ?");
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows === 0) {
            $check_stmt->close();
            return ['status' => 'error', 'message' => 'Customer not found'];
        }
        $check_stmt->close();

        $this->conn->begin_transaction();

        try {
            $delete_tokens = $this->conn->prepare("DELETE FROM remember_me_tokens WHERE customer_id = ?");
            $delete_tokens->bind_param("i", $id);
            $delete_tokens->execute();
            $delete_tokens->close();

            $delete_cart = $this->conn->prepare("DELETE FROM cart WHERE c_id = ?");
            $delete_cart->bind_param("i", $id);
            $delete_cart->execute();
            $delete_cart->close();

            $delete_payment = $this->conn->prepare("DELETE FROM payment WHERE customer_id = ?");
            $delete_payment->bind_param("i", $id);
            $delete_payment->execute();
            $delete_payment->close();

            $delete_order_details = $this->conn->prepare("DELETE FROM orderdetails WHERE order_id IN (SELECT order_id FROM orders WHERE customer_id = ?)");
            $delete_order_details->bind_param("i", $id);
            $delete_order_details->execute();
            $delete_order_details->close();

            $delete_orders = $this->conn->prepare("DELETE FROM orders WHERE customer_id = ?");
            $delete_orders->bind_param("i", $id);
            $delete_orders->execute();
            $delete_orders->close();

            $delete_customer = $this->conn->prepare("DELETE FROM customer WHERE customer_id = ?");
            $delete_customer->bind_param("i", $id);
            $delete_customer->execute();
            $delete_customer->close();

            $this->conn->commit();
            return ['status' => 'success', 'message' => 'Account deleted successfully'];
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['status' => 'error', 'message' => 'Failed to delete account'];
        }
    }

    public function changePassword($customer_id = 0, $current_password = '', $new_password = '', $confirm_password = '')
    {
        // Validate inputs
        if (empty($customer_id) || empty($current_password) || empty($new_password) || empty($confirm_password)) {
            return ['status' => 'error', 'message' => 'All fields are required'];
        }

        // Validate new password strength
        $passwordValidation = $this->validatePassword($new_password);
        if (!$passwordValidation['valid']) {
            return ['status' => 'error', 'message' => $passwordValidation['message']];
        }

        // Validate password confirmation
        if ($new_password !== $confirm_password) {
            return ['status' => 'error', 'message' => 'New passwords do not match'];
        }

        // Get current password from database
        $stmt = $this->conn->prepare("SELECT customer_pass FROM customer WHERE customer_id = ?");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            return ['status' => 'error', 'message' => 'Customer not found'];
        }

        $user = $result->fetch_assoc();
        $stmt->close();

        // Verify current password
        if (!password_verify($current_password, $user['customer_pass'])) {
            return ['status' => 'error', 'message' => 'Current password is incorrect'];
        }

        // Hash new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update password
        $update_stmt = $this->conn->prepare("UPDATE customer SET customer_pass = ? WHERE customer_id = ?");
        $update_stmt->bind_param("si", $hashed_password, $customer_id);

        if ($update_stmt->execute()) {
            $update_stmt->close();
            return ['status' => 'success', 'message' => 'Password changed successfully'];
        } else {
            $update_stmt->close();
            return ['status' => 'error', 'message' => 'Failed to change password'];
        }
    }

    public function getCustomerProfile($customer_id = 0)
    {
        if (empty($customer_id)) {
            return ['status' => 'error', 'message' => 'Customer ID is required'];
        }

        $stmt = $this->conn->prepare("SELECT customer_id, customer_name, customer_email, customer_country, customer_city, customer_contact, customer_image, user_role FROM customer WHERE customer_id = ?");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            return ['status' => 'error', 'message' => 'Customer not found'];
        }

        $customer = $result->fetch_assoc();
        $stmt->close();

        return ['status' => 'success', 'customer' => $customer];
    }

    private function hasOtherActiveAdmins($id)
    {
        $stmt = $this->conn->prepare("SELECT customer_id FROM customer WHERE user_role = 1 AND is_active = 1 AND customer_id != ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->store_result();
        $hasOtherAdmins = $stmt->num_rows > 0;
        $stmt->close();

        return $hasOtherAdmins;
    }

    public function getAllCustomers($search = '', $role = 0, $status = -1)
    {
        $search = trim($search);
        $searchTerm = '%' . $search . '%';
        $role = in_array((int) $role, [1, 2], true) ? (int) $role : 0;
        $status = in_array((int) $status, [0, 1], true) ? (int) $status : -1;

        $stmt = $this->conn->prepare(
            "SELECT customer_id, customer_name, customer_email, customer_country, customer_city, customer_contact, user_role, is_active
             FROM customer
             WHERE (? = '' OR customer_name LIKE ? OR customer_email LIKE ?)
               AND (? = 0 OR user_role = ?)
               AND (? = -1 OR is_active = ?)
             ORDER BY customer_name ASC"
        );
        $stmt->bind_param("sssiiii", $search, $searchTerm, $searchTerm, $role, $role, $status, $status);
        $stmt->execute();
        $result = $stmt->get_result();

        $customers = [];
        while ($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }

        $stmt->close();
        return $customers;
    }

    public function getCustomerById($id = 0)
    {
        $id = (int) $id;

        if ($id <= 0) {
            return null;
        }

        $stmt = $this->conn->prepare("SELECT customer_id, customer_name, customer_email, customer_country, customer_city, customer_contact, user_role, is_active FROM customer WHERE customer_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $customer = $result->fetch_assoc();
        $stmt->close();

        return $customer ?: null;
    }

    public function getCustomerAccessStatus($id = 0)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->conn->prepare("SELECT customer_name, customer_email, user_role, is_active FROM customer WHERE customer_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $access = $result->fetch_assoc();
        $stmt->close();

        return $access ?: null;
    }

    public function adminUpdateCustomerRole($id = 0, $role = 0)
    {
        $id = (int) $id;
        $role = (int) $role;

        if ($id <= 0 || !in_array($role, [1, 2], true)) {
            return ['success' => false, 'error' => 'Invalid user or role'];
        }

        $customer = $this->getCustomerById($id);
        if (!$customer) {
            return ['success' => false, 'error' => 'User not found'];
        }

        if ($id === (int) ($_SESSION['customer_id'] ?? 0) && $role !== (int) $customer['user_role']) {
            return ['success' => false, 'error' => 'You cannot change your own role'];
        }

        if ((int) $customer['user_role'] === 1 && $role !== 1 && (int) $customer['is_active'] === 1 && !$this->hasOtherActiveAdmins($id)) {
            return ['success' => false, 'error' => 'The last active administrator cannot be demoted'];
        }

        $stmt = $this->conn->prepare("UPDATE customer SET user_role = ? WHERE customer_id = ?");
        $stmt->bind_param("ii", $role, $id);

        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'User role updated'];
        }

        $error = $this->conn->error;
        $stmt->close();
        return ['success' => false, 'error' => 'Failed to update user role: ' . $error];
    }

    public function adminSetCustomerActive($id = 0, $active = 0)
    {
        $id = (int) $id;
        $active = (int) $active;

        if ($id <= 0 || !in_array($active, [0, 1], true)) {
            return ['success' => false, 'error' => 'Invalid user or account status'];
        }

        $customer = $this->getCustomerById($id);
        if (!$customer) {
            return ['success' => false, 'error' => 'User not found'];
        }

        if ($id === (int) ($_SESSION['customer_id'] ?? 0) && $active === 0) {
            return ['success' => false, 'error' => 'You cannot deactivate your own account'];
        }

        if ((int) $customer['user_role'] === 1 && (int) $customer['is_active'] === 1 && $active === 0 && !$this->hasOtherActiveAdmins($id)) {
            return ['success' => false, 'error' => 'The last active administrator cannot be deactivated'];
        }

        $stmt = $this->conn->prepare("UPDATE customer SET is_active = ? WHERE customer_id = ?");
        $stmt->bind_param("ii", $active, $id);

        if ($stmt->execute()) {
            $stmt->close();

            if ($active === 0) {
                $this->deleteRememberMeToken($id);
            }

            return ['success' => true, 'message' => $active === 1 ? 'User account activated' : 'User account deactivated'];
        }

        $error = $this->conn->error;
        $stmt->close();
        return ['success' => false, 'error' => 'Failed to update account status: ' . $error];
    }

    public function adminDeleteCustomer($id = 0)
    {
        $id = (int) $id;

        if ($id <= 0) {
            return ['success' => false, 'error' => 'Invalid user ID'];
        }

        if ($id === (int) ($_SESSION['customer_id'] ?? 0)) {
            return ['success' => false, 'error' => 'You cannot delete your own account'];
        }

        $customer = $this->getCustomerById($id);
        if (!$customer) {
            return ['success' => false, 'error' => 'User not found'];
        }

        if ((int) $customer['user_role'] === 1 && (int) $customer['is_active'] === 1 && !$this->hasOtherActiveAdmins($id)) {
            return ['success' => false, 'error' => 'The last active administrator cannot be deleted'];
        }

        $result = $this->deleteAccount($id);
        if ($result['status'] === 'success') {
            return ['success' => true, 'message' => $result['message']];
        }

        return ['success' => false, 'error' => $result['message']];
    }
}

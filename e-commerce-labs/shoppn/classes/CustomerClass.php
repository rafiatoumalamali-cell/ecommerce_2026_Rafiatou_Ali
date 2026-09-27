<?php
require_once __DIR__ . '/../core/db_class.php';

class CustomerClass extends DbClass
{
    public function register($data = [])
    {
        // Validate required fields
        $required = ['name', 'email', 'password', 'country', 'city', 'contact'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['status' => 'error', 'message' => "Field '$field' is required"];
            }
        }

        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['status' => 'error', 'message' => 'Invalid email format'];
        }

        // Check if email already exists
        $check_email = $this->conn->prepare("SELECT customer_id FROM customer WHERE customer_email = ?");
        $check_email->bind_param("s", $data['email']);
        $check_email->execute();
        $check_email->store_result();

        if ($check_email->num_rows > 0) {
            $check_email->close();
            return ['status' => 'error', 'message' => 'Email already registered'];
        }
        $check_email->close();

        // Hash password
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

        // Insert new customer
        $stmt = $this->conn->prepare("INSERT INTO customer (customer_name, customer_email, customer_pass, customer_country, customer_city, customer_contact, user_role) VALUES (?, ?, ?, ?, ?, ?, 2)");
        $stmt->bind_param("ssssss", $data['name'], $data['email'], $hashed_password, $data['country'], $data['city'], $data['contact']);

        if ($stmt->execute()) {
            $stmt->close();
            return ['status' => 'success', 'message' => 'Registration successful'];
        } else {
            $stmt->close();
            return ['status' => 'error', 'message' => 'Registration failed: ' . $this->conn->error];
        }
    }

    public function login($email = '', $password = '')
    {
        // Validate inputs
        if (empty($email) || empty($password)) {
            return ['status' => 'error', 'message' => 'Email and password are required'];
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['status' => 'error', 'message' => 'Invalid email format'];
        }

        // Fetch user by email
        $stmt = $this->conn->prepare("SELECT customer_id, customer_name, customer_pass, user_role FROM customer WHERE customer_email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            return ['status' => 'error', 'message' => 'Invalid email or password'];
        }

        $user = $result->fetch_assoc();
        $stmt->close();

        // Verify password
        if (!password_verify($password, $user['customer_pass'])) {
            return ['status' => 'error', 'message' => 'Invalid email or password'];
        }

        // Set session variables
        $_SESSION['customer_id'] = $user['customer_id'];
        $_SESSION['customer_name'] = $user['customer_name'];
        $_SESSION['user_role'] = $user['user_role'];

        return ['status' => 'success', 'message' => 'Login successful', 'user_role' => $user['user_role']];
    }

    public function editProfile($id = 0, $data = [])
    {
        return ['status' => 'not_implemented'];
    }

    public function deleteAccount($id = 0)
    {
        return ['status' => 'not_implemented'];
    }
}

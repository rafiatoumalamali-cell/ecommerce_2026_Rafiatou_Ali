<?php
require_once __DIR__ . '/../classes/CustomerClass.php';

class CustomerController
{
    private $customer;

    public function __construct()
    {
        $this->customer = new CustomerClass();
    }

    public function register($data = [])
    {
        return $this->customer->register($data);
    }

    public function login($email = '', $password = '')
    {
        $user = $this->customer->login($email, $password);

        if ($user === false) {
            return ['success' => false, 'error' => 'Invalid email or password'];
        }

        if (!empty($user['account_deactivated'])) {
            return [
                'success' => false,
                'error' => 'Your account has been deactivated. Please contact customer support for assistance.'
            ];
        }

        return ['success' => true, 'customer' => $user];
    }

    public function setRememberMeToken($customer_id = 0)
    {
        return $this->customer->setRememberMeToken($customer_id);
    }

    public function validateRememberMeToken($selector = '', $token = '')
    {
        return $this->customer->validateRememberMeToken($selector, $token);
    }

    public function deleteRememberMeToken($customer_id = 0)
    {
        return $this->customer->deleteRememberMeToken($customer_id);
    }

    public function initiatePasswordReset($email = '')
    {
        return $this->customer->initiatePasswordReset($email);
    }

    public function resetPassword($email = '', $secret_keyword = '', $password = '', $confirm_password = '')
    {
        return $this->customer->resetPassword($email, $secret_keyword, $password, $confirm_password);
    }

    public function editProfile($id = 0, $data = [])
    {
        return $this->customer->editProfile($id, $data);
    }

    public function changePassword($customer_id = 0, $current_password = '', $new_password = '', $confirm_password = '')
    {
        return $this->customer->changePassword($customer_id, $current_password, $new_password, $confirm_password);
    }

    public function deleteAccount($id = 0)
    {
        return $this->customer->deleteAccount($id);
    }

    public function getCustomerProfile($customer_id = 0)
    {
        return $this->customer->getCustomerProfile($customer_id);
    }

    public function getAllCustomers($search = '', $role = 0, $status = -1)
    {
        return $this->customer->getAllCustomers($search, $role, $status);
    }

    public function getCustomerById($id = 0)
    {
        return $this->customer->getCustomerById($id);
    }

    public function getCustomerAccessStatus($id = 0)
    {
        return $this->customer->getCustomerAccessStatus($id);
    }

    public function adminUpdateCustomerRole($id = 0, $role = 0)
    {
        return $this->customer->adminUpdateCustomerRole($id, $role);
    }

    public function adminSetCustomerActive($id = 0, $active = 0)
    {
        return $this->customer->adminSetCustomerActive($id, $active);
    }

    public function adminDeleteCustomer($id = 0)
    {
        return $this->customer->adminDeleteCustomer($id);
    }
}

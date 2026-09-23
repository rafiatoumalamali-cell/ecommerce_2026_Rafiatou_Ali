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
        return $this->customer->login($email, $password);
    }

    public function editProfile($id = 0, $data = [])
    {
        return $this->customer->editProfile($id, $data);
    }

    public function deleteAccount($id = 0)
    {
        return $this->customer->deleteAccount($id);
    }
}

<?php
require_once __DIR__ . '/../core/db_class.php';

class CustomerClass extends DbClass
{
    public function register($data = [])
    {
        return ['status' => 'not_implemented'];
    }

    public function login($email = '', $password = '')
    {
        return ['status' => 'not_implemented'];
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

<?php
require_once __DIR__ . '/../classes/ProductClass.php';

class ProductController
{
    private $product;

    public function __construct()
    {
        $this->product = new ProductClass();
    }

    public function addBrand($data = [])
    {
        return $this->product->addBrand($data);
    }

    public function updateBrand($id = 0, $data = [])
    {
        return $this->product->updateBrand($id, $data);
    }

    public function addCategory($data = [])
    {
        return $this->product->addCategory($data);
    }

    public function updateCategory($id = 0, $data = [])
    {
        return $this->product->updateCategory($id, $data);
    }

    public function addProduct($data = [])
    {
        return $this->product->addProduct($data);
    }

    public function updateProduct($id = 0, $data = [])
    {
        return $this->product->updateProduct($id, $data);
    }
}

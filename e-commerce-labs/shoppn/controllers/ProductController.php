<?php
require_once __DIR__ . '/../classes/ProductClass.php';

class ProductController
{
    private $product;

    public function __construct()
    {
        $this->product = new ProductClass();
    }

    public function addBrand($name = '')
    {
        return $this->product->addBrand($name);
    }

    public function getAllBrands()
    {
        return $this->product->getAllBrands();
    }

    public function getBrandById($id = 0)
    {
        return $this->product->getBrandById($id);
    }

    public function updateBrand($id = 0, $name = '')
    {
        return $this->product->updateBrand($id, $name);
    }

    public function deleteBrand($id = 0)
    {
        return $this->product->deleteBrand($id);
    }

    public function addCategory($name = '')
    {
        return $this->product->addCategory($name);
    }

    public function getAllCategories()
    {
        return $this->product->getAllCategories();
    }

    public function getCategoryById($id = 0)
    {
        return $this->product->getCategoryById($id);
    }

    public function updateCategory($id = 0, $name = '')
    {
        return $this->product->updateCategory($id, $name);
    }

    public function deleteCategory($id = 0)
    {
        return $this->product->deleteCategory($id);
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

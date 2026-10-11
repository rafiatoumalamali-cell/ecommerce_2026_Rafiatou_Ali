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

    public function addProduct($cat, $brand, $title, $price, $desc, $image_filename, $keywords, $stockQuantity)
    {
        return $this->product->addProduct($cat, $brand, $title, $price, $desc, $image_filename, $keywords, $stockQuantity);
    }

    public function updateProduct($id, $cat, $brand, $title, $price, $desc, $image_filename, $keywords, $stockQuantity)
    {
        return $this->product->updateProduct($id, $cat, $brand, $title, $price, $desc, $image_filename, $keywords, $stockQuantity);
    }

    public function updateStockQuantity($id, $stockQuantity)
    {
        return $this->product->updateStockQuantity($id, $stockQuantity);
    }

    public function deleteProduct($id)
    {
        return $this->product->deleteProduct($id);
    }

    public function getProductById($id)
    {
        return $this->product->getProductById($id);
    }

    public function getAllProducts($categoryId = null, $brandId = null)
    {
        return $this->product->getAllProducts($categoryId, $brandId);
    }

    public function getFeaturedProducts($limit = 6)
    {
        return $this->product->getFeaturedProducts($limit);
    }

    public function getProductsByCategory($catId)
    {
        return $this->product->getProductsByCategory($catId);
    }

    public function getProductsByBrand($brandId)
    {
        return $this->product->getProductsByBrand($brandId);
    }

    public function searchProducts($query)
    {
        return $this->product->searchProducts($query);
    }
}

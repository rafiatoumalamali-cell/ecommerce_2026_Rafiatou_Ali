<?php
require_once __DIR__ . '/../core/db_class.php';

class ProductClass extends DbClass
{
    public function addBrand($name = '')
    {
        $name = trim(strip_tags($name));

        if ($name === '') {
            return ['success' => false, 'error' => 'Brand name is required'];
        }

        if (strlen($name) > 100) {
            return ['success' => false, 'error' => 'Brand name is too long'];
        }

        $stmt = $this->conn->prepare("INSERT INTO brands (brand_name) VALUES (?)");
        $stmt->bind_param("s", $name);

        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Brand added.'];
        }

        $error = $this->conn->error;
        $stmt->close();
        return ['success' => false, 'error' => 'Failed to add brand: ' . $error];
    }

    public function getAllBrands()
    {
        $stmt = $this->conn->prepare("SELECT * FROM brands ORDER BY brand_name ASC");
        $stmt->execute();
        $result = $stmt->get_result();

        $brands = [];
        while ($row = $result->fetch_assoc()) {
            $brands[] = $row;
        }

        $stmt->close();
        return $brands;
    }

    public function getBrandById($id = 0)
    {
        $id = (int) $id;

        if ($id <= 0) {
            return null;
        }

        $stmt = $this->conn->prepare("SELECT * FROM brands WHERE brand_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $brand = $result->fetch_assoc();
        $stmt->close();

        return $brand ?: null;
    }

    public function updateBrand($id = 0, $name = '')
    {
        $id = (int) $id;
        $name = trim(strip_tags($name));

        if ($id <= 0) {
            return ['success' => false, 'error' => 'Invalid brand ID'];
        }

        if ($name === '') {
            return ['success' => false, 'error' => 'Brand name is required'];
        }

        if (strlen($name) > 100) {
            return ['success' => false, 'error' => 'Brand name is too long'];
        }

        $stmt = $this->conn->prepare("UPDATE brands SET brand_name = ? WHERE brand_id = ?");
        $stmt->bind_param("si", $name, $id);

        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Brand updated.'];
        }

        $error = $this->conn->error;
        $stmt->close();
        return ['success' => false, 'error' => 'Failed to update brand: ' . $error];
    }

    public function deleteBrand($id = 0)
    {
        $id = (int) $id;

        if ($id <= 0) {
            return ['success' => false, 'error' => 'Invalid brand ID'];
        }

        $stmt = $this->conn->prepare("DELETE FROM brands WHERE brand_id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Brand deleted.'];
        }

        $error = $this->conn->error;
        $stmt->close();
        return ['success' => false, 'error' => 'Failed to delete brand: ' . $error];
    }

    public function addCategory($name = '')
    {
        if (is_array($name)) {
            $name = $name['cat_name'] ?? $name['category_name'] ?? '';
        }

        $name = trim(strip_tags($name));

        if ($name === '') {
            return ['success' => false, 'error' => 'Category name is required'];
        }

        if (strlen($name) > 100) {
            return ['success' => false, 'error' => 'Category name is too long'];
        }

        $stmt = $this->conn->prepare("INSERT INTO categories (cat_name) VALUES (?)");
        $stmt->bind_param("s", $name);

        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Category added.'];
        }

        $error = $this->conn->error;
        $stmt->close();
        return ['success' => false, 'error' => 'Failed to add category: ' . $error];
    }

    public function getAllCategories()
    {
        $stmt = $this->conn->prepare("SELECT * FROM categories ORDER BY cat_name ASC");
        $stmt->execute();
        $result = $stmt->get_result();

        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }

        $stmt->close();
        return $categories;
    }

    public function getCategoryById($id = 0)
    {
        $id = (int) $id;

        if ($id <= 0) {
            return null;
        }

        $stmt = $this->conn->prepare("SELECT * FROM categories WHERE cat_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $category = $result->fetch_assoc();
        $stmt->close();

        return $category ?: null;
    }

    public function updateCategory($id = 0, $name = '')
    {
        if (is_array($name)) {
            $name = $name['cat_name'] ?? $name['category_name'] ?? '';
        }

        $id = (int) $id;
        $name = trim(strip_tags($name));

        if ($id <= 0) {
            return ['success' => false, 'error' => 'Invalid category ID'];
        }

        if ($name === '') {
            return ['success' => false, 'error' => 'Category name is required'];
        }

        if (strlen($name) > 100) {
            return ['success' => false, 'error' => 'Category name is too long'];
        }

        $stmt = $this->conn->prepare("UPDATE categories SET cat_name = ? WHERE cat_id = ?");
        $stmt->bind_param("si", $name, $id);

        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Category updated.'];
        }

        $error = $this->conn->error;
        $stmt->close();
        return ['success' => false, 'error' => 'Failed to update category: ' . $error];
    }

    public function deleteCategory($id = 0)
    {
        $id = (int) $id;

        if ($id <= 0) {
            return ['success' => false, 'error' => 'Invalid category ID'];
        }

        $stmt = $this->conn->prepare("DELETE FROM categories WHERE cat_id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Category deleted.'];
        }

        $error = $this->conn->error;
        $stmt->close();
        return ['success' => false, 'error' => 'Failed to delete category: ' . $error];
    }

    public function addProduct($data = [])
    {
        return ['status' => 'not_implemented'];
    }

    public function updateProduct($id = 0, $data = [])
    {
        return ['status' => 'not_implemented'];
    }
}

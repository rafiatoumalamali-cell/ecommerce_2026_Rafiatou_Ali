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

    public function addProduct($cat, $brand, $title, $price, $desc, $image_filename, $keywords, $stockQuantity)
    {
        $data = $this->validateProductData($cat, $brand, $title, $price, $desc, $image_filename, $keywords, $stockQuantity);
        if (!$data['success']) {
            return $data;
        }

        $stmt = $this->conn->prepare(
            "INSERT INTO products (product_cat, product_brand, product_title, product_price, product_desc, product_image, product_keywords, stock_quantity)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        if (!$stmt) {
            error_log('Failed to prepare product insert: ' . $this->conn->error);
            return ['success' => false, 'error' => 'Unable to add the product right now.'];
        }

        $stmt->bind_param(
            "iisdsssi",
            $data['cat'],
            $data['brand'],
            $data['title'],
            $data['price'],
            $data['desc'],
            $data['image_filename'],
            $data['keywords'],
            $data['stock_quantity']
        );

        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Product added successfully.'];
        }

        error_log('Failed to add product: ' . $stmt->error);
        $stmt->close();
        return ['success' => false, 'error' => 'Unable to add the product. Check that the selected category and brand still exist.'];
    }

    public function updateProduct($id, $cat, $brand, $title, $price, $desc, $image_filename, $keywords, $stockQuantity)
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if ($id === false || $id <= 0) {
            return ['success' => false, 'error' => 'Invalid product ID.'];
        }

        $data = $this->validateProductData($cat, $brand, $title, $price, $desc, $image_filename, $keywords, $stockQuantity);
        if (!$data['success']) {
            return $data;
        }

        $stmt = $this->conn->prepare(
            "UPDATE products
             SET product_cat = ?, product_brand = ?, product_title = ?, product_price = ?,
                 product_desc = ?, product_image = ?, product_keywords = ?, stock_quantity = ?
             WHERE product_id = ?"
        );
        if (!$stmt) {
            error_log('Failed to prepare product update: ' . $this->conn->error);
            return ['success' => false, 'error' => 'Unable to update the product right now.'];
        }

        $stmt->bind_param(
            "iisdsssii",
            $data['cat'],
            $data['brand'],
            $data['title'],
            $data['price'],
            $data['desc'],
            $data['image_filename'],
            $data['keywords'],
            $data['stock_quantity'],
            $id
        );

        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Product updated successfully.'];
        }

        error_log('Failed to update product: ' . $stmt->error);
        $stmt->close();
        return ['success' => false, 'error' => 'Unable to update the product. Check that the selected category and brand still exist.'];
    }

    public function updateStockQuantity($id, $stockQuantity)
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        $stockQuantity = filter_var($stockQuantity, FILTER_VALIDATE_INT);
        if (
            $id === false || $id <= 0
            || $stockQuantity === false || $stockQuantity < 0 || $stockQuantity > 2147483647
        ) {
            return ['success' => false, 'error' => 'Enter a valid product and whole-number stock quantity.'];
        }

        $stmt = $this->conn->prepare("UPDATE products SET stock_quantity = ? WHERE product_id = ?");
        if (!$stmt) {
            error_log('Failed to prepare inventory update: ' . $this->conn->error);
            return ['success' => false, 'error' => 'Unable to update inventory right now.'];
        }
        $stmt->bind_param("ii", $stockQuantity, $id);
        if (!$stmt->execute()) {
            error_log('Failed to update inventory: ' . $stmt->error);
            $stmt->close();
            return ['success' => false, 'error' => 'Unable to update inventory right now.'];
        }

        $updated = $stmt->affected_rows === 1;
        $stmt->close();
        if (!$updated) {
            $product = $this->getProductById($id);
            if (!$product) {
                return ['success' => false, 'error' => 'Product not found.'];
            }
        }

        return ['success' => true, 'message' => 'Stock quantity updated.'];
    }

    public function deleteProduct($id)
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if ($id === false || $id <= 0) {
            return ['success' => false, 'error' => 'Invalid product ID.'];
        }

        $lookup = $this->conn->prepare("SELECT product_image FROM products WHERE product_id = ?");
        if (!$lookup) {
            error_log('Failed to prepare product delete lookup: ' . $this->conn->error);
            return ['success' => false, 'error' => 'Unable to delete the product right now.'];
        }
        $lookup->bind_param("i", $id);
        if (!$lookup->execute()) {
            error_log('Failed to look up product before deletion: ' . $lookup->error);
            $lookup->close();
            return ['success' => false, 'error' => 'Unable to delete the product right now.'];
        }
        $product = $lookup->get_result()->fetch_assoc();
        $lookup->close();

        if (!$product) {
            return ['success' => false, 'error' => 'Product not found.'];
        }

        $orderCheck = $this->conn->prepare("SELECT 1 FROM orderdetails WHERE product_id = ? LIMIT 1");
        if (!$orderCheck) {
            error_log('Failed to prepare product order-history check: ' . $this->conn->error);
            return ['success' => false, 'error' => 'Unable to verify the product order history. Please try again.'];
        }
        $orderCheck->bind_param("i", $id);
        if (!$orderCheck->execute()) {
            error_log('Failed to check product order history: ' . $orderCheck->error);
            $orderCheck->close();
            return ['success' => false, 'error' => 'Unable to verify the product order history. Please try again.'];
        }
        $hasOrderHistory = $orderCheck->get_result()->num_rows > 0;
        $orderCheck->close();

        if ($hasOrderHistory) {
            return ['success' => false, 'error' => 'This product is part of an order and cannot be deleted.'];
        }

        $stmt = $this->conn->prepare("DELETE FROM products WHERE product_id = ?");
        if (!$stmt) {
            error_log('Failed to prepare product deletion: ' . $this->conn->error);
            return ['success' => false, 'error' => 'Unable to delete the product right now.'];
        }
        $stmt->bind_param("i", $id);

        if ($stmt->execute() && $stmt->affected_rows === 1) {
            $stmt->close();
            return [
                'success' => true,
                'message' => 'Product deleted successfully.',
                'image_filename' => $product['product_image'],
            ];
        }

        $errorCode = $stmt->errno;
        error_log('Failed to delete product: ' . $stmt->error);
        $stmt->close();

        if ($errorCode === 1451) {
            return ['success' => false, 'error' => 'This product is part of an order and cannot be deleted.'];
        }
        return ['success' => false, 'error' => 'Unable to delete the product. Please try again.'];
    }

    public function getProductById($id)
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if ($id === false || $id <= 0) {
            return null;
        }

        $stmt = $this->conn->prepare(
            "SELECT p.*, c.cat_name, b.brand_name
             FROM products p
             INNER JOIN categories c ON p.product_cat = c.cat_id
             INNER JOIN brands b ON p.product_brand = b.brand_id
             WHERE p.product_id = ?"
        );
        if (!$stmt) {
            error_log('Failed to prepare product lookup: ' . $this->conn->error);
            return null;
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();

        return $product ?: null;
    }

    public function getFeaturedProducts($limit = 6)
    {
        $limit = filter_var($limit, FILTER_VALIDATE_INT);
        if ($limit === false || $limit <= 0) {
            $limit = 6;
        }
        $limit = min($limit, 50);

        return $this->fetchProducts(
            "SELECT p.*, c.cat_name, b.brand_name
             FROM products p
             INNER JOIN categories c ON p.product_cat = c.cat_id
             INNER JOIN brands b ON p.product_brand = b.brand_id
             ORDER BY RAND()
             LIMIT ?",
            "i",
            [$limit]
        );
    }

    public function getProductsByCategory($catId)
    {
        $catId = filter_var($catId, FILTER_VALIDATE_INT);
        if ($catId === false || $catId <= 0) {
            return [];
        }

        return $this->fetchProducts(
            "SELECT p.*, c.cat_name, b.brand_name
             FROM products p
             INNER JOIN categories c ON p.product_cat = c.cat_id
             INNER JOIN brands b ON p.product_brand = b.brand_id
             WHERE p.product_cat = ?
             ORDER BY p.product_id DESC",
            "i",
            [$catId]
        );
    }

    public function getProductsByBrand($brandId)
    {
        $brandId = filter_var($brandId, FILTER_VALIDATE_INT);
        if ($brandId === false || $brandId <= 0) {
            return [];
        }

        return $this->fetchProducts(
            "SELECT p.*, c.cat_name, b.brand_name
             FROM products p
             INNER JOIN categories c ON p.product_cat = c.cat_id
             INNER JOIN brands b ON p.product_brand = b.brand_id
             WHERE p.product_brand = ?
             ORDER BY p.product_id DESC",
            "i",
            [$brandId]
        );
    }

    public function searchProducts($query)
    {
        $query = trim((string) $query);
        if ($query === '') {
            return [];
        }

        $searchTerm = '%' . $query . '%';
        return $this->fetchProducts(
            "SELECT p.*, c.cat_name, b.brand_name
             FROM products p
             INNER JOIN categories c ON p.product_cat = c.cat_id
             INNER JOIN brands b ON p.product_brand = b.brand_id
             WHERE p.product_title LIKE ? OR p.product_keywords LIKE ?
             ORDER BY p.product_id DESC",
            "ss",
            [$searchTerm, $searchTerm]
        );
    }

    private function fetchProducts($sql, $types = '', $values = [])
    {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log('Failed to prepare product query: ' . $this->conn->error);
            return [];
        }

        if ($types !== '') {
            $stmt->bind_param($types, ...$values);
        }

        if (!$stmt->execute()) {
            error_log('Failed to load products: ' . $stmt->error);
            $stmt->close();
            return [];
        }

        $result = $stmt->get_result();
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        $stmt->close();

        return $products;
    }

    public function getAllProducts($categoryId = null, $brandId = null)
    {
        if ($categoryId !== null) {
            $categoryId = filter_var($categoryId, FILTER_VALIDATE_INT);
            if ($categoryId === false || $categoryId <= 0) {
                return false;
            }
        }

        if ($brandId !== null) {
            $brandId = filter_var($brandId, FILTER_VALIDATE_INT);
            if ($brandId === false || $brandId <= 0) {
                return false;
            }
        }

        $sql = "SELECT p.*, c.cat_name, b.brand_name
                FROM products p
                INNER JOIN categories c ON p.product_cat = c.cat_id
                INNER JOIN brands b ON p.product_brand = b.brand_id";
        if ($categoryId !== null && $brandId !== null) {
            $sql .= " WHERE p.product_cat = ? AND p.product_brand = ?";
        } elseif ($categoryId !== null) {
            $sql .= " WHERE p.product_cat = ?";
        } elseif ($brandId !== null) {
            $sql .= " WHERE p.product_brand = ?";
        }
        $sql .= " ORDER BY p.product_id DESC";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log('Failed to prepare product listing: ' . $this->conn->error);
            return false;
        }

        if ($categoryId !== null && $brandId !== null) {
            $stmt->bind_param("ii", $categoryId, $brandId);
        } elseif ($categoryId !== null) {
            $stmt->bind_param("i", $categoryId);
        } elseif ($brandId !== null) {
            $stmt->bind_param("i", $brandId);
        }

        if (!$stmt->execute()) {
            error_log('Failed to load products: ' . $stmt->error);
            $stmt->close();
            return false;
        }

        $result = $stmt->get_result();
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        $stmt->close();

        return $products;
    }

    private function validateProductData($cat, $brand, $title, $price, $desc, $image_filename, $keywords, $stockQuantity)
    {
        $cat = filter_var($cat, FILTER_VALIDATE_INT);
        $brand = filter_var($brand, FILTER_VALIDATE_INT);
        $stockQuantity = filter_var($stockQuantity, FILTER_VALIDATE_INT);
        $title = trim((string) $title);
        $desc = trim((string) $desc);
        $image_filename = trim((string) $image_filename);
        $keywords = trim((string) $keywords);

        if ($cat === false || $cat <= 0 || $brand === false || $brand <= 0) {
            return ['success' => false, 'error' => 'Select a valid category and brand.'];
        }

        if ($title === '' || strlen($title) > 200) {
            return ['success' => false, 'error' => 'Product title is required and must be 200 characters or fewer.'];
        }

        if (!is_numeric($price) || !is_finite((float) $price) || (float) $price < 0) {
            return ['success' => false, 'error' => 'Enter a valid, non-negative product price.'];
        }

        if ($stockQuantity === false || $stockQuantity < 0 || $stockQuantity > 2147483647) {
            return ['success' => false, 'error' => 'Enter a whole-number stock quantity of zero or more.'];
        }

        if (strlen($desc) > 500 || strlen($image_filename) > 100 || strlen($keywords) > 100) {
            return ['success' => false, 'error' => 'Description, image filename, or keywords exceed the allowed length.'];
        }

        return [
            'success' => true,
            'cat' => $cat,
            'brand' => $brand,
            'title' => $title,
            'price' => (float) $price,
            'desc' => $desc,
            'image_filename' => $image_filename,
            'keywords' => $keywords,
            'stock_quantity' => $stockQuantity,
        ];
    }
}

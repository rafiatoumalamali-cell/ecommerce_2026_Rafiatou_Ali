<?php
require_once __DIR__ . '/../core/db_class.php';

class CartClass extends DbClass
{
    public function isInCart($productId, $ip)
    {
        $productId = filter_var($productId, FILTER_VALIDATE_INT);
        if ($productId === false || $productId <= 0 || !$this->isValidIp($ip)) {
            return false;
        }

        $stmt = $this->conn->prepare("SELECT 1 FROM cart WHERE p_id = ? AND ip_add = ? LIMIT 1");
        if (!$stmt) {
            error_log('Failed to prepare cart duplicate check: ' . $this->conn->error);
            return false;
        }
        $stmt->bind_param("is", $productId, $ip);
        if (!$stmt->execute()) {
            error_log('Failed to check cart duplicate: ' . $stmt->error);
            $stmt->close();
            return false;
        }

        $found = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        return $found;
    }

    public function addToCart($productId, $quantity, $ip, $customerId = null)
    {
        $productId = filter_var($productId, FILTER_VALIDATE_INT);
        $quantity = filter_var($quantity, FILTER_VALIDATE_INT);
        if ($productId === false || $productId <= 0 || $quantity === false || $quantity < 1) {
            return ['success' => false, 'error' => 'Choose a valid product and quantity.'];
        }
        if (!$this->isValidIp($ip)) {
            return ['success' => false, 'error' => 'Unable to identify this shopping session.'];
        }

        $stockStmt = $this->conn->prepare("SELECT stock_quantity FROM products WHERE product_id = ? LIMIT 1");
        if (!$stockStmt) {
            error_log('Failed to prepare product stock lookup: ' . $this->conn->error);
            return ['success' => false, 'error' => 'Unable to check product availability right now.'];
        }
        $stockStmt->bind_param("i", $productId);
        if (!$stockStmt->execute()) {
            error_log('Failed to check product stock: ' . $stockStmt->error);
            $stockStmt->close();
            return ['success' => false, 'error' => 'Unable to check product availability right now.'];
        }
        $product = $stockStmt->get_result()->fetch_assoc();
        $stockStmt->close();
        if (!$product || $quantity > (int) $product['stock_quantity']) {
            return ['success' => false, 'error' => 'The requested quantity is not currently available.'];
        }

        $stmt = $this->conn->prepare("INSERT INTO cart (p_id, ip_add, qty) VALUES (?, ?, ?)");
        if (!$stmt) {
            error_log('Failed to prepare add-to-cart query: ' . $this->conn->error);
            return ['success' => false, 'error' => 'Unable to add this product to your cart right now.'];
        }
        $stmt->bind_param("isi", $productId, $ip, $quantity);
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Product added to your cart.'];
        }

        error_log('Failed to add product to cart: ' . $stmt->error);
        $stmt->close();
        return ['success' => false, 'error' => 'Unable to add this product to your cart.'];
    }

    public function getCartItems($ip)
    {
        if (!$this->isValidIp($ip)) {
            return [];
        }

        $stmt = $this->conn->prepare(
            "SELECT cart.*, products.product_title, products.product_price, products.product_image,
                    products.stock_quantity
             FROM cart
             INNER JOIN products ON cart.p_id = products.product_id
             WHERE cart.ip_add = ?
             ORDER BY products.product_title ASC"
        );
        if (!$stmt) {
            error_log('Failed to prepare cart items query: ' . $this->conn->error);
            return [];
        }
        $stmt->bind_param("s", $ip);
        if (!$stmt->execute()) {
            error_log('Failed to load cart items: ' . $stmt->error);
            $stmt->close();
            return [];
        }

        $result = $stmt->get_result();
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        $stmt->close();
        return $items;
    }

    public function getCartCount($ip)
    {
        if (!$this->isValidIp($ip)) {
            return 0;
        }

        $stmt = $this->conn->prepare("SELECT COUNT(*) AS item_count FROM cart WHERE ip_add = ?");
        if (!$stmt) {
            error_log('Failed to prepare cart count query: ' . $this->conn->error);
            return 0;
        }
        $stmt->bind_param("s", $ip);
        if (!$stmt->execute()) {
            error_log('Failed to load cart count: ' . $stmt->error);
            $stmt->close();
            return 0;
        }

        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int) ($row['item_count'] ?? 0);
    }

    public function getCartTotal($ip)
    {
        $total = 0.0;
        foreach ($this->getCartItems($ip) as $item) {
            $total += (int) $item['qty'] * (float) $item['product_price'];
        }
        return $total;
    }

    public function validateCheckoutDetails($shipping)
    {
        if (!is_array($shipping)) {
            return ['success' => false, 'error' => 'Delivery details are required.'];
        }

        $fields = [
            'name' => ['label' => 'Full name', 'max' => 100],
            'email' => ['label' => 'Email', 'max' => 100],
            'contact' => ['label' => 'Phone number', 'max' => 30],
            'address' => ['label' => 'Delivery address', 'max' => 255],
            'city' => ['label' => 'City', 'max' => 100],
            'country' => ['label' => 'Country', 'max' => 100],
        ];
        $details = [];

        foreach ($fields as $field => $configuration) {
            $value = $shipping[$field] ?? '';
            if (!is_string($value)) {
                return ['success' => false, 'error' => $configuration['label'] . ' is invalid.'];
            }
            $value = trim($value);
            if ($value === '') {
                return ['success' => false, 'error' => $configuration['label'] . ' is required.'];
            }
            if (strlen($value) > $configuration['max']) {
                return ['success' => false, 'error' => $configuration['label'] . ' is too long.'];
            }
            $details[$field] = $value;
        }

        if (!filter_var($details['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Enter a valid email address.'];
        }
        if (!preg_match('/^[0-9+\-\s()]{7,30}$/', $details['contact'])) {
            return ['success' => false, 'error' => 'Enter a valid phone number.'];
        }

        return ['success' => true, 'details' => $details];
    }

    public function processPayment($customerId, $ip, $reference, $amount, $shipping = [], $cartSnapshot = [], $currency = 'GHS')
    {
        if (!$this->isValidIp($ip) || !is_string($reference) || !preg_match('/^[A-Za-z0-9._-]{1,100}$/', $reference)) {
            return ['success' => false, 'error' => 'The payment reference or shopping session is invalid.'];
        }

        $customerId = $customerId === null ? null : filter_var($customerId, FILTER_VALIDATE_INT);
        if ($customerId !== null && ($customerId === false || $customerId <= 0)) {
            return ['success' => false, 'error' => 'Your customer account is invalid.'];
        }

        $amount = filter_var($amount, FILTER_VALIDATE_INT);
        if ($amount === false || $amount <= 0 || $currency !== 'GHS') {
            return ['success' => false, 'error' => 'The verified payment amount or currency is invalid.'];
        }

        $shippingResult = $this->validateCheckoutDetails($shipping);
        if (!$shippingResult['success']) {
            return $shippingResult;
        }

        if (!is_array($cartSnapshot) || $cartSnapshot === []) {
            return ['success' => false, 'error' => 'Your checkout session expired. Please return to your cart and try again.'];
        }

        $items = [];
        $total = 0.0;
        foreach ($cartSnapshot as $item) {
            if (!is_array($item)) {
                return ['success' => false, 'error' => 'The checkout cart snapshot is invalid.'];
            }
            $productId = filter_var($item['product_id'] ?? null, FILTER_VALIDATE_INT);
            $quantity = filter_var($item['qty'] ?? null, FILTER_VALIDATE_INT);
            $unitPrice = $item['unit_price'] ?? null;
            if (
                $productId === false || $productId <= 0
                || $quantity === false || $quantity < 1
                || !is_numeric($unitPrice) || !is_finite((float) $unitPrice) || (float) $unitPrice < 0
            ) {
                return ['success' => false, 'error' => 'The checkout cart snapshot is invalid.'];
            }
            $unitPrice = (float) $unitPrice;
            $items[] = ['product_id' => $productId, 'qty' => $quantity, 'unit_price' => $unitPrice];
            $total += $quantity * $unitPrice;
        }

        if ((int) round($total * 100) !== $amount) {
            return ['success' => false, 'error' => 'The verified payment amount does not match your order total. Please contact support before retrying.'];
        }

        try {
            $this->conn->begin_transaction();

            $existingStmt = $this->conn->prepare("SELECT order_id FROM orders WHERE payment_reference = ? LIMIT 1");
            if (!$existingStmt) {
                throw new mysqli_sql_exception('Unable to prepare payment reference lookup: ' . $this->conn->error);
            }
            $existingStmt->bind_param("s", $reference);
            if (!$existingStmt->execute()) {
                throw new mysqli_sql_exception('Unable to look up payment reference: ' . $existingStmt->error);
            }
            $existingOrder = $existingStmt->get_result()->fetch_assoc();
            $existingStmt->close();

            if ($existingOrder) {
                $this->conn->commit();
                return [
                    'success' => true,
                    'order_id' => (int) $existingOrder['order_id'],
                    'already_processed' => true,
                ];
            }

            $stockStmt = $this->conn->prepare(
                "UPDATE products
                 SET stock_quantity = stock_quantity - ?
                 WHERE product_id = ? AND stock_quantity >= ?"
            );
            if (!$stockStmt) {
                throw new mysqli_sql_exception('Unable to prepare inventory deduction: ' . $this->conn->error);
            }
            foreach ($items as $item) {
                $stockStmt->bind_param("iii", $item['qty'], $item['product_id'], $item['qty']);
                if (!$stockStmt->execute()) {
                    throw new mysqli_sql_exception('Unable to deduct product inventory: ' . $stockStmt->error);
                }
                if ($stockStmt->affected_rows !== 1) {
                    $stockStmt->close();
                    $this->conn->rollback();
                    return [
                        'success' => false,
                        'error' => 'Payment was verified, but the available stock changed before the order could be completed. Do not pay again; contact support with your payment reference.',
                    ];
                }
            }
            $stockStmt->close();

            $invoiceNumber = null;
            $invoiceStmt = $this->conn->prepare("SELECT 1 FROM orders WHERE invoice_no = ? LIMIT 1");
            if (!$invoiceStmt) {
                throw new mysqli_sql_exception('Unable to prepare invoice number lookup: ' . $this->conn->error);
            }
            for ($attempt = 0; $attempt < 10; $attempt++) {
                $candidate = mt_rand(100000, 999999);
                $invoiceStmt->bind_param("i", $candidate);
                if (!$invoiceStmt->execute()) {
                    throw new mysqli_sql_exception('Unable to check invoice number: ' . $invoiceStmt->error);
                }
                if ($invoiceStmt->get_result()->num_rows === 0) {
                    $invoiceNumber = $candidate;
                    break;
                }
            }
            $invoiceStmt->close();
            if ($invoiceNumber === null) {
                throw new mysqli_sql_exception('Unable to generate a unique invoice number.');
            }

            $orderDate = date('Y-m-d');
            $createdAt = date('Y-m-d H:i:s');
            $status = 'paid';
            $currency = 'GHS';
            $shippingDetails = $shippingResult['details'];
            $orderStmt = $this->conn->prepare(
                "INSERT INTO orders
                 (customer_id, invoice_no, order_date, order_status, shipping_name, shipping_email,
                  shipping_contact, shipping_address, shipping_city, shipping_country, total_amount,
                  currency, payment_reference, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            if (!$orderStmt) {
                throw new mysqli_sql_exception('Unable to prepare order creation: ' . $this->conn->error);
            }
            $orderTypes = 'ii' . str_repeat('s', 8) . 'dsss';
            $orderStmt->bind_param(
                $orderTypes,
                $customerId,
                $invoiceNumber,
                $orderDate,
                $status,
                $shippingDetails['name'],
                $shippingDetails['email'],
                $shippingDetails['contact'],
                $shippingDetails['address'],
                $shippingDetails['city'],
                $shippingDetails['country'],
                $total,
                $currency,
                $reference,
                $createdAt
            );
            if (!$orderStmt->execute()) {
                throw new mysqli_sql_exception('Unable to create paid order: ' . $orderStmt->error);
            }
            $orderId = (int) $this->conn->insert_id;
            $orderStmt->close();

            $detailStmt = $this->conn->prepare(
                "INSERT INTO orderdetails (order_id, product_id, qty, unit_price) VALUES (?, ?, ?, ?)"
            );
            if (!$detailStmt) {
                throw new mysqli_sql_exception('Unable to prepare invoice items: ' . $this->conn->error);
            }
            foreach ($items as $item) {
                $detailStmt->bind_param("iiid", $orderId, $item['product_id'], $item['qty'], $item['unit_price']);
                if (!$detailStmt->execute()) {
                    throw new mysqli_sql_exception('Unable to save invoice items: ' . $detailStmt->error);
                }
            }
            $detailStmt->close();

            $paymentAmount = $amount / 100;
            $paymentDate = date('Y-m-d');
            $paymentStmt = $this->conn->prepare(
                "INSERT INTO payment (amt, customer_id, order_id, currency, payment_date, reference)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            if (!$paymentStmt) {
                throw new mysqli_sql_exception('Unable to prepare payment record: ' . $this->conn->error);
            }
            $paymentStmt->bind_param(
                "diisss",
                $paymentAmount,
                $customerId,
                $orderId,
                $currency,
                $paymentDate,
                $reference
            );
            if (!$paymentStmt->execute()) {
                throw new mysqli_sql_exception('Unable to record payment: ' . $paymentStmt->error);
            }
            $paymentStmt->close();

            $clearCartStmt = $this->conn->prepare("DELETE FROM cart WHERE p_id = ? AND ip_add = ? AND qty = ?");
            if (!$clearCartStmt) {
                throw new mysqli_sql_exception('Unable to prepare cart cleanup: ' . $this->conn->error);
            }
            foreach ($items as $item) {
                $clearCartStmt->bind_param("isi", $item['product_id'], $ip, $item['qty']);
                if (!$clearCartStmt->execute()) {
                    throw new mysqli_sql_exception('Unable to clear checked-out cart items: ' . $clearCartStmt->error);
                }
            }
            $clearCartStmt->close();

            $this->conn->commit();
            return ['success' => true, 'order_id' => $orderId, 'invoice_no' => $invoiceNumber];
        } catch (mysqli_sql_exception $exception) {
            $this->conn->rollback();
            error_log('Paystack order recording failed: ' . $exception->getMessage());
            return ['success' => false, 'error' => 'Payment was verified, but we could not finish recording your order. Contact support with your payment reference.'];
        }
    }

    public function getOrderById($orderId)
    {
        $orderId = filter_var($orderId, FILTER_VALIDATE_INT);
        if ($orderId === false || $orderId <= 0) {
            return null;
        }

        $stmt = $this->conn->prepare(
            "SELECT o.order_id, o.invoice_no, o.order_date, o.created_at, o.order_status,
                    o.shipping_name, o.shipping_email, o.shipping_contact, o.shipping_address,
                    o.shipping_city, o.shipping_country, o.total_amount, o.currency, o.payment_reference,
                    od.product_id, od.qty, od.unit_price, p.product_title, p.product_image
             FROM orders o
             INNER JOIN orderdetails od ON od.order_id = o.order_id
             INNER JOIN products p ON p.product_id = od.product_id
             WHERE o.order_id = ?
             ORDER BY p.product_title ASC"
        );
        if (!$stmt) {
            error_log('Failed to prepare invoice lookup: ' . $this->conn->error);
            return null;
        }
        $stmt->bind_param("i", $orderId);
        if (!$stmt->execute()) {
            error_log('Failed to load invoice: ' . $stmt->error);
            $stmt->close();
            return null;
        }

        $result = $stmt->get_result();
        $order = null;
        while ($row = $result->fetch_assoc()) {
            if ($order === null) {
                $order = [
                    'order_id' => $row['order_id'],
                    'invoice_no' => $row['invoice_no'],
                    'order_date' => $row['order_date'],
                    'created_at' => $row['created_at'],
                    'order_status' => $row['order_status'],
                    'shipping_name' => $row['shipping_name'],
                    'shipping_email' => $row['shipping_email'],
                    'shipping_contact' => $row['shipping_contact'],
                    'shipping_address' => $row['shipping_address'],
                    'shipping_city' => $row['shipping_city'],
                    'shipping_country' => $row['shipping_country'],
                    'total_amount' => $row['total_amount'],
                    'currency' => $row['currency'],
                    'payment_reference' => $row['payment_reference'],
                    'items' => [],
                ];
            }
            $order['items'][] = [
                'product_id' => $row['product_id'],
                'qty' => $row['qty'],
                'unit_price' => $row['unit_price'],
                'product_title' => $row['product_title'],
                'product_image' => $row['product_image'],
            ];
        }
        $stmt->close();

        return $order;
    }

    public function removeFromCart($productId, $ip)
    {
        $productId = filter_var($productId, FILTER_VALIDATE_INT);
        if ($productId === false || $productId <= 0 || !$this->isValidIp($ip)) {
            return ['success' => false, 'error' => 'Invalid cart item.'];
        }

        $stmt = $this->conn->prepare("DELETE FROM cart WHERE p_id = ? AND ip_add = ?");
        if (!$stmt) {
            error_log('Failed to prepare cart removal: ' . $this->conn->error);
            return ['success' => false, 'error' => 'Unable to remove this item right now.'];
        }
        $stmt->bind_param("is", $productId, $ip);
        if (!$stmt->execute()) {
            error_log('Failed to remove cart item: ' . $stmt->error);
            $stmt->close();
            return ['success' => false, 'error' => 'Unable to remove this item right now.'];
        }
        $removed = $stmt->affected_rows > 0;
        $stmt->close();
        return $removed
            ? ['success' => true, 'message' => 'Selected item(s) removed from your cart.']
            : ['success' => false, 'error' => 'The selected item was not found in your cart.'];
    }

    public function updateQty($productId, $newQuantity, $ip)
    {
        $productId = filter_var($productId, FILTER_VALIDATE_INT);
        $newQuantity = filter_var($newQuantity, FILTER_VALIDATE_INT);
        if ($productId === false || $productId <= 0 || $newQuantity === false || $newQuantity < 1) {
            return ['success' => false, 'error' => 'Enter a quantity of at least 1.'];
        }
        if (!$this->isValidIp($ip)) {
            return ['success' => false, 'error' => 'Unable to identify this shopping session.'];
        }

        $stockStmt = $this->conn->prepare("SELECT stock_quantity FROM products WHERE product_id = ? LIMIT 1");
        if (!$stockStmt) {
            error_log('Failed to prepare stock lookup for cart quantity update: ' . $this->conn->error);
            return ['success' => false, 'error' => 'Unable to check product availability right now.'];
        }
        $stockStmt->bind_param("i", $productId);
        if (!$stockStmt->execute()) {
            error_log('Failed to check stock for cart quantity update: ' . $stockStmt->error);
            $stockStmt->close();
            return ['success' => false, 'error' => 'Unable to check product availability right now.'];
        }
        $product = $stockStmt->get_result()->fetch_assoc();
        $stockStmt->close();
        if (!$product || $newQuantity > (int) $product['stock_quantity']) {
            return ['success' => false, 'error' => 'The requested quantity exceeds the available stock.'];
        }

        $stmt = $this->conn->prepare("UPDATE cart SET qty = ? WHERE p_id = ? AND ip_add = ?");
        if (!$stmt) {
            error_log('Failed to prepare cart quantity update: ' . $this->conn->error);
            return ['success' => false, 'error' => 'Unable to update cart quantities right now.'];
        }
        $stmt->bind_param("iis", $newQuantity, $productId, $ip);
        if (!$stmt->execute()) {
            error_log('Failed to update cart quantity: ' . $stmt->error);
            $stmt->close();
            return ['success' => false, 'error' => 'Unable to update cart quantities right now.'];
        }
        $stmt->close();
        return ['success' => true, 'message' => 'Cart quantity updated.'];
    }

    private function isValidIp($ip)
    {
        return is_string($ip) && $ip !== '' && strlen($ip) <= 50;
    }
}

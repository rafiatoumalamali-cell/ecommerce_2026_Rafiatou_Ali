ALTER TABLE orders
    ADD COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER order_date,
    ADD COLUMN currency CHAR(3) NOT NULL DEFAULT 'XOF' AFTER total_amount,
    ADD COLUMN payment_reference VARCHAR(100) DEFAULT NULL AFTER currency,
    ADD UNIQUE KEY uq_orders_payment_reference (payment_reference);

ALTER TABLE payment
    MODIFY COLUMN customer_id INT(11) DEFAULT NULL,
    ADD COLUMN reference VARCHAR(100) DEFAULT NULL AFTER currency,
    ADD UNIQUE KEY uq_payment_reference (reference);

ALTER TABLE products
    MODIFY COLUMN product_price DECIMAL(12,2) NOT NULL;

UPDATE products
SET product_price = ROUND(product_price * 0.020, 2);

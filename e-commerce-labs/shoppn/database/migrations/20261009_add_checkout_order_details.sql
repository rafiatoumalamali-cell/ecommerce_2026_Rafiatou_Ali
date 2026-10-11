ALTER TABLE orders
    DROP FOREIGN KEY orders_ibfk_1,
    MODIFY COLUMN customer_id INT(11) DEFAULT NULL,
    ADD COLUMN shipping_name VARCHAR(100) NOT NULL DEFAULT '' AFTER order_status,
    ADD COLUMN shipping_email VARCHAR(100) NOT NULL DEFAULT '' AFTER shipping_name,
    ADD COLUMN shipping_contact VARCHAR(30) NOT NULL DEFAULT '' AFTER shipping_email,
    ADD COLUMN shipping_address VARCHAR(255) NOT NULL DEFAULT '' AFTER shipping_contact,
    ADD COLUMN shipping_city VARCHAR(100) NOT NULL DEFAULT '' AFTER shipping_address,
    ADD COLUMN shipping_country VARCHAR(100) NOT NULL DEFAULT '' AFTER shipping_city,
    ADD COLUMN total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER shipping_country,
    ADD CONSTRAINT orders_ibfk_1
        FOREIGN KEY (customer_id) REFERENCES customer (customer_id);

ALTER TABLE orderdetails
    ADD COLUMN unit_price DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER qty;

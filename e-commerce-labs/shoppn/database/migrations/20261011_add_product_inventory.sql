ALTER TABLE products
    ADD COLUMN stock_quantity INT UNSIGNED NOT NULL DEFAULT 0 AFTER product_keywords;

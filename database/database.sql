-- ========================================
-- PHONE SHOP MANAGEMENT SYSTEM DATABASE
-- ========================================

-- Create database
CREATE DATABASE IF NOT EXISTS phone_shop_db;
USE phone_shop_db;

-- ========================================
-- 1. USERS TABLE
-- ========================================
CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    role ENUM('admin') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- 2. PRODUCTS TABLE
-- ========================================
CREATE TABLE IF NOT EXISTS products (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(200) NOT NULL,
    category ENUM('Phone', 'Accessories') NOT NULL,
    brand VARCHAR(100) NOT NULL,
    buying_price DECIMAL(10,2) NOT NULL,
    selling_price DECIMAL(10,2) NOT NULL,
    quantity_in_stock INT(11) NOT NULL DEFAULT 0,
    supplier_name VARCHAR(150),
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive') DEFAULT 'active',
    INDEX idx_category (category),
    INDEX idx_brand (brand),
    INDEX idx_stock (quantity_in_stock)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- 3. SALES TABLE
-- ========================================
CREATE TABLE IF NOT EXISTS sales (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2) NOT NULL,
    total_cost DECIMAL(10,2) NOT NULL,
    profit DECIMAL(10,2) NOT NULL,
    payment_method ENUM('Cash', 'Card', 'Mobile Money') DEFAULT 'Cash',
    customer_name VARCHAR(100),
    user_id INT(11),
    INDEX idx_sale_date (sale_date),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- 4. SALE_ITEMS TABLE
-- ========================================
CREATE TABLE IF NOT EXISTS sale_items (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    sale_id INT(11) NOT NULL,
    product_id INT(11) NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    quantity INT(11) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    buying_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    profit DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    INDEX idx_sale_id (sale_id),
    INDEX idx_product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- 5. INSERT DEFAULT ADMIN USER
-- Password: admin123 (hashed using PHP password_hash)
-- ========================================
INSERT INTO users (username, password, full_name, email, role) VALUES
('admin', '$2y$10$YIjlrJZrn.WZLVKLIKdW3.TjqkMlmFp0Lm1zcqJ0Kpx9XM8WpNJDu', 'System Administrator', 'admin@phoneshop.com', 'admin');

-- ========================================
-- 6. INSERT SAMPLE PRODUCTS (Optional)
-- ========================================
INSERT INTO products (product_name, category, brand, buying_price, selling_price, quantity_in_stock, supplier_name) VALUES
('iPhone 15 Pro Max', 'Phone', 'Apple', 1200.00, 1500.00, 15, 'Tech Suppliers Ltd'),
('Samsung Galaxy S24 Ultra', 'Phone', 'Samsung', 1000.00, 1300.00, 20, 'Tech Suppliers Ltd'),
('AirPods Pro 2', 'Accessories', 'Apple', 200.00, 280.00, 30, 'Audio World'),
('Samsung Fast Charger', 'Accessories', 'Samsung', 15.00, 25.00, 50, 'Accessory Hub'),
('Phone Case Universal', 'Accessories', 'Generic', 3.00, 8.00, 100, 'Accessory Hub'),
('Screen Protector Tempered Glass', 'Accessories', 'Generic', 2.00, 7.00, 150, 'Accessory Hub'),
('Xiaomi Redmi Note 13', 'Phone', 'Xiaomi', 250.00, 320.00, 25, 'Tech Suppliers Ltd'),
('USB-C Cable', 'Accessories', 'Generic', 5.00, 12.00, 80, 'Accessory Hub'),
('Wireless Earbuds', 'Accessories', 'JBL', 40.00, 65.00, 40, 'Audio World'),
('Power Bank 20000mAh', 'Accessories', 'Anker', 30.00, 50.00, 35, 'Power Solutions');

-- ========================================
-- 7. CREATE VIEWS FOR REPORTS
-- ========================================

-- View for low stock products (less than 10 items)
CREATE OR REPLACE VIEW low_stock_products AS
SELECT id, product_name, category, brand, quantity_in_stock, selling_price
FROM products
WHERE quantity_in_stock < 10 AND status = 'active'
ORDER BY quantity_in_stock ASC;

-- View for daily sales summary
CREATE OR REPLACE VIEW daily_sales_summary AS
SELECT 
    DATE(sale_date) as date,
    COUNT(*) as total_sales,
    SUM(total_amount) as total_revenue,
    SUM(profit) as total_profit
FROM sales
GROUP BY DATE(sale_date)
ORDER BY date DESC;

-- View for best selling products
CREATE OR REPLACE VIEW best_selling_products AS
SELECT 
    p.id,
    p.product_name,
    p.category,
    p.brand,
    SUM(si.quantity) as total_sold,
    SUM(si.subtotal) as total_revenue,
    SUM(si.profit) as total_profit
FROM products p
INNER JOIN sale_items si ON p.id = si.product_id
GROUP BY p.id, p.product_name, p.category, p.brand
ORDER BY total_sold DESC;

-- ========================================
-- DATABASE SETUP COMPLETE
-- ========================================
-- THIS FILE CONTAINS SQL QUERIES THAT CREATE, DATABASE, USE IT, AND ALSO CREATE TABLES IN IT IF THEY DON'T EXIT

CREATE DATABASE IF NOT EXISTS `addaduma-wholesale_system`;
USE `addaduma-wholesale_system`;

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO categories (name) VALUES
('Beverages'), ('Achoholic'), ('Water'), ('Soft Drinks');


CREATE TABLE suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO suppliers (name, phone, address) VALUES
('Coca-Cola Ghana', '0302-123456', 'Accra Industrial Area'),
('Unilever Ghana', '0302-234567', 'Tema Free Zone'),
('Nestle Ghana', '0302-345678', 'Tema'),
('Local Distributors Ltd', '0302-456789', 'Kumasi'),
('Fresh Foods Co', '0302-567890', 'Tamale');


CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    category_id INT NOT NULL,
    supplier_id INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    quantity INT NOT NULL DEFAULT 0,
    image_path VARCHAR(255),
    low_stock_threshold INT DEFAULT 10,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE
);

INSERT INTO products (name, category_id, supplier_id, unit_price, quantity, low_stock_threshold) VALUES
('Coca-Cola 500ml', 1, 1, 3.50, 8, 15),
('Fanta Orange 500ml', 1, 1, 3.50, 45, 15),
('Sprite 500ml', 1, 1, 3.50, 120, 15),
('Omo Detergent 1kg', 3, 2, 25.00, 60, 10),
('Close Up Toothpaste', 4, 2, 8.50, 5, 10),
('Maggi Cubes Pack', 2, 3, 12.00, 200, 20),
('Milo 400g', 1, 3, 35.00, 75, 15),
('Vita Milk 1L', 1, 3, 18.00, 90, 20),
('Indomie Noodles', 2, 4, 4.50, 150, 25),
('Peak Milk 170g', 1, 3, 6.00, 12, 15);

CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
INSERT INTO customers (name, phone, email, address) VALUES
('Kwame Mensah', '0244-111111', 'kwame@email.com', 'Accra'),
('Ama Osei', '0244-222222', 'ama@email.com', 'Tema'),
('Kofi Asante', '0244-333333', 'kofi@email.com', 'Kumasi'),
('Abena Darko', '0244-444444', 'abena@email.com', 'Tamale'),
('Yaw Boateng', '0244-555555', 'yaw@email.com', 'Sunyani');


CREATE TABLE staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    role ENUM('Administrator', 'Manager', 'Sales') NOT NULL DEFAULT 'Sales',
    email VARCHAR(100),
    password_hash VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);


INSERT INTO staff (name, phone, role, email, password_hash) VALUES
('GroupAdmin', '0531691093', 'Administrator', 'admin@addaduma.com', 'admin123');


CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    order_date DATE NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    status ENUM('Pending', 'Paid', 'Cancelled', 'Delivered') NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

INSERT INTO orders (customer_id, order_date, total_amount, status) VALUES
(1, '2026-07-01', 450.00, 'Paid'),
(2, '2026-07-02', 320.50, 'Paid'),
(3, '2026-07-03', 175.00, 'Pending'),
(4, '2026-07-03', 890.00, 'Paid'),
(5, '2026-07-04', 210.00, 'Pending'),
(2, '2026-06-28', 560.00, 'Paid'),
(4, '2026-06-29', 125.00, 'Delivered'),
(1, '2026-06-30', 780.00, 'Paid');



-- JUNCTION TABLE
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal) VALUES
(1, 1, 50, 3.50, 175.00),
(2, 3, 50, 3.50, 175.00),
(4, 6, 10, 12.00, 120.00),
(5, 4, 8, 25.00, 200.00),
(6, 5, 14, 8.50, 119.00),
(7, 9, 30, 4.50, 135.00),
(8, 7, 20, 35.00, 700.00);



CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    payment_date DATE NOT NULL,
    method ENUM('Cash', 'Mobile Money', 'Bank Transfer', 'Cheque') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    reference_number VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

INSERT INTO payments (order_id, payment_date, method, amount) VALUES
(1, '2026-07-01', 'Mobile Money', 450.00),
(2, '2026-07-02', 'Cash', 320.50),
(4, '2026-07-03', 'Bank Transfer', 890.00),
(6, '2026-06-28', 'Cash', 560.00),
(7, '2026-06-29', 'Mobile Money', 125.00),
(8, '2026-06-30', 'Bank Transfer', 780.00);



CREATE TABLE invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL UNIQUE,
    invoice_date DATE NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

INSERT INTO invoices (order_id, invoice_date, total_amount) VALUES
(1, '2026-07-01', 450.00),
(2, '2026-07-02', 320.50),
(3, '2026-07-03', 890.00),
(4, '2026-06-28', 560.00),
(5, '2026-06-29', 125.00),
(6, '2026-06-30', 780.00);



CREATE TABLE inventory_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    change_amount INT NOT NULL,
    log_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    type ENUM('Stock In', 'Stock Out', 'Adjustment', 'Sale', 'Return') NOT NULL,
    notes TEXT,
    staff_id INT,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE SET NULL
);

INSERT INTO inventory_logs (product_id, change_amount, type, notes, staff_id) VALUES
(1, -50, 'Sale', 'Order #1', 1),
(3, -50, 'Sale', 'Order #1', 1),
(4, 100, 'Stock In', 'Monthly restock', 1),
(5, -14, 'Sale', 'Order #2', 1),
(9, 200, 'Stock In', 'Bulk delivery', 1);























































































































-- Indexes for performance
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_supplier ON products(supplier_id);
CREATE INDEX idx_orders_customer ON orders(customer_id);
CREATE INDEX idx_orders_date ON orders(order_date);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_order_items_order ON order_items(order_id);
CREATE INDEX idx_payments_order ON payments(order_id);
CREATE INDEX idx_inventory_logs_product ON inventory_logs(product_id);

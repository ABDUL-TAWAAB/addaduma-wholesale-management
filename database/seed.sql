-- -- Sample seed data for development and testing
-- USE `addaduma-wholesale_system`;

-- INSERT INTO categories (name) VALUES
-- ('Beverages'), ('Snacks'), ('Household'), ('Personal Care'), ('Frozen Foods');

-- INSERT INTO suppliers (name, phone, address) VALUES
-- ('Coca-Cola Ghana', '0302-123456', 'Accra Industrial Area'),
-- ('Unilever Ghana', '0302-234567', 'Tema Free Zone'),
-- ('Nestle Ghana', '0302-345678', 'Tema'),
-- ('Local Distributors Ltd', '0302-456789', 'Kumasi'),
-- ('Fresh Foods Co', '0302-567890', 'Tamale');

-- INSERT INTO products (name, category_id, supplier_id, unit_price, quantity, low_stock_threshold) VALUES
-- ('Coca-Cola 500ml', 1, 1, 3.50, 8, 15),
-- ('Fanta Orange 500ml', 1, 1, 3.50, 45, 15),
-- ('Sprite 500ml', 1, 1, 3.50, 120, 15),
-- ('Omo Detergent 1kg', 3, 2, 25.00, 60, 10),
-- ('Close Up Toothpaste', 4, 2, 8.50, 5, 10),
-- ('Maggi Cubes Pack', 2, 3, 12.00, 200, 20),
-- ('Milo 400g', 1, 3, 35.00, 75, 15),
-- ('Vita Milk 1L', 1, 3, 18.00, 90, 20),
-- ('Indomie Noodles', 2, 4, 4.50, 150, 25),
-- ('Peak Milk 170g', 1, 3, 6.00, 12, 15);

-- INSERT INTO customers (name, phone, email) VALUES
-- ('Kwame Mensah', '0244-111111', 'kwame@email.com'),
-- ('Ama Osei', '0244-222222', 'ama@email.com'),
-- ('Kofi Asante', '0244-333333', 'kofi@email.com'),
-- ('Abena Darko', '0244-444444', 'abena@email.com'),
-- ('Yaw Boateng', '0244-555555', 'yaw@email.com'),
-- ('Efua Addo', '0244-666666', 'efua@email.com'),
-- ('Nana Agyeman', '0244-777777', 'nana@email.com'),
-- ('Akua Frimpong', '0244-888888', 'akua@email.com');

-- INSERT INTO staff (name, phone, role, email) VALUES
-- ('Admin User', '0302-000001', 'Administrator', 'admin@addaduma.com'),
-- ('Grace Adom', '0302-000002', 'Manager', 'grace@addaduma.com'),
-- ('Samuel Tetteh', '0302-000003', 'Sales', 'samuel@addaduma.com'),
-- ('Mary Ofori', '0302-000004', 'Inventory', 'mary@addaduma.com'),
-- ('Daniel Kusi', '0302-000005', 'Cashier', 'daniel@addaduma.com');

-- INSERT INTO orders (customer_id, order_date, total_amount, status) VALUES
-- (1, '2026-07-01', 450.00, 'Paid'),
-- (2, '2026-07-02', 320.50, 'Paid'),
-- (3, '2026-07-03', 175.00, 'Pending'),
-- (4, '2026-07-03', 890.00, 'Paid'),
-- (5, '2026-07-04', 210.00, 'Pending'),
-- (6, '2026-06-28', 560.00, 'Paid'),
-- (7, '2026-06-29', 125.00, 'Delivered'),
-- (8, '2026-06-30', 780.00, 'Paid');

-- INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal) VALUES
-- (1, 1, 50, 3.50, 175.00),
-- (1, 3, 50, 3.50, 175.00),
-- (1, 6, 10, 12.00, 120.00),
-- (2, 4, 8, 25.00, 200.00),
-- (2, 5, 14, 8.50, 119.00),
-- (3, 9, 30, 4.50, 135.00),
-- (4, 7, 20, 35.00, 700.00),
-- (4, 8, 10, 18.00, 180.00);

-- INSERT INTO payments (order_id, payment_date, method, amount) VALUES
-- (1, '2026-07-01', 'Mobile Money', 450.00),
-- (2, '2026-07-02', 'Cash', 320.50),
-- (4, '2026-07-03', 'Bank Transfer', 890.00),
-- (6, '2026-06-28', 'Cash', 560.00),
-- (7, '2026-06-29', 'Mobile Money', 125.00),
-- (8, '2026-06-30', 'Bank Transfer', 780.00);

-- INSERT INTO invoices (order_id, invoice_date, total_amount) VALUES
-- (1, '2026-07-01', 450.00),
-- (2, '2026-07-02', 320.50),
-- (4, '2026-07-03', 890.00),
-- (6, '2026-06-28', 560.00),
-- (7, '2026-06-29', 125.00),
-- (8, '2026-06-30', 780.00);

-- INSERT INTO inventory_logs (product_id, change_amount, type, notes, staff_id) VALUES
-- (1, -50, 'Sale', 'Order #1', 3),
-- (3, -50, 'Sale', 'Order #1', 3),
-- (4, 100, 'Stock In', 'Monthly restock', 4),
-- (5, -14, 'Sale', 'Order #2', 3),
-- (9, 200, 'Stock In', 'Bulk delivery', 4);

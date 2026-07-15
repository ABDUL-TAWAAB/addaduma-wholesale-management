<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

try {
    $db = getDB();
} catch (RuntimeException $e) {
    jsonError('Database connection failed: ' . $e->getMessage(), 500);
}

$method = getRequestMethod();
$segments = getPathSegments();
$resource = $segments[0] ?? '';
$id = isset($segments[1]) && is_numeric($segments[1]) ? (int) $segments[1] : null;
$sub = $segments[1] ?? null;

try {
    if ($resource === 'health') {
        handleHealth($db);
    } elseif ($resource === 'dashboard') {
        handleDashboard($db, $method, $sub);
    } elseif ($resource === 'reports') {
        handleReports($db, $method, $sub);
    } elseif ($resource === 'inventory') {
        handleInventory($db, $method, $id);
    } elseif ($resource === 'categories') {
        handleCrud($db, $method, $id, 'categories', ['name']);
    } elseif ($resource === 'suppliers') {
        handleCrud($db, $method, $id, 'suppliers', ['name', 'phone', 'address']);
    } elseif ($resource === 'customers') {
        handleCrud($db, $method, $id, 'customers', ['name', 'phone', 'email', 'address']);
    } elseif ($resource === 'staff') {
        handleStaff($db, $method, $id);
    } elseif ($resource === 'products') {
        handleProducts($db, $method, $id);
    } elseif ($resource === 'orders') {
        handleOrders($db, $method, $id);
    } elseif ($resource === 'payments') {
        handlePayments($db, $method, $id);
    } elseif ($resource === 'invoices') {
        handleInvoices($db, $method, $id);
    } else {
        jsonError('Resource not found', 404);
    }
} catch (RuntimeException $e) {
    $message = $e->getMessage();

    if (isForeignKeyError($message)) {
        jsonError('This record is linked to other data and cannot be removed.', 409);
    }

    jsonError('Database error: ' . $message, 500);
}

function isForeignKeyError(string $message): bool
{
    $keywords = [
        'foreign key constraint fails',
        'Cannot delete or update a parent row',
        '1451',
        '1452',
    ];

    foreach ($keywords as $keyword) {
        if (str_contains($message, $keyword)) {
            return true;
        }
    }

    return false;
}

function handleHealth($db): void{
    db_query($db, 'SELECT 1');
    jsonResponse([
        'status' => 'ok',
        'database' => 'connected',
    ]);
}

function handleDashboard($db, string $method, ?string $sub): void
{
    if ($method !== 'GET' || $sub !== 'stats') {
        jsonError('Not found', 404);
    }

    $totalProducts = (int) db_fetch_column(db_query($db, 'SELECT COUNT(*) FROM products'));
    $totalOrders = (int) db_fetch_column(db_query($db, 'SELECT COUNT(*) FROM orders'));
    $totalCustomers = (int) db_fetch_column(db_query($db, 'SELECT COUNT(*) FROM customers'));
    $totalSales = (float) db_fetch_column(db_query($db, "SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status IN ('Paid', 'Delivered')"));

    $lowStockRows = db_fetch_all(db_query($db, '
        SELECT id, name, quantity, low_stock_threshold
        FROM products
        WHERE quantity <= low_stock_threshold
        ORDER BY quantity ASC
        LIMIT 10
    '));

    $recentOrderRows = db_fetch_all(db_query($db, '
        SELECT o.id, o.customer_id, o.order_date, o.total_amount, o.status, c.name AS customer_name
        FROM orders o
        JOIN customers c ON o.customer_id = c.id
        ORDER BY o.order_date DESC, o.id DESC
        LIMIT 5
    '));

    $topProductRows = db_fetch_all(db_query($db, '
        SELECT oi.product_id, p.name, SUM(oi.quantity) AS quantity
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        GROUP BY oi.product_id, p.name
        ORDER BY quantity DESC
        LIMIT 5
    '));

    $salesRows = db_fetch_all(db_query($db, "
        SELECT DAY(order_date) AS day, SUM(total_amount) AS total
        FROM orders
        WHERE status IN ('Paid', 'Delivered')
          AND MONTH(order_date) = MONTH(CURRENT_DATE())
          AND YEAR(order_date) = YEAR(CURRENT_DATE())
        GROUP BY DAY(order_date)
    "));

    $salesByDay = [];
    foreach ($salesRows as $row) {
        $day = str_pad($row['day'], 2, '0', STR_PAD_LEFT);
        $salesByDay[$day] = (float) $row['total'];
    }

    jsonResponse([
        'totalProducts' => $totalProducts,
        'totalOrders' => $totalOrders,
        'totalCustomers' => $totalCustomers,
        'totalSales' => $totalSales,
        'lowStock' => formatRows($lowStockRows, ['id', 'quantity', 'low_stock_threshold']),
        'recentOrders' => formatRows($recentOrderRows, ['id', 'customer_id', 'total_amount']),
        'topProducts' => formatRows($topProductRows, ['product_id', 'quantity']),
        'salesByDay' => $salesByDay,
    ]);
}

function handleReports($db, string $method, ?string $type): void
{
    if ($method !== 'GET' || empty($type)) {
        jsonError('Not found', 404);
    }

    $data = null;

    if ($type === 'daily-sales') {
        $data = db_fetch_all(db_query($db, "
            SELECT order_date AS date, COUNT(*) AS orders, SUM(total_amount) AS total
            FROM orders
            WHERE status IN ('Paid', 'Delivered')
            GROUP BY order_date
            ORDER BY order_date DESC
            LIMIT 30
        "));
    } elseif ($type === 'monthly-sales') {
        $data = db_fetch_all(db_query($db, "
            SELECT DATE_FORMAT(order_date, '%Y-%m') AS month, COUNT(*) AS orders, SUM(total_amount) AS total
            FROM orders
            WHERE status IN ('Paid', 'Delivered')
            GROUP BY month
            ORDER BY month DESC
            LIMIT 12
        "));
    } elseif ($type === 'inventory') {
        $data = db_fetch_all(db_query($db, '
            SELECT p.id, p.name, p.quantity, p.low_stock_threshold, c.name AS category_name
            FROM products p
            JOIN categories c ON p.category_id = c.id
            ORDER BY p.quantity ASC
        '));
    } elseif ($type === 'customer') {
        $data = db_fetch_all(db_query($db, '
            SELECT c.id, c.name, c.phone, COUNT(o.id) AS order_count, COALESCE(SUM(o.total_amount), 0) AS total_spent
            FROM customers c
            LEFT JOIN orders o ON c.id = o.customer_id
            GROUP BY c.id, c.name, c.phone
            ORDER BY total_spent DESC
        '));
    } elseif ($type === 'payment') {
        $data = db_fetch_all(db_query($db, '
            SELECT method, COUNT(*) AS count, SUM(amount) AS total
            FROM payments
            GROUP BY method
        '));
    } elseif ($type === 'supplier') {
        $data = db_fetch_all(db_query($db, '
            SELECT s.id, s.name, COUNT(p.id) AS product_count
            FROM suppliers s
            LEFT JOIN products p ON s.id = p.supplier_id
            GROUP BY s.id, s.name
            ORDER BY product_count DESC
        '));
    }

    if ($data === null) {
        jsonError('Unknown report type', 404);
    }

    $title = ucfirst(str_replace('-', ' ', $type));
    jsonResponse([
        'message' => $title . ' report generated successfully',
        'data' => $data,
    ]);
}

function handleInventory($db, string $method, ?int $productId): void
{
    if ($method !== 'PUT' || empty($productId)) {
        jsonError('Not found', 404);
    }

    $body = getJsonBody();
    $newQuantity = isset($body['quantity']) ? (int) $body['quantity'] : null;
    $notes = $body['notes'] ?? '';

    if ($newQuantity === null) {
        jsonError('Quantity is required');
    }

    $stmt = db_prepare($db, 'SELECT id, quantity FROM products WHERE id = ?');
    db_execute($stmt, [$productId]);
    $product = db_fetch_assoc($stmt);

    if (!$product) {
        jsonError('Product not found', 404);
    }

    $changeAmount = $newQuantity - (int) $product['quantity'];
    $changeType = $changeAmount >= 0 ? 'Stock In' : 'Stock Out';

    $updateStmt = db_prepare($db, 'UPDATE products SET quantity = ? WHERE id = ?');
    db_execute($updateStmt, [$newQuantity, $productId]);

    $insertStmt = db_prepare($db, '
        INSERT INTO inventory_logs (product_id, change_amount, type, notes)
        VALUES (?, ?, ?, ?)
    ');
    db_execute($insertStmt, [$productId, $changeAmount, $changeType, $notes]);

    $updatedStmt = db_prepare($db, '
        SELECT p.*, c.name AS category_name, s.name AS supplier_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        JOIN suppliers s ON p.supplier_id = s.id
        WHERE p.id = ?
    ');
    db_execute($updatedStmt, [$productId]);
    $row = db_fetch_assoc($updatedStmt);

    jsonResponse(formatRow($row, ['id', 'category_id', 'supplier_id', 'unit_price', 'quantity', 'low_stock_threshold']));
}

function handleProducts($db, string $method, ?int $id): void
{
    if ($method === 'GET') {
        if ($id) {
            $stmt = db_prepare($db, '
                SELECT p.*, c.name AS category_name, s.name AS supplier_name
                FROM products p
                JOIN categories c ON p.category_id = c.id
                JOIN suppliers s ON p.supplier_id = s.id
                WHERE p.id = ?
            ');
            db_execute($stmt, [$id]);
            $row = db_fetch_assoc($stmt);

            if (!$row) {
                jsonError('Product not found', 404);
            }

            jsonResponse(formatRow($row, ['id', 'category_id', 'supplier_id', 'unit_price', 'quantity', 'low_stock_threshold']));
        }

        $rows = db_fetch_all(db_query($db, '
            SELECT p.*, c.name AS category_name, s.name AS supplier_name
            FROM products p
            JOIN categories c ON p.category_id = c.id
            JOIN suppliers s ON p.supplier_id = s.id
            ORDER BY p.id ASC
        '));

        jsonResponse(formatRows($rows, ['id', 'category_id', 'supplier_id', 'unit_price', 'quantity', 'low_stock_threshold']));
        return;
    }

    $fields = ['name', 'category_id', 'supplier_id', 'unit_price', 'quantity', 'low_stock_threshold'];

    if ($method === 'POST') {
        $body = getJsonBody();

        $stmt = db_prepare($db, 'INSERT INTO products (name, category_id, supplier_id, unit_price, quantity, low_stock_threshold) VALUES (?, ?, ?, ?, ?, ?)');
        db_execute($stmt, [
            $body['name'] ?? null,
            (int) ($body['category_id'] ?? 0),
            (int) ($body['supplier_id'] ?? 0),
            (float) ($body['unit_price'] ?? 0),
            (int) ($body['quantity'] ?? 0),
            (int) ($body['low_stock_threshold'] ?? 10),
        ]);

        handleProducts($db, 'GET', (int) db_last_insert_id($db));
        return;
    }

    if ($method === 'PUT' && $id) {
        $body = getJsonBody();
        $updateParts = buildUpdateParts($body, $fields, ['category_id', 'supplier_id', 'quantity', 'low_stock_threshold'], ['unit_price']);

        if ($updateParts['setClauses'] === []) {
            jsonError('No fields to update');
        }

        $values = $updateParts['values'];
        $values[] = $id;

        $stmt = db_prepare($db, 'UPDATE products SET ' . implode(', ', $updateParts['setClauses']) . ' WHERE id = ?');
        db_execute($stmt, $values);

        handleProducts($db, 'GET', $id);
        return;
    }

    if ($method === 'DELETE' && $id) {
        $stmt = db_prepare($db, 'DELETE FROM products WHERE id = ?');
        db_execute($stmt, [$id]);
        jsonResponse(['success' => true]);
        return;
    }

    jsonError('Method not allowed', 405);
}

function handleOrders($db, string $method, ?int $id): void
{
    if ($method === 'GET') {
        if ($id) {
            $stmt = db_prepare($db, '
                SELECT o.*, c.name AS customer_name
                FROM orders o
                JOIN customers c ON o.customer_id = c.id
                WHERE o.id = ?
            ');
            db_execute($stmt, [$id]);
            $order = db_fetch_assoc($stmt);

            if (!$order) {
                jsonError('Order not found', 404);
            }

            $itemsStmt = db_prepare($db, '
                SELECT oi.*, p.name AS product_name
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?
            ');
            db_execute($itemsStmt, [$id]);
            $order['items'] = db_fetch_all($itemsStmt);
            $order = formatRow($order, ['id', 'customer_id', 'total_amount']);
            jsonResponse($order);
        }

        $rows = db_fetch_all(db_query($db, '
            SELECT o.*, c.name AS customer_name
            FROM orders o
            JOIN customers c ON o.customer_id = c.id
            ORDER BY o.order_date DESC, o.id DESC
        '));

        jsonResponse(formatRows($rows, ['id', 'customer_id', 'total_amount']));
        return;
    }

    if ($method === 'POST') {
        $body = getJsonBody();

        $lineItems = [];
        if (isset($body['items']) && is_array($body['items']) && $body['items'] !== []) {
            $lineItems = $body['items'];
        } else {
            $productId = isset($body['product_id']) ? (int) $body['product_id'] : 0;
            $quantity = isset($body['quantity']) ? (int) $body['quantity'] : 0;
            $unitPrice = isset($body['unit_price']) ? (float) $body['unit_price'] : 0.0;

            if (!$productId || $quantity <= 0) {
                jsonError('Product and quantity are required', 400);
            }

            $lineItems[] = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
            ];
        }

        if (!mysqli_begin_transaction($db)) {
            throw new RuntimeException('Failed to start database transaction');
        }

        try {
            $preparedItems = [];
            $orderTotal = 0.0;

            foreach ($lineItems as $item) {
                $productId = isset($item['product_id']) ? (int) $item['product_id'] : 0;
                $quantity = isset($item['quantity']) ? (int) $item['quantity'] : 0;
                $unitPrice = isset($item['unit_price']) ? (float) $item['unit_price'] : 0.0;

                if (!$productId || $quantity <= 0) {
                    throw new RuntimeException('Each order item must include a product and quantity');
                }

                $productStmt = db_prepare($db, 'SELECT id, quantity, unit_price FROM products WHERE id = ?');
                db_execute($productStmt, [$productId]);
                $product = db_fetch_assoc($productStmt);

                if (!$product) {
                    throw new RuntimeException('Product not found');
                }

                if ((int) $product['quantity'] < $quantity) {
                    throw new RuntimeException('Insufficient stock for the selected product');
                }

                $effectiveUnitPrice = $unitPrice > 0 ? $unitPrice : (float) $product['unit_price'];
                $subtotal = round($effectiveUnitPrice * $quantity, 2);
                $orderTotal += $subtotal;

                $preparedItems[] = [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'unit_price' => $effectiveUnitPrice,
                    'subtotal' => $subtotal,
                ];
            }

            $orderStmt = db_prepare($db, 'INSERT INTO orders (customer_id, order_date, total_amount, status) VALUES (?, ?, ?, ?)');
            db_execute($orderStmt, [
                (int) ($body['customer_id'] ?? 0),
                $body['order_date'] ?? null,
                round($orderTotal, 2),
                $body['status'] ?? 'Pending',
            ]);
            $orderId = (int) db_last_insert_id($db);

            foreach ($preparedItems as $item) {
                $itemStmt = db_prepare($db, 'INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)');
                db_execute($itemStmt, [
                    $orderId,
                    $item['product_id'],
                    $item['quantity'],
                    $item['unit_price'],
                    $item['subtotal'],
                ]);

                $stockStmt = db_prepare($db, 'UPDATE products SET quantity = quantity - ? WHERE id = ?');
                db_execute($stockStmt, [$item['quantity'], $item['product_id']]);
            }

            $invoiceDate = !empty($body['order_date']) ? $body['order_date'] : date('Y-m-d');
            $invoiceStmt = db_prepare($db, 'INSERT INTO invoices (order_id, invoice_date, total_amount) VALUES (?, ?, ?)');
            db_execute($invoiceStmt, [$orderId, $invoiceDate, round($orderTotal, 2)]);

            $paymentStmt = db_prepare($db, 'INSERT INTO payments (order_id, payment_date, method, amount, reference_number) VALUES (?, ?, ?, ?, ?)');
            db_execute($paymentStmt, [
                $orderId,
                $invoiceDate,
                $body['payment_method'] ?? 'Cash',
                round($orderTotal, 2),
                'AUTO-' . $orderId,
            ]);

            mysqli_commit($db);
            handleOrders($db, 'GET', $orderId);
            return;
        } catch (Throwable $e) {
            mysqli_rollback($db);
            throw $e;
        }
    }

    if ($method === 'PUT' && $id) {
        $body = getJsonBody();
        $fields = ['customer_id', 'order_date', 'total_amount', 'status'];
        $updateParts = buildUpdateParts($body, $fields, ['customer_id'], ['total_amount']);

        if ($updateParts['setClauses'] === []) {
            jsonError('No fields to update');
        }

        $values = $updateParts['values'];
        $values[] = $id;

        $stmt = db_prepare($db, 'UPDATE orders SET ' . implode(', ', $updateParts['setClauses']) . ' WHERE id = ?');
        db_execute($stmt, $values);

        handleOrders($db, 'GET', $id);
        return;
    }

    if ($method === 'DELETE' && $id) {
        $stmt = db_prepare($db, 'DELETE FROM orders WHERE id = ?');
        db_execute($stmt, [$id]);
        jsonResponse(['success' => true]);
        return;
    }

    jsonError('Method not allowed', 405);
}

function handlePayments($db, string $method, ?int $id): void
{
    if ($method === 'GET') {
        if ($id) {
            $stmt = db_prepare($db, 'SELECT * FROM payments WHERE id = ?');
            db_execute($stmt, [$id]);
            $row = db_fetch_assoc($stmt);

            if (!$row) {
                jsonError('Payment not found', 404);
            }

            jsonResponse(formatRow($row, ['id', 'order_id', 'amount']));
        }

        $rows = db_fetch_all(db_query($db, 'SELECT * FROM payments ORDER BY payment_date DESC, id DESC'));
        jsonResponse(formatRows($rows, ['id', 'order_id', 'amount']));
        return;
    }

    if ($method === 'POST') {
        $body = getJsonBody();

        $stmt = db_prepare($db, 'INSERT INTO payments (order_id, payment_date, method, amount, reference_number) VALUES (?, ?, ?, ?, ?)');
        db_execute($stmt, [
            (int) ($body['order_id'] ?? 0),
            $body['payment_date'] ?? null,
            $body['method'] ?? null,
            (float) ($body['amount'] ?? 0),
            $body['reference_number'] ?? null,
        ]);

        $newId = (int) db_last_insert_id($db);
        handlePayments($db, 'GET', $newId);
        return;
    }

    jsonError('Method not allowed', 405);
}

function handleInvoices($db, string $method, ?int $id): void
{
    if ($method === 'GET') {
        if ($id) {
            $stmt = db_prepare($db, '
                SELECT i.*, o.customer_id, c.name AS customer_name
                FROM invoices i
                JOIN orders o ON i.order_id = o.id
                JOIN customers c ON o.customer_id = c.id
                WHERE i.id = ?
            ');
            db_execute($stmt, [$id]);
            $row = db_fetch_assoc($stmt);

            if (!$row) {
                jsonError('Invoice not found', 404);
            }

            jsonResponse(formatRow($row, ['id', 'order_id', 'total_amount', 'customer_id']));
        }

        $byOrder = isset($_GET['order_id']) ? (int) $_GET['order_id'] : null;
        if ($byOrder) {
            $stmt = db_prepare($db, '
                SELECT i.*, c.name AS customer_name, o.customer_id
                FROM invoices i
                JOIN orders o ON i.order_id = o.id
                JOIN customers c ON o.customer_id = c.id
                WHERE i.order_id = ?
            ');
            db_execute($stmt, [$byOrder]);
            $row = db_fetch_assoc($stmt);

            jsonResponse($row ? formatRow($row, ['id', 'order_id', 'total_amount', 'customer_id']) : null);
        }

        $rows = db_fetch_all(db_query($db, '
            SELECT i.*, c.name AS customer_name, o.customer_id
            FROM invoices i
            JOIN orders o ON i.order_id = o.id
            JOIN customers c ON o.customer_id = c.id
            ORDER BY i.invoice_date DESC, i.id DESC
        '));

        jsonResponse(formatRows($rows, ['id', 'order_id', 'total_amount', 'customer_id']));
        return;
    }

    if ($method === 'POST') {
        $body = getJsonBody();

        $stmt = db_prepare($db, 'INSERT INTO invoices (order_id, invoice_date, total_amount) VALUES (?, ?, ?)');
        db_execute($stmt, [
            (int) ($body['order_id'] ?? 0),
            $body['invoice_date'] ?? null,
            (float) ($body['total_amount'] ?? 0),
        ]);

        handleInvoices($db, 'GET', (int) db_last_insert_id($db));
        return;
    }

    jsonError('Method not allowed', 405);
}

function handleStaff($db, string $method, ?int $id): void
{
    if ($method === 'GET') {
        if ($id) {
            $stmt = db_prepare($db, 'SELECT id, name, phone, role, email, is_active FROM staff WHERE id = ?');
            db_execute($stmt, [$id]);
            $row = db_fetch_assoc($stmt);

            if (!$row) {
                jsonError('Staff not found', 404);
            }

            jsonResponse(formatRow($row, ['id']));
        }

        $rows = db_fetch_all(db_query($db, 'SELECT id, name, phone, role, email, is_active FROM staff ORDER BY id ASC'));
        jsonResponse(formatRows($rows, ['id']));
        return;
    }

    if ($method === 'POST') {
        $body = getJsonBody();

        $stmt = db_prepare($db, 'INSERT INTO staff (name, phone, role, email) VALUES (?, ?, ?, ?)');
        db_execute($stmt, [
            $body['name'] ?? null,
            $body['phone'] ?? null,
            $body['role'] ?? null,
            $body['email'] ?? null,
        ]);

        handleStaff($db, 'GET', (int) db_last_insert_id($db));
        return;
    }

    if ($method === 'PUT' && $id) {
        $body = getJsonBody();
        $fields = ['name', 'phone', 'role', 'email'];
        $updateParts = buildUpdateParts($body, $fields);

        if ($updateParts['setClauses'] === []) {
            jsonError('No fields to update');
        }

        $values = $updateParts['values'];
        $values[] = $id;

        $stmt = db_prepare($db, 'UPDATE staff SET ' . implode(', ', $updateParts['setClauses']) . ' WHERE id = ?');
        db_execute($stmt, $values);

        handleStaff($db, 'GET', $id);
        return;
    }

    if ($method === 'DELETE' && $id) {
        $stmt = db_prepare($db, 'DELETE FROM staff WHERE id = ?');
        db_execute($stmt, [$id]);
        jsonResponse(['success' => true]);
        return;
    }

    jsonError('Method not allowed', 405);
}

function handleCrud($db, string $method, ?int $id, string $table, array $fields): void
{
    if ($method === 'GET') {
        if ($id) {
            $stmt = db_prepare($db, "SELECT * FROM $table WHERE id = ?");
            db_execute($stmt, [$id]);
            $row = db_fetch_assoc($stmt);

            if (!$row) {
                jsonError('Not found', 404);
            }

            jsonResponse(formatRow($row, ['id']));
        }

        $rows = db_fetch_all(db_query($db, "SELECT * FROM $table ORDER BY id ASC"));
        jsonResponse(formatRows($rows, ['id']));
        return;
    }

    if ($method === 'POST') {
        $body = getJsonBody();
        $columns = [];
        $placeholders = [];
        $values = [];

        foreach ($fields as $field) {
            if (array_key_exists($field, $body)) {
                $columns[] = $field;
                $placeholders[] = '?';
                $values[] = $body[$field];
            }
        }

        if ($columns === []) {
            jsonError('No data provided');
        }

        $sql = 'INSERT INTO ' . $table . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';
        $stmt = db_prepare($db, $sql);
        db_execute($stmt, $values);

        handleCrud($db, 'GET', (int) db_last_insert_id($db), $table, $fields);
        return;
    }

    if ($method === 'PUT' && $id) {
        $body = getJsonBody();
        $updateParts = buildUpdateParts($body, $fields);

        if ($updateParts['setClauses'] === []) {
            jsonError('No fields to update');
        }

        $values = $updateParts['values'];
        $values[] = $id;

        $stmt = db_prepare($db, 'UPDATE ' . $table . ' SET ' . implode(', ', $updateParts['setClauses']) . ' WHERE id = ?');
        db_execute($stmt, $values);

        handleCrud($db, 'GET', $id, $table, $fields);
        return;
    }

    if ($method === 'DELETE' && $id) {
        try {
            $stmt = db_prepare($db, 'DELETE FROM ' . $table . ' WHERE id = ?');
            db_execute($stmt, [$id]);
            jsonResponse(['success' => true]);
            return;
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), '1451') || str_contains($e->getMessage(), '1452')) {
                jsonError('Cannot delete: record is referenced by other data', 409);
            }
            throw $e;
        }
    }

    jsonError('Method not allowed', 405);
}

function buildUpdateParts(array $body, array $fields, array $intFields = [], array $floatFields = []): array
{
    $setClauses = [];
    $values = [];

    foreach ($fields as $field) {
        if (!array_key_exists($field, $body)) {
            continue;
        }

        $setClauses[] = $field . ' = ?';
        $value = $body[$field];

        if (in_array($field, $intFields, true)) {
            $value = (int) $value;
        } elseif (in_array($field, $floatFields, true)) {
            $value = (float) $value;
        }

        $values[] = $value;
    }

    return [
        'setClauses' => $setClauses,
        'values' => $values,
    ];
}

function formatRow(array $row, array $fields): array
{
    $formatted = [];

    foreach ($row as $key => $value) {
        $formatted[$key] = $value;
    }

    foreach ($fields as $field) {
        if (array_key_exists($field, $formatted)) {
            $formatted[$field] = convertToNumberIfPossible($formatted[$field]);
        }
    }

    return $formatted;
}

function formatRows(array $rows, array $fields): array
{
    $formattedRows = [];

    foreach ($rows as $row) {
        $formattedRows[] = formatRow($row, $fields);
    }

    return $formattedRows;
}

function convertToNumberIfPossible($value)
{
    if (!is_numeric($value)) {
        return $value;
    }

    if (strpos((string) $value, '.') !== false) {
        return (float) $value;
    }

    return (int) $value;
}

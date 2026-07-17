<?php
require_once 'config/database.php';
$db = getDB();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    die("Invalid invoice ID.");
}

$sql = "
SELECT
    invoices.id,
    invoices.invoice_date,
    invoices.total_amount,
    orders.id AS order_id,
    customers.name,
    customers.phone,
    customers.email,
    customers.address
FROM invoices
INNER JOIN orders
    ON invoices.order_id = orders.id
INNER JOIN customers
    ON orders.customer_id = customers.id
WHERE invoices.id = ?
";

$stmt = db_prepare($db, $sql);
db_execute($stmt, [$id]);

$invoice = db_fetch_assoc($stmt);

if (!$invoice) {
    die("Invoice not found.");
}
$sql = "
SELECT oi.* , p.name AS product_name
FROM order_items oi
JOIN products p ON oi.product_id = p.id
WHERE oi.order_id = ?
";
$stmt = db_prepare($db, $sql);
db_execute($stmt, [$invoice['order_id']]);
$invoiceItems = db_fetch_all($stmt);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <link rel="stylesheet" href="css/print_invoice.css">
</head>
<body>
    <div class="invoice">
    <h2>ADDADUMA ENTERPRISE</h2>
    <h3>SALES INVOICE</h3>
<hr>
    <p><strong>Invoice No:</strong> <?= $invoice['id'] ?></p>
    <p><strong>Order No:</strong> <?= $invoice['order_id'] ?></p>
    <p><strong>Date:</strong> <?= $invoice['invoice_date'] ?></p>
<hr>
    <h4>Customer Details</h4>
    <p><strong>Name:</strong> <?= htmlspecialchars($invoice['name']) ?></p>
    <p><strong>Phone:</strong> <?= htmlspecialchars($invoice['phone']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($invoice['email']) ?></p>
    <p><strong>Address:</strong> <?= htmlspecialchars($invoice['address']) ?></p>
<hr>
<table>
        <tr>
            <th>#</th>
            <th>Product</th>
            <th>Qty</th>
            <th>Unit Price</th>
            <th>Subtotal</th>
        </tr>
                <?php
                $count = 1;
                foreach($invoiceItems as $item){
                ?>
        <tr>
            <td><?= $count++ ?></td>
            <td><?= htmlspecialchars($item['product_name']) ?></td>
            <td><?= $item['quantity'] ?></td>
            <td>GHS <?= number_format($item['unit_price'],2) ?></td>
            <td>GHS <?= number_format($item['subtotal'],2) ?></td>
        </tr>
    <?php } ?>
    </table>
        <p class="total"> Total: GHS <?= number_format($invoice['total_amount'],2) ?></p>
    <hr>
    <p style="text-align:center">Thank you for doing business with us.</p>
</div>
<script>
window.onload=function(){
    window.print();
}
window.onafterprint=function(){
    window.close();
}
</script>
</body>
</html>
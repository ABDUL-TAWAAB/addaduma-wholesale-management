<?php
require_once 'config/database.php';
$db = getDB();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    die("Invalid payment ID.");
}

$sql = "
SELECT
    payments.id,
    payments.payment_date,
    payments.method,
    payments.amount,
    payments.reference_number,
    orders.id AS order_id,
    customers.name,
    customers.phone,
    customers.email,
    customers.address
FROM payments
INNER JOIN orders
    ON payments.order_id = orders.id
INNER JOIN customers
    ON orders.customer_id = customers.id
WHERE payments.id = ?
";

$stmt = db_prepare($db, $sql);

db_execute($stmt, [$id]);

$payment = db_fetch_assoc($stmt);

if (!$payment) {
    die("Payment not found.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment Receipt</title>
<style>
body{
    font-family:Arial;
    background:#eee;
}
.receipt{
    width:500px;
    margin:40px auto;
    background:white;
    padding:30px;
    border:1px solid #ddd;
}
table{
    width:100%;
    border-collapse:collapse;
}
td{
    padding:8px;
}
h2,h3{
    text-align:center;
}
button{
    margin-top:20px;
}
@media print{
button{
display:none;
}
body{
background:white;
}
.receipt{
border:none;
margin:0;
width:100%;
}

}
</style>
</head>

<body>
    
<div class="receipt">
<h2>ADDADUMA ENTERPRISE</h2>
<h3>PAYMENT RECEIPT</h3>
<hr>
<table>
<tr>
    <td><strong>Receipt No</strong></td>
    <td><?= $payment['id'] ?></td>
</tr>
<tr>
<td><strong>Order No</strong></td>
<td><?= $payment['order_id'] ?></td>
</tr>
<tr>
    <td><strong>Customer</strong></td>
    <td><?= htmlspecialchars($payment['name']) ?></td>
</tr>
<tr>
    <td><strong>Phone</strong></td>
    <td><?= htmlspecialchars($payment['phone']) ?></td>
</tr>
<tr>
    <td><strong>Amount</strong></td>
    <td>GHS <?= number_format($payment['amount'],2) ?></td>
</tr>
<tr>
    <td><strong>Method</strong></td>
    <td><?= htmlspecialchars($payment['method']) ?></td>
</tr>
<tr>
    <td><strong>Reference</strong></td>
    <td><?= htmlspecialchars($payment['reference_number']) ?></td>
</tr>
<tr>
    <td><strong>Date</strong></td>
    <td><?= htmlspecialchars($payment['payment_date']) ?></td>
</tr>
</table>
<hr>
    <p style="text-align:center">Thank you for doing business with us.</p>
    <button onclick="window.print()">Print Receipt</button>
</div>
<script>
window.onload = function(){
    window.print();
}
window.onafterprint = function(){
    window.close();
}
</script>
</body>
</html>
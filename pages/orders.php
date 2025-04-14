<?php
$pdo = require_once '../config/database.php';
require_once '../includes/Order.php';
$order = new Order($pdo);
$orders = $order->getAllOrders();
?>

<h2>All Orders</h2>
<table border="1" cellpadding="10" cellspacing="0">
    <tr>
        <th>Order #</th>
        <th>Total</th>
        <th>Status</th>
        <th>Created At</th>
        <th>Action</th>
    </tr>
    <?php foreach ($orders as $ord): ?>
        <tr>
            <td><?= $ord['id'] ?></td>
            <td><?= number_format($ord['total'], 2) ?> EGP</td>
            <td><?= $ord['status'] ?></td>
            <td><?= $ord['created_at'] ?></td>
            <td><a href="order_details.php?order_id=<?= $ord['id'] ?>">View</a></td>
        </tr>
    <?php endforeach; ?>
</table>

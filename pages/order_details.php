<?php
$pdo = require_once '../config/database.php';
require_once '../includes/Order.php';
$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

$order = new Order($pdo);
$data = $order->getOrderWithItems($orderId);

if (!$data) {
    echo "<p>Order not found.</p>";
    exit;
}
?>

<h2>Order #<?= $data['id'] ?> Details</h2>
<p>Status: <strong><?= $data['status'] ?></strong></p>

<h3>Items</h3>
<ul>
    <?php foreach ($data['items'] as $item): ?>
        <li><?= $item['name'] ?> (x<?= $item['quantity'] ?>) - <?= number_format($item['price'], 2) ?> EGP</li>
    <?php endforeach; ?>
</ul>

<h3>Payment Payloads</h3>
<?php
$log = $order->getPaymentLog($orderId);
if ($log):
?>
    <strong>Request:</strong>
    <pre><?= htmlspecialchars($log['request_payload']) ?></pre>

    <strong>Response:</strong>
    <pre><?= htmlspecialchars($log['response_payload']) ?></pre>
<?php else: ?>
    <p>No payment recorded.</p>
<?php endif; ?>

<?php
$refunds = $order->getRefunds($orderId);
if ($refunds):
?>
    <h3>Refunds</h3>
    <?php foreach ($refunds as $r): ?>
        <strong>Request:</strong>
        <pre><?= htmlspecialchars($r['request_payload']) ?></pre>
        <strong>Response:</strong>
        <pre><?= htmlspecialchars($r['response_payload']) ?></pre>
        <hr>
    <?php endforeach; ?>
<?php endif; ?>

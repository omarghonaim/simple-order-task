<?php
$pdo = require_once '../config/database.php';
require_once '../includes/Order.php';
// require_once '../templates/header.php';

$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$order = new Order($pdo);
$orderDetails = $order->getOrderWithItems($orderId);

if (!$orderDetails) {
    echo "<p>Order not found.</p>";
    require_once '../templates/footer.php';
    exit;
}

$total = 0;
foreach ($orderDetails['items'] as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>

<h2>Checkout</h2>
<p><strong>Order #<?= $orderId ?></strong></p>
<p>Total Amount: <strong><?= number_format($total, 2) ?> EGP</strong></p>

<form id="checkout-form">
    <input type="hidden" name="order_id" value="<?= $orderId ?>">
    <input type="text" name="customer_name" placeholder="Full Name" required><br><br>
    <input type="email" name="customer_email" placeholder="Email Address" required><br><br>

    <label>
        <input type="radio" name="delivery_method" value="shipping" checked> Ship to address
    </label>
    <label>
        <input type="radio" name="delivery_method" value="pickup"> Pick up in store
    </label><br><br>

    <button type="submit" id="payBtn">Pay Now</button>
</form>

<div id="payment-form" style="margin-top: 30px;"></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$('#checkout-form').on('submit', function(e) {
    e.preventDefault();

    $.ajax({
        url: '../includes/Payment.php',
        method: 'POST',
        data: $(this).serialize() + '&action=init_payment',
        success: function(response) {
            $('#payment-form').html(response);
        },
        error: function() {
            alert('Error processing payment.');
        }
    });
});
</script>

<!-- <?php require_once '../templates/footer.php'; ?> -->

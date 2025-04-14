<?php
$pdo = require_once '../config/database.php'; // ✅ Get $pdo from config
require_once '../includes/Product.php';
require_once '../includes/Order.php';
// require_once '../templates/header.php';


$productModel = new Product($pdo);
$products = $productModel->getAllProducts();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order = new Order($pdo);
    $orderId = $order->createOrder($_POST['products']); // array: [product_id => qty]

    header("Location: checkout.php?order_id=" . $orderId);
    exit;
}
?>

<h2>Create New Order</h2>
<form method="post">
    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>Product</th>
                <th>Price (EGP)</th>
                <th>Quantity</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= htmlspecialchars($product['name']) ?></td>
                    <td><?= number_format($product['price'], 2) ?></td>
                    <td>
                        <input type="number" name="products[<?= $product['id'] ?>]" min="0" value="0" />
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <br>
    <button type="submit">Proceed to Checkout</button>
</form>

<!-- <?php require_once '../templates/footer.php'; ?> -->

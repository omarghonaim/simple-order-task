<?php
$pdo = require_once '../config/database.php';

if ($_POST['action'] === 'init_payment') {
    $orderId = (int)$_POST['order_id'];
    $name = $_POST['customer_name'];
    $email = $_POST['customer_email'];
    $method = $_POST['delivery_method'];

    $stmt = $pdo->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
    $stmt->execute([$orderId]);
    $items = $stmt->fetchAll();

    $amount = 0;
    foreach ($items as $item) {
        $amount += $item['price'] * $item['quantity'];
    }

    $payload = [
        "profile_id" => "132344",
        "tran_type" => "sale",
        "tran_class" => "ecom",
        "cart_id" => "ORDER-$orderId",
        "cart_description" => "Simple Order #$orderId",
        "cart_currency" => "EGP",
        "cart_amount" => $amount,
        "return" => "http://localhost:8888/simpleOrderTask/pages/success.php",
        "callback" => "http://localhost:8888/simpleOrderTask/includes/paytabs_callback.php",

        "framed" => true,
        "framed_return_top" => true,
        "hide_shipping" => true,

        "customer_details" => [
            "name" => $name,
            "email" => $email,
            "phone" => "01000000000",
            "street1" => "N/A",
            "city" => "Cairo",
            "state" => "Cairo",
            "country" => "EG",
            "zip" => "12345",
            "ip" => $_SERVER['REMOTE_ADDR']
        ],
        "shipping_details" => [
            "name" => $name,
            "email" => $email,
            "phone" => "01000000000",
            "street1" => "N/A",
            "city" => "Cairo",
            "state" => "Cairo",
            "country" => "EG",
            "zip" => "12345"
        ]
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://secure-egypt.paytabs.com/payment/request");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "authorization: SWJ992BZTN-JHGTJBWDLM-BZJKMR2ZHT",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $result = json_decode($response);

    if (isset($result->redirect_url)) {
        echo '<iframe id="paytabs-iframe" src="' . $result->redirect_url . '" width="100%" height="600" frameborder="0" allow="payment" scrolling="no"></iframe>';
    } else {
        echo "<p>Error creating payment. Check credentials or API response.</p>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
    }

    exit;
}

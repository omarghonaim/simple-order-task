<?php
class Order {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function createOrder($products) {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("INSERT INTO orders (status, created_at) VALUES ('pending', NOW())");
            $stmt->execute();
            $orderId = $this->pdo->lastInsertId();

            $itemStmt = $this->pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($products as $productId => $qty) {
                if ((int)$qty > 0) {
                    $priceStmt = $this->pdo->prepare("SELECT price FROM products WHERE id = ?");
                    $priceStmt->execute([$productId]);
                    $product = $priceStmt->fetch(PDO::FETCH_ASSOC);
                    $itemStmt->execute([$orderId, $productId, $qty, $product['price']]);
                }
            }

            $this->pdo->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            die("Order creation failed: " . $e->getMessage());
        }
    }
    public function getOrderWithItems($orderId) {
        $stmt = $this->pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
    
        if (!$order) {
            return false;
        }    
        $stmt = $this->pdo->prepare("
            SELECT oi.*, p.name 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?");
        $stmt->execute([$orderId]);
        $items = $stmt->fetchAll();    
        return [
            'id' => $order['id'],
            'status' => $order['status'],
            'created_at' => $order['created_at'],
            'items' => $items
        ];
    }
    
    public function getAllOrders() {
        $stmt = $this->pdo->query("SELECT o.*, 
            (SELECT SUM(price * quantity) FROM order_items WHERE order_id = o.id) as total 
            FROM orders o ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }
    
    public function getPaymentLog($orderId) {
        $stmt = $this->pdo->prepare("SELECT * FROM payments WHERE order_id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetch();
    }
    
    public function getRefunds($orderId) {
        $stmt = $this->pdo->prepare("
            SELECT r.* 
            FROM refunds r
            JOIN payments p ON r.payment_id = p.id
            WHERE p.order_id = ?
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }
    
    
    
    
}

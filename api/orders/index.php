<?php
require_once '../config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        getOrders();
        break;
    case 'POST':
        createOrder();
        break;
    default:
        sendError('Method not allowed', 405);
}

function getOrders() {
    global $pdo;
    
    if(!isset($_SESSION['user_id'])) sendError('Unauthorized', 401);
    
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($orders as &$order) {
        $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$order['id']]);
        $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    sendResponse(['success' => true, 'data' => $orders]);
}

function createOrder() {
    global $pdo;
    
    if(!isset($_SESSION['user_id'])) sendError('Unauthorized', 401);
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $pdo->beginTransaction();
    
    try {
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, shipping_address, status) VALUES (?, ?, ?, 'pending')");
        $stmt->execute([
            $_SESSION['user_id'],
            $data['total_amount'],
            $data['shipping_address']
        ]);
        
        $orderId = $pdo->lastInsertId();
        
        foreach($data['items'] as $item) {
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $orderId,
                $item['product_id'],
                $item['product_name'],
                $item['quantity'],
                $item['price']
            ]);
        }
        
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        
        $pdo->commit();
        
        sendResponse(['success' => true, 'order_id' => $orderId], 201);
    } catch(Exception $e) {
        $pdo->rollBack();
        sendError('Order creation failed', 500);
    }
}
?>

<?php
require_once '../config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        getCart();
        break;
    case 'POST':
        addToCart();
        break;
    case 'PUT':
        updateCart();
        break;
    case 'DELETE':
        removeFromCart();
        break;
    default:
        sendError('Method not allowed', 405);
}

function getCart() {
    global $pdo;
    
    if(!isset($_SESSION['user_id'])) sendError('Unauthorized', 401);
    
    $stmt = $pdo->prepare("SELECT c.*, p.name, p.price, p.image 
                           FROM cart c 
                           JOIN products p ON c.product_id = p.id 
                           WHERE c.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total = array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $items));
    
    sendResponse(['success' => true, 'data' => $items, 'total' => $total]);
}

function addToCart() {
    global $pdo;
    
    if(!isset($_SESSION['user_id'])) sendError('Unauthorized', 401);
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$_SESSION['user_id'], $data['product_id']]);
    $existing = $stmt->fetch();
    
    if($existing) {
        $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + ? WHERE id = ?");
        $stmt->execute([$data['quantity'] ?? 1, $existing['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $data['product_id'], $data['quantity'] ?? 1]);
    }
    
    sendResponse(['success' => true]);
}

function updateCart() {
    global $pdo;
    
    if(!isset($_SESSION['user_id'])) sendError('Unauthorized', 401);
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$data['quantity'], $data['cart_id'], $_SESSION['user_id']]);
    
    sendResponse(['success' => true]);
}

function removeFromCart() {
    global $pdo;
    
    if(!isset($_SESSION['user_id'])) sendError('Unauthorized', 401);
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$data['cart_id'], $_SESSION['user_id']]);
    
    sendResponse(['success' => true]);
}
?>

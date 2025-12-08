<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();
require_once 'config.php';

header('Content-Type: application/json');

try {

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$user_id = $_SESSION['user_id'];

try {
    switch ($action) {
        case 'add':
            $product_id = (int)$_POST['product_id'];
            $quantity = (int)($_POST['quantity'] ?? 1);
            
            // Check if product exists and has stock
            $stmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ? AND status = 'active'");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
            
            if (!$product) {
                echo json_encode(['success' => false, 'message' => 'Product not found']);
                exit();
            }
            
            if ($product['stock_quantity'] < $quantity) {
                echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
                exit();
            }
            
            // Check if item already in cart
            $stmt = $pdo->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user_id, $product_id]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                $new_quantity = $existing['quantity'] + $quantity;
                if ($new_quantity > $product['stock_quantity']) {
                    echo json_encode(['success' => false, 'message' => 'Cannot add more items than available stock']);
                    exit();
                }
                
                try {
                    $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
                    $stmt->execute([$new_quantity, $user_id, $product_id]);
                } catch (PDOException $e) {
                    $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
                    $stmt->execute([$new_quantity, $user_id, $product_id]);
                }
            } else {
                $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                $stmt->execute([$user_id, $product_id, $quantity]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Item added to cart']);
            break;
            
        case 'update':
            $product_id = (int)$_POST['product_id'];
            $quantity = (int)$_POST['quantity'];
            
            if ($quantity <= 0) {
                $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$user_id, $product_id]);
            } else {
                // Check stock
                $stmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ?");
                $stmt->execute([$product_id]);
                $product = $stmt->fetch();
                
                if ($quantity > $product['stock_quantity']) {
                    echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
                    exit();
                }
                
                try {
                    $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
                    $stmt->execute([$quantity, $user_id, $product_id]);
                } catch (PDOException $e) {
                    // Try without updated_at column if it doesn't exist
                    $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
                    $stmt->execute([$quantity, $user_id, $product_id]);
                }
            }
            
            echo json_encode(['success' => true, 'message' => 'Cart updated']);
            break;
            
        case 'remove':
            $product_id = (int)$_POST['product_id'];
            
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user_id, $product_id]);
            
            echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
            break;
            
        case 'count':
            $stmt = $pdo->prepare("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $result = $stmt->fetch();
            
            echo json_encode(['count' => (int)($result['count'] ?? 0)]);
            break;
            
        case 'get':
            $stmt = $pdo->prepare("
                SELECT c.*, p.name, p.price, p.image, p.stock_quantity 
                FROM cart c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.user_id = ? 
                ORDER BY c.created_at DESC
            ");
            $stmt->execute([$user_id]);
            $items = $stmt->fetchAll();
            
            $total = 0;
            foreach ($items as &$item) {
                $item['subtotal'] = $item['price'] * $item['quantity'];
                $total += $item['subtotal'];
            }
            
            echo json_encode([
                'success' => true,
                'items' => $items,
                'total' => $total,
                'count' => count($items)
            ]);
            break;
            
        case 'clear':
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            echo json_encode(['success' => true, 'message' => 'Cart cleared successfully']);
            break;
            
        case 'reorder':
            $order_id = (int)$_POST['order_id'];
            
            $stmt = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
            $stmt->execute([$order_id]);
            $items = $stmt->fetchAll();
            
            foreach ($items as $item) {
                $product_id = $item['product_id'];
                $quantity = $item['quantity'];
                
                $stmt = $pdo->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$user_id, $product_id]);
                $existing = $stmt->fetch();
                
                if ($existing) {
                    try {
                        $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?");
                        $stmt->execute([$quantity, $user_id, $product_id]);
                    } catch (PDOException $e) {
                        $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?");
                        $stmt->execute([$quantity, $user_id, $product_id]);
                    }
                } else {
                    $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                    $stmt->execute([$user_id, $product_id, $quantity]);
                }
            }
            
            echo json_encode(['success' => true, 'message' => 'Items added to cart']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} catch (PDOException $e) {
    error_log('Cart handler PDO error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

} catch (Exception $e) {
    error_log('Cart handler error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
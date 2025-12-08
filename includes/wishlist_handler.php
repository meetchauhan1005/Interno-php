<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

$action = $_POST['action'] ?? '';
$user_id = $_SESSION['user_id'];

try {
    switch ($action) {
        case 'add':
            $product_id = (int)$_POST['product_id'];
            
            // Check if already in wishlist
            $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user_id, $product_id]);
            
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Item already in wishlist']);
                exit();
            }
            
            // Add to wishlist
            $stmt = $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $product_id]);
            
            echo json_encode(['success' => true, 'message' => 'Added to wishlist']);
            break;
            
        case 'remove':
            $product_id = (int)$_POST['product_id'];
            
            $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user_id, $product_id]);
            
            echo json_encode(['success' => true, 'message' => 'Removed from wishlist']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
<?php
session_start();
require_once 'includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to submit a review']);
    exit;
}

$product_id = (int)$_POST['product_id'];
$rating = (int)$_POST['rating'];
$comment = trim($_POST['comment']);
$user_id = $_SESSION['user_id'];

if ($product_id <= 0 || $rating < 1 || $rating > 5 || empty($comment)) {
    echo json_encode(['success' => false, 'message' => 'Please provide valid rating and comment']);
    exit;
}

try {
    // Create table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS product_reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        user_id INT NOT NULL,
        rating INT NOT NULL,
        comment TEXT NOT NULL,
        status VARCHAR(20) DEFAULT 'approved',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Check if user already reviewed this product
    $stmt = $pdo->prepare("SELECT id FROM product_reviews WHERE product_id = ? AND user_id = ?");
    $stmt->execute([$product_id, $user_id]);
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'You have already reviewed this product']);
        exit;
    }
    
    // Insert review
    $stmt = $pdo->prepare("INSERT INTO product_reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->execute([$product_id, $user_id, $rating, $comment]);
    
    echo json_encode(['success' => true, 'message' => 'Review submitted successfully!']);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
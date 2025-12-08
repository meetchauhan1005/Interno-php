<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Email is required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

try {
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM newsletter_subscribers WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already subscribed']);
        exit;
    }
    
    // Add new subscriber
    $stmt = $pdo->prepare("INSERT INTO newsletter_subscribers (email, subscribed_at) VALUES (?, NOW())");
    $stmt->execute([$email]);
    
    echo json_encode(['success' => true, 'message' => 'Successfully subscribed to newsletter']);
    
} catch (PDOException $e) {
    // If table doesn't exist, create it
    if ($e->getCode() == '42S02') {
        try {
            $pdo->exec("CREATE TABLE newsletter_subscribers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) UNIQUE NOT NULL,
                subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            
            // Try inserting again
            $stmt = $pdo->prepare("INSERT INTO newsletter_subscribers (email, subscribed_at) VALUES (?, NOW())");
            $stmt->execute([$email]);
            
            echo json_encode(['success' => true, 'message' => 'Successfully subscribed to newsletter']);
        } catch (PDOException $e2) {
            echo json_encode(['success' => false, 'message' => 'Database error occurred']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    }
}
?>
<?php
session_start();
require_once '../includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$email = trim($_POST['email']);

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Email is required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

try {
    // Check if email exists
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ? AND is_active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Email not found in our records']);
        exit;
    }
    
    // Generate reset token
    $reset_token = bin2hex(random_bytes(32));
    $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Store reset token in database
    $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, email, token, expires_at) VALUES (?, ?, ?, ?) 
                          ON DUPLICATE KEY UPDATE token = ?, expires_at = ?");
    $stmt->execute([$user['id'], $email, $reset_token, $expires_at, $reset_token, $expires_at]);
    
    // In a real application, you would send an email here
    // For demo purposes, we'll just return success
    echo json_encode([
        'success' => true, 
        'message' => 'Password reset instructions sent to your email',
        'demo_token' => $reset_token // Remove this in production
    ]);
    
} catch (PDOException $e) {
    // Create password_resets table if it doesn't exist
    if ($e->getCode() == '42S02') {
        try {
            $pdo->exec("CREATE TABLE password_resets (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                email VARCHAR(255) NOT NULL,
                token VARCHAR(64) NOT NULL,
                expires_at TIMESTAMP NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_email (email),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )");
            
            // Try again after creating table
            $reset_token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, email, token, expires_at) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user['id'], $email, $reset_token, $expires_at]);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Password reset instructions sent to your email',
                'demo_token' => $reset_token
            ]);
        } catch (PDOException $e2) {
            echo json_encode(['success' => false, 'message' => 'Database error occurred']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    }
}
?>
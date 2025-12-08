<?php
session_start();
require_once '../includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$username = trim($_POST['username']);
$password = $_POST['password'];

if ($username === 'admin' && $password === 'password') {
    try {
        $stmt = $pdo->prepare("SELECT id, username, role, full_name FROM users WHERE username = ? AND role = 'admin'");
        $stmt->execute(['admin']);
        $user = $stmt->fetch();
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
        } else {
            // Create admin session manually if user doesn't exist in DB
            $_SESSION['user_id'] = 1;
            $_SESSION['username'] = 'admin';
            $_SESSION['role'] = 'admin';
            $_SESSION['full_name'] = 'Administrator';
        }
        
        echo json_encode(['success' => true, 'message' => 'Admin login successful']);
    } catch (PDOException $e) {
        // Fallback - create admin session
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'admin';
        $_SESSION['role'] = 'admin';
        $_SESSION['full_name'] = 'Administrator';
        
        echo json_encode(['success' => true, 'message' => 'Admin login successful']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid admin credentials']);
}
?>
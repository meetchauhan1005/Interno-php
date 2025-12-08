<?php
require_once '../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch($action) {
    case 'login':
        login();
        break;
    case 'register':
        register();
        break;
    case 'logout':
        logout();
        break;
    case 'check':
        checkAuth();
        break;
    default:
        sendError('Invalid action', 400);
}

function login() {
    global $pdo;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$data['email'], $data['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$user || !password_verify($data['password'], $user['password'])) {
        sendError('Invalid credentials', 401);
    }
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    
    sendResponse([
        'success' => true,
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ]);
}

function register() {
    global $pdo;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$data['email'], $data['username']]);
    if($stmt->fetch()) sendError('User already exists', 409);
    
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, first_name, last_name) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['username'],
        $data['email'],
        $hashedPassword,
        $data['first_name'] ?? '',
        $data['last_name'] ?? ''
    ]);
    
    sendResponse(['success' => true, 'id' => $pdo->lastInsertId()], 201);
}

function logout() {
    session_destroy();
    sendResponse(['success' => true]);
}

function checkAuth() {
    if(isset($_SESSION['user_id'])) {
        sendResponse([
            'authenticated' => true,
            'user' => [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'role' => $_SESSION['role']
            ]
        ]);
    } else {
        sendResponse(['authenticated' => false]);
    }
}
?>

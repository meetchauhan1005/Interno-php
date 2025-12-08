<?php
require_once '../config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        getCategories();
        break;
    default:
        sendError('Method not allowed', 405);
}

function getCategories() {
    global $pdo;
    
    $stmt = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order, name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    sendResponse(['success' => true, 'data' => $categories]);
}
?>

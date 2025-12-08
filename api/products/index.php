<?php
require_once '../config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            getProduct($_GET['id']);
        } else {
            getProducts();
        }
        break;
    case 'POST':
        createProduct();
        break;
    case 'PUT':
        updateProduct();
        break;
    case 'DELETE':
        deleteProduct();
        break;
    default:
        sendError('Method not allowed', 405);
}

function getProducts() {
    global $pdo;
    
    $category = $_GET['category'] ?? null;
    $search = $_GET['search'] ?? null;
    $featured = $_GET['featured'] ?? null;
    $limit = $_GET['limit'] ?? 100;
    
    $sql = "SELECT p.*, c.name as category_name FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.status = 'active'";
    
    if($category) $sql .= " AND p.category_id = " . intval($category);
    if($search) $sql .= " AND p.name LIKE '%" . $pdo->quote($search) . "%'";
    if($featured) $sql .= " AND p.featured = 1";
    
    $sql .= " ORDER BY p.created_at DESC LIMIT " . intval($limit);
    
    $stmt = $pdo->query($sql);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    sendResponse(['success' => true, 'data' => $products]);
}

function getProduct($id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.id 
                           WHERE p.id = ? AND p.status = 'active'");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$product) sendError('Product not found', 404);
    
    sendResponse(['success' => true, 'data' => $product]);
}

function createProduct() {
    global $pdo;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, category_id, stock_quantity, image) 
                           VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['name'],
        $data['description'],
        $data['price'],
        $data['category_id'],
        $data['stock_quantity'] ?? 0,
        $data['image'] ?? null
    ]);
    
    sendResponse(['success' => true, 'id' => $pdo->lastInsertId()], 201);
}

function updateProduct() {
    global $pdo;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $stmt = $pdo->prepare("UPDATE products SET name=?, description=?, price=?, category_id=?, stock_quantity=? WHERE id=?");
    $stmt->execute([
        $data['name'],
        $data['description'],
        $data['price'],
        $data['category_id'],
        $data['stock_quantity'],
        $data['id']
    ]);
    
    sendResponse(['success' => true]);
}

function deleteProduct() {
    global $pdo;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $stmt = $pdo->prepare("UPDATE products SET status='inactive' WHERE id=?");
    $stmt->execute([$data['id']]);
    
    sendResponse(['success' => true]);
}
?>

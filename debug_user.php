<?php
session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    die('Please login first');
}

try {
    // Check user data
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    echo "<h3>User Data:</h3>";
    echo "<pre>";
    print_r($user);
    echo "</pre>";
    
    // Check table structure
    echo "<h3>Users Table Columns:</h3>";
    $columns = $pdo->query("SHOW COLUMNS FROM users")->fetchAll();
    foreach ($columns as $col) {
        echo $col['Field'] . " - " . $col['Type'] . "<br>";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
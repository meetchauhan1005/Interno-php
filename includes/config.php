<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'interno_ecommerce');

try {
    // First connect without database to create it if needed
    $pdo_temp = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo_temp->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $pdo_temp->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    
    // Now connect to the database
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if tables exist, create them if not
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        // Create tables
        $sql = file_get_contents(__DIR__ . '/../database.sql');
        $pdo->exec($sql);
    }
    
    // Add missing columns if they don't exist
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(20)");
    } catch (PDOException $e) {}
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN address TEXT");
    } catch (PDOException $e) {}
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN city VARCHAR(50)");
    } catch (PDOException $e) {}
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN state VARCHAR(50)");
    } catch (PDOException $e) {}
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN zip_code VARCHAR(10)");
    } catch (PDOException $e) {}
    
    // Add missing orders table columns
    try {
        $pdo->exec("ALTER TABLE orders ADD COLUMN order_number VARCHAR(50)");
    } catch (PDOException $e) {}
    try {
        $pdo->exec("ALTER TABLE orders ADD COLUMN subtotal DECIMAL(10,2) DEFAULT 0");
    } catch (PDOException $e) {}
    try {
        $pdo->exec("ALTER TABLE orders ADD COLUMN shipping_amount DECIMAL(10,2) DEFAULT 0");
    } catch (PDOException $e) {}
    try {
        $pdo->exec("ALTER TABLE orders ADD COLUMN tax_amount DECIMAL(10,2) DEFAULT 0");
    } catch (PDOException $e) {}
    try {
        $pdo->exec("ALTER TABLE orders ADD COLUMN payment_status VARCHAR(20) DEFAULT 'pending'");
    } catch (PDOException $e) {}
    try {
        $pdo->exec("ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50)");
    } catch (PDOException $e) {}
    try {
        $pdo->exec("ALTER TABLE orders ADD COLUMN shipping_address TEXT");
    } catch (PDOException $e) {}
    
    // Create order_items table if it doesn't exist
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            product_name VARCHAR(255) NOT NULL,
            quantity INT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            total DECIMAL(10,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    } catch (PDOException $e) {}
    
} catch(PDOException $e) {
    die("Database error: " . $e->getMessage() . "<br>Please make sure MySQL is running and import database.sql manually.");
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
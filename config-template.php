<?php
// HOSTING CONFIGURATION TEMPLATE
// Copy this to includes/config.php and update with your hosting details

// Database Configuration
define('DB_HOST', 'localhost');           // Change to your host (e.g., sql123.infinityfree.com)
define('DB_USER', 'root');                // Change to your database username
define('DB_PASS', '');                    // Change to your database password
define('DB_NAME', 'interno_ecommerce');   // Change to your database name

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

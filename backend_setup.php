<?php
// Backend Setup and Database Initialization
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>INTERNO E-commerce Backend Setup</h1>";

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'interno_ecommerce';

try {
    // Connect to MySQL server
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>✅ MySQL connection successful</p>";
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $database");
    echo "<p>✅ Database '$database' created/verified</p>";
    
    // Connect to the database
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Read and execute SQL file
    $sql = file_get_contents('database.sql');
    $pdo->exec($sql);
    
    echo "<p>✅ Database tables created successfully</p>";
    
    // Verify tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>✅ Tables created: " . implode(', ', $tables) . "</p>";
    
    // Check sample data
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    $productCount = $stmt->fetchColumn();
    echo "<p>✅ Sample products: $productCount</p>";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM categories");
    $categoryCount = $stmt->fetchColumn();
    echo "<p>✅ Categories: $categoryCount</p>";
    
    echo "<hr>";
    echo "<h2>🎉 Backend Setup Complete!</h2>";
    echo "<div style='background: #f0f8ff; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3>Access Your Website:</h3>";
    echo "<p><strong>Frontend:</strong> <a href='http://localhost/interno-php/' target='_blank' style='color: #007bff;'>http://localhost/interno-php/</a></p>";
    echo "<p><strong>Admin Panel:</strong> <a href='http://localhost/interno-php/admin/dashboard.php' target='_blank' style='color: #007bff;'>http://localhost/interno-php/admin/dashboard.php</a></p>";
    echo "<p><strong>Admin Login:</strong> Username: <code>admin</code> | Password: <code>password</code></p>";
    echo "</div>";
    
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h4>Next Steps:</h4>";
    echo "<ul>";
    echo "<li>Visit the frontend to see your e-commerce website</li>";
    echo "<li>Login to admin panel to manage products and orders</li>";
    echo "<li>Add product images to assets/images/ folder</li>";
    echo "</ul>";
    echo "</div>";
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Make sure XAMPP MySQL service is running!</p>";
}
?>
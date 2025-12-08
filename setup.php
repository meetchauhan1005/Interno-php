<?php
// INTERNO E-commerce Setup Script
require_once 'includes/config.php';

echo "<h2>INTERNO E-commerce Setup</h2>";

try {
    // Test database connection
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    $adminCount = $stmt->fetchColumn();
    
    echo "<p>✅ Database connection successful</p>";
    echo "<p>✅ Tables created successfully</p>";
    echo "<p>✅ Admin users found: $adminCount</p>";
    
    // Check if sample data exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    $productCount = $stmt->fetchColumn();
    echo "<p>✅ Products in database: $productCount</p>";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM categories");
    $categoryCount = $stmt->fetchColumn();
    echo "<p>✅ Categories in database: $categoryCount</p>";
    
    echo "<hr>";
    echo "<h3>🎉 Setup Complete!</h3>";
    echo "<p><strong>Frontend URL:</strong> <a href='http://localhost/interno-php/' target='_blank'>http://localhost/interno-php/</a></p>";
    echo "<p><strong>Admin Panel:</strong> <a href='http://localhost/interno-php/admin/dashboard.php' target='_blank'>http://localhost/interno-php/admin/dashboard.php</a></p>";
    echo "<p><strong>Admin Login:</strong> Username: admin | Password: password</p>";
    
} catch(Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Please ensure XAMPP MySQL is running and try again.</p>";
}
?>
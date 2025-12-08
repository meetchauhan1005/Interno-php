<?php
// Database Repair Script
require_once 'includes/config.php';

echo "<h2>Database Repair</h2>";

try {
    // Check if orders table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'orders'");
    if ($stmt->rowCount() == 0) {
        echo "<p>Creating orders table...</p>";
        $pdo->exec("
            CREATE TABLE `orders` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `user_id` int(11),
              `total_amount` decimal(10,2) NOT NULL,
              `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
              `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `user_id` (`user_id`),
              CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        echo "<p>✅ Orders table created</p>";
    } else {
        echo "<p>✅ Orders table exists</p>";
    }
    
    echo "<p><strong>Database repair complete!</strong></p>";
    echo "<p><a href='admin/dashboard.php'>Go to Admin Dashboard</a></p>";
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
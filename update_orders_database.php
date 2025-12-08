<?php
require_once 'includes/config.php';

try {
    // Create order_items table
    $sql = "CREATE TABLE IF NOT EXISTS `order_items` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `order_id` int(11) NOT NULL,
        `product_id` int(11) NOT NULL,
        `product_name` varchar(255) NOT NULL,
        `quantity` int(11) NOT NULL DEFAULT 1,
        `price` decimal(10,2) NOT NULL,
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `order_id` (`order_id`),
        KEY `product_id` (`product_id`),
        CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
        CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $pdo->exec($sql);

    // Add missing columns to orders table
    try {
        $pdo->exec("ALTER TABLE orders ADD COLUMN shipping_address TEXT");
    } catch (PDOException $e) {}
    
    try {
        $pdo->exec("ALTER TABLE orders ADD COLUMN tracking_number VARCHAR(100)");
    } catch (PDOException $e) {}
    
    try {
        $pdo->exec("ALTER TABLE orders ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    } catch (PDOException $e) {}

    // Insert sample order items if none exist
    $stmt = $pdo->query("SELECT COUNT(*) FROM order_items");
    if ($stmt->fetchColumn() == 0) {
        $orderItems = [
            [1, 1, 'King Size Platform Bed', 1, 35000.00],
            [2, 3, 'Velvet Curved Sectional Sofa', 1, 54000.00],
            [3, 5, 'Glass Top Dining Table', 1, 18000.00]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?)");
        foreach ($orderItems as $item) {
            $stmt->execute($item);
        }
    }

    echo "Database updated successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
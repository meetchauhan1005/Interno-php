<?php
require_once 'includes/config.php';

// Define the categories we want
$categories = [
    ['name' => 'Bedroom', 'description' => 'Beds, wardrobes, and bedroom furniture'],
    ['name' => 'Office', 'description' => 'Desks, chairs, and office furniture'],
    ['name' => 'Sofas & Chairs', 'description' => 'Comfortable seating furniture for your living room'],
    ['name' => 'Storage', 'description' => 'Cabinets, shelves, and storage solutions'],
    ['name' => 'Tables', 'description' => 'Dining tables, coffee tables, and more']
];

try {
    // Clear existing categories
    $pdo->exec("DELETE FROM categories");
    
    // Insert new categories
    $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
    
    foreach ($categories as $category) {
        $stmt->execute([$category['name'], $category['description']]);
    }
    
    echo "Categories updated successfully!";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
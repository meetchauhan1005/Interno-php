<?php
require_once 'includes/config.php';

try {
    // Create product_reviews table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS product_reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        user_id INT NULL,
        rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
        comment TEXT NOT NULL,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )";
    
    $pdo->exec($sql);
    
    // Insert some sample reviews for testing
    $sampleReviews = [
        [
            'product_id' => 1,
            'user_id' => null,
            'rating' => 5,
            'comment' => 'Excellent product! Very comfortable and stylish. Highly recommended.',
            'status' => 'approved'
        ],
        [
            'product_id' => 1,
            'user_id' => null,
            'rating' => 4,
            'comment' => 'Good quality furniture. Assembly was a bit challenging but worth it.',
            'status' => 'approved'
        ],
        [
            'product_id' => 1,
            'user_id' => null,
            'rating' => 5,
            'comment' => 'Amazing design and very comfortable. Perfect for my living room.',
            'status' => 'approved'
        ]
    ];
    
    // Check if reviews already exist
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM product_reviews WHERE product_id = 1");
    $stmt->execute();
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        foreach ($sampleReviews as $review) {
            $stmt = $pdo->prepare("INSERT INTO product_reviews (product_id, user_id, rating, comment, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $review['product_id'],
                $review['user_id'],
                $review['rating'],
                $review['comment'],
                $review['status']
            ]);
        }
        echo "Sample reviews added successfully!<br>";
    }
    
    echo "Product reviews table created successfully!<br>";
    echo "<a href='product_detail.php?id=1'>View Product with Reviews</a>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
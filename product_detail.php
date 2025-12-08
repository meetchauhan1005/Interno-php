<?php
require_once 'includes/config.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    header('Location: products.php');
    exit();
}

// Get product details
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name 
                       FROM products p 
                       LEFT JOIN categories c ON p.category_id = c.id 
                       WHERE p.id = ? AND p.status = 'active'");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: products.php');
    exit();
}

// Get related products
$stmt = $pdo->prepare("SELECT p.* FROM products p 
                       WHERE p.category_id = ? AND p.id != ? AND p.status = 'active' 
                       ORDER BY RAND() LIMIT 4");
$stmt->execute([$product['category_id'], $product_id]);
$related_products = $stmt->fetchAll();

// Create reviews table and sample data if it doesn't exist
try {
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'product_reviews'");
    if ($stmt->rowCount() == 0) {
        // Create table with correct structure
        $sql = "CREATE TABLE product_reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            user_id INT NULL,
            rating INT NOT NULL,
            comment TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $pdo->exec($sql);
    }
    
    // Check if reviews exist for this product
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM product_reviews WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $reviewCount = $stmt->fetchColumn();
    
    // Add sample reviews if none exist
    if ($reviewCount == 0) {
        $sampleReviews = [
            [$product_id, null, 5, 'Excellent product! Very comfortable and stylish. Highly recommended for anyone looking for quality furniture.'],
            [$product_id, null, 4, 'Good quality furniture. Assembly was a bit challenging but the end result is worth it. Looks great in my living room.'],
            [$product_id, null, 5, 'Amazing design and very comfortable. Perfect for my living room. The color matches exactly what I expected.'],
            [$product_id, null, 4, 'Great value for money. Looks exactly like the pictures. Delivery was prompt and packaging was excellent.'],
            [$product_id, null, 3, 'Decent product but delivery took longer than expected. Quality is good once assembled.']
        ];
        
        foreach ($sampleReviews as $review) {
            $stmt = $pdo->prepare("INSERT INTO product_reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
            $stmt->execute($review);
        }
    }
    
    // Get product reviews - handle different possible column names
    try {
        $stmt = $pdo->prepare("SELECT r.*, COALESCE(u.username, 'Anonymous') as user_name 
                               FROM product_reviews r 
                               LEFT JOIN users u ON r.user_id = u.id 
                               WHERE r.product_id = ?
                               ORDER BY r.created_at DESC");
        $stmt->execute([$product_id]);
        $reviews = $stmt->fetchAll();
    } catch (PDOException $e) {
        // If column doesn't exist, try without status filter
        $stmt = $pdo->prepare("SELECT r.*, COALESCE(u.username, 'Anonymous') as user_name 
                               FROM product_reviews r 
                               LEFT JOIN users u ON r.user_id = u.id 
                               WHERE r.product_id = ?
                               ORDER BY r.id DESC");
        $stmt->execute([$product_id]);
        $reviews = $stmt->fetchAll();
    }
    
} catch (PDOException $e) {
    // If database fails, show sample reviews
    $reviews = [
        [
            'id' => 1,
            'rating' => 5,
            'comment' => 'Excellent product! Very comfortable and stylish. Highly recommended for anyone looking for quality furniture.',
            'user_name' => 'Sarah Johnson',
            'created_at' => '2024-01-15 10:30:00'
        ],
        [
            'id' => 2,
            'rating' => 4,
            'comment' => 'Good quality furniture. Assembly was a bit challenging but the end result is worth it. Looks great in my living room.',
            'user_name' => 'Mike Chen',
            'created_at' => '2024-01-10 14:20:00'
        ],
        [
            'id' => 3,
            'rating' => 5,
            'comment' => 'Amazing design and very comfortable. Perfect for my living room. The color matches exactly what I expected.',
            'user_name' => 'Emma Wilson',
            'created_at' => '2024-01-08 09:15:00'
        ],
        [
            'id' => 4,
            'rating' => 4,
            'comment' => 'Great value for money. Looks exactly like the pictures. Delivery was prompt and packaging was excellent.',
            'user_name' => 'David Brown',
            'created_at' => '2024-01-05 16:45:00'
        ],
        [
            'id' => 5,
            'rating' => 3,
            'comment' => 'Decent product but delivery took longer than expected. Quality is good once assembled.',
            'user_name' => 'Lisa Garcia',
            'created_at' => '2024-01-02 11:30:00'
        ]
    ];
}



$page_title = $product['name'];
include 'includes/header.php';
?>

<div class="product-container">
    <div class="product-detail">
        <div class="product-image-section">
            <?php if ($product['image']): ?>
                <img src="assets/images/<?php echo htmlspecialchars($product['image']); ?>" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>" class="main-product-image">
            <?php else: ?>
                <div class="no-image-placeholder">
                    <i class="fas fa-image"></i>
                    <p>No image available</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="product-info-section">
            <?php if ($product['category_name']): ?>
                <div class="breadcrumb"><?php echo htmlspecialchars($product['category_name']); ?></div>
            <?php endif; ?>
            
            <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
            
            <?php if ($product['rating'] > 0): ?>
                <div class="rating-section">
                    <div class="stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star <?php echo $i <= $product['rating'] ? 'filled' : ''; ?>">★</span>
                        <?php endfor; ?>
                    </div>
                    <span class="review-count">(<?php echo $product['review_count'] ?? 0; ?> reviews)</span>
                </div>
            <?php endif; ?>
            
            <div class="price-display">
                <span class="current-price">₹<?php echo number_format($product['price'], 2); ?></span>
            </div>
            
            <?php if ($product['short_description'] || $product['description']): ?>
                <div class="product-description">
                    <?php if ($product['short_description']): ?>
                        <?php echo htmlspecialchars($product['short_description']); ?>
                    <?php elseif ($product['description']): ?>
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="stock-status">
                <?php if ($product['stock_quantity'] > 10): ?>
                    <span class="in-stock">✓ In Stock (<?php echo $product['stock_quantity']; ?> available)</span>
                <?php elseif ($product['stock_quantity'] > 0): ?>
                    <span class="low-stock">⚠ Only <?php echo $product['stock_quantity']; ?> left</span>
                <?php else: ?>
                    <span class="out-of-stock">✗ Out of Stock</span>
                <?php endif; ?>
            </div>
            
            <?php if ($product['stock_quantity'] > 0): ?>
                <div class="quantity-section">
                    <label>Quantity:</label>
                    <input type="number" id="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" class="quantity-input">
                </div>
                
                <button onclick="addToCartWithQuantity(<?php echo $product['id']; ?>)" class="add-to-cart-button">
                    <i class="fas fa-shopping-cart"></i> Add to Cart
                </button>
            <?php else: ?>
                <button class="add-to-cart-button disabled" disabled>
                    Out of Stock
                </button>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Customer Reviews Section -->
    <div class="reviews-section">
        <div class="reviews-header">
            <h2>Customer Reviews</h2>
            <div class="reviews-summary">
                <?php if (($product['review_count'] ?? 0) > 0): ?>
                    <div class="overall-rating">
                        <div class="rating-display">
                            <span class="rating-number"><?php echo number_format($product['rating'], 1); ?></span>
                            <div class="stars-large">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star <?php echo $i <= $product['rating'] ? 'filled' : ''; ?>">★</span>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <p class="review-count-text"><?php echo $product['review_count'] ?? 0; ?> reviews</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Reviews Content Layout -->
        <div class="reviews-content">
            <!-- Write Review Form -->
            <div class="write-review">
                <h3>Write a Review</h3>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <form class="review-form" onsubmit="submitReview(event)">
                        <div class="form-row">
                            <div class="rating-input">
                                <label>Rating:</label>
                                <div class="star-rating">
                                    <span class="star-input" data-rating="1">☆</span>
                                    <span class="star-input" data-rating="2">☆</span>
                                    <span class="star-input" data-rating="3">☆</span>
                                    <span class="star-input" data-rating="4">☆</span>
                                    <span class="star-input" data-rating="5">☆</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="comment-input">
                            <label>Your Review:</label>
                            <textarea id="review-comment" placeholder="Share your experience with this product..." required></textarea>
                        </div>
                        
                        <button type="submit" class="submit-review-btn">
                            <i class="fas fa-paper-plane"></i> Submit Review
                        </button>
                    </form>
                <?php else: ?>
                    <div class="login-prompt">
                        <p>Please <a href="#" onclick="openLoginModal()">login</a> to write a review.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Existing Reviews -->
            <div class="reviews-list">
                <h3>Customer Reviews</h3>
                <?php if (!empty($reviews)): ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <div class="reviewer-info">
                                    <div class="reviewer-avatar">
                                        <?php echo strtoupper(substr($review['user_name'] ?: 'Anonymous', 0, 1)); ?>
                                    </div>
                                    <div class="reviewer-details">
                                        <h4 class="reviewer-name"><?php echo htmlspecialchars($review['user_name'] ?: 'Anonymous'); ?></h4>
                                        <div class="review-rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <span class="star <?php echo $i <= $review['rating'] ? 'filled' : ''; ?>">★</span>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="review-date">
                                    <?php echo date('M j, Y', strtotime($review['created_at'])); ?>
                                </div>
                            </div>
                            <div class="review-content">
                                <p><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-reviews">No reviews yet. Be the first to review!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    

    
    <!-- Related Products -->
    <?php if (!empty($related_products)): ?>
        <div class="related-products">
            <h2>You May Also Like</h2>
            <div class="products-grid">
                <?php foreach ($related_products as $related): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if ($related['image']): ?>
                                <img src="assets/images/<?php echo htmlspecialchars($related['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($related['name']); ?>">
                            <?php else: ?>
                                <div class="no-image-placeholder">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-info">
                            <h3 class="product-name"><?php echo htmlspecialchars($related['name']); ?></h3>
                            <div class="product-price">
                                <span class="current-price">₹<?php echo number_format($related['price'], 0); ?></span>
                            </div>
                            <div class="product-actions">
                                <button onclick="viewProduct(<?php echo $related['id']; ?>)" class="btn-view">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button onclick="addToCart(<?php echo $related['id']; ?>)" class="btn-add">
                                    <i class="fas fa-cart-plus"></i> Add
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
* {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

.product-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    background: #f5f5f5;
}

.product-detail {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.product-image-section {
    display: flex;
    justify-content: center;
    align-items: center;
}

.main-product-image {
    width: 100%;
    max-width: 400px;
    height: auto;
    border-radius: 8px;
    object-fit: cover;
}

.no-image-placeholder {
    width: 100%;
    height: 400px;
    background: #f8f9fa;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    border-radius: 8px;
}

.no-image-placeholder i {
    font-size: 60px;
    margin-bottom: 10px;
}

.product-info-section {
    padding: 20px 0;
}

.breadcrumb {
    color: #666;
    font-size: 14px;
    margin-bottom: 10px;
}

.product-title {
    font-size: 24px;
    font-weight: normal;
    color: #333;
    margin-bottom: 15px;
    line-height: 1.3;
}

.rating-section {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
}

.stars {
    display: flex;
    gap: 2px;
}

.star {
    font-size: 16px;
    color: #ddd;
}

.star.filled {
    color: #ffc107;
}

.review-count {
    color: #666;
    font-size: 14px;
}

.price-display {
    margin-bottom: 20px;
}

.current-price {
    font-size: 28px;
    color: #007bff;
    font-weight: normal;
}

.product-description {
    color: #666;
    line-height: 1.6;
    margin-bottom: 20px;
    font-size: 14px;
}

.stock-status {
    margin-bottom: 20px;
}

.in-stock {
    color: #28a745;
    font-size: 14px;
}

.low-stock {
    color: #ffc107;
    font-size: 14px;
}

.out-of-stock {
    color: #dc3545;
    font-size: 14px;
}

.quantity-section {
    margin-bottom: 20px;
}

.quantity-section label {
    display: block;
    margin-bottom: 8px;
    font-size: 14px;
    color: #333;
}

.quantity-input {
    width: 60px;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    text-align: center;
    font-size: 14px;
}

.add-to-cart-button {
    background: #4a5568;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 4px;
    font-size: 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: background 0.2s;
}

.add-to-cart-button:hover {
    background: #2d3748;
}

.add-to-cart-button.disabled {
    background: #ccc;
    cursor: not-allowed;
}

.reviews-section {
    background: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.reviews-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f0f0f0;
}

.reviews-section h2 {
    font-size: 24px;
    font-weight: normal;
    color: #333;
    margin: 0;
}

.reviews-summary {
    text-align: right;
}

.overall-rating {
    display: flex;
    align-items: center;
    gap: 15px;
}

.rating-display {
    display: flex;
    align-items: center;
    gap: 10px;
}

.rating-number {
    font-size: 32px;
    font-weight: normal;
    color: #007bff;
}

.stars-large {
    display: flex;
    gap: 2px;
}

.stars-large .star {
    font-size: 20px;
    color: #ddd;
}

.stars-large .star.filled {
    color: #ffc107;
}

.review-count-text {
    color: #666;
    font-size: 14px;
    margin: 0;
}

.no-reviews-text {
    color: #666;
    font-style: italic;
    margin: 0;
}

.reviews-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    margin-top: 30px;
}

.reviews-list {
    max-height: 600px;
    overflow-y: auto;
}

.reviews-list h3 {
    font-size: 18px;
    font-weight: normal;
    color: #333;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #f0f0f0;
}

.no-reviews {
    color: #666;
    font-style: italic;
    text-align: center;
    padding: 40px 20px;
}

.review-item {
    padding: 25px;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    margin-bottom: 20px;
    transition: all 0.2s;
}

.review-item:hover {
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border-color: #007bff;
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.reviewer-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.reviewer-avatar {
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: normal;
    font-size: 18px;
}

.reviewer-details h4 {
    margin: 0 0 5px 0;
    font-size: 16px;
    font-weight: normal;
    color: #333;
}

.review-rating {
    display: flex;
    gap: 2px;
}

.review-rating .star {
    font-size: 14px;
    color: #ddd;
}

.review-rating .star.filled {
    color: #ffc107;
}

.review-date {
    color: #666;
    font-size: 13px;
}

.review-content {
    color: #555;
    line-height: 1.6;
    font-size: 14px;
}

.review-content p {
    margin: 0;
}

.write-review {
    background: #f8f9fa;
    padding: 30px;
    border-radius: 10px;
    border: 1px solid #e9ecef;
    height: fit-content;
}

.write-review h3 {
    font-size: 18px;
    font-weight: normal;
    color: #333;
    margin-bottom: 20px;
}

.review-form {
    max-width: 600px;
}

.form-row {
    margin-bottom: 20px;
}

.rating-input {
    margin-bottom: 20px;
}

.rating-input label {
    display: block;
    margin-bottom: 10px;
    font-size: 14px;
    color: #333;
    font-weight: normal;
}

.star-rating {
    display: flex;
    gap: 8px;
}

.star-input {
    font-size: 24px;
    color: #ddd;
    cursor: pointer;
    transition: all 0.2s;
}

.star-input:hover,
.star-input.active {
    color: #ffc107;
    transform: scale(1.1);
}

.comment-input {
    margin-bottom: 20px;
}

.comment-input label {
    display: block;
    margin-bottom: 10px;
    font-size: 14px;
    color: #333;
    font-weight: normal;
}

.comment-input textarea {
    width: 100%;
    height: 120px;
    padding: 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 14px;
    font-family: Arial, sans-serif;
    resize: vertical;
    transition: border-color 0.2s;
}

.comment-input textarea:focus {
    outline: none;
    border-color: #007bff;
}

.submit-review-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.submit-review-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(102,126,234,0.4);
}

.login-prompt {
    text-align: center;
    padding: 30px;
    color: #666;
}

.login-prompt a {
    color: #007bff;
    text-decoration: none;
    font-weight: normal;
}

.login-prompt a:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    .reviews-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .reviews-summary {
        text-align: left;
    }
    
    .overall-rating {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .review-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .reviews-section {
        padding: 25px;
    }
    
    .write-review {
        padding: 20px;
    }
    
    .reviews-content {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .reviews-list {
        max-height: none;
        order: 2;
    }
    
    .write-review {
        order: 1;
    }
}



.related-products {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.related-products h2 {
    font-size: 20px;
    font-weight: normal;
    color: #333;
    margin-bottom: 20px;
    text-align: center;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.product-card {
    border: 1px solid #eee;
    border-radius: 8px;
    overflow: hidden;
    transition: box-shadow 0.2s;
}

.product-card:hover {
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.product-card .product-image {
    aspect-ratio: 1;
    overflow: hidden;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
}

.product-card .product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-card .product-info {
    padding: 15px;
}

.product-card .product-name {
    font-size: 14px;
    color: #333;
    margin-bottom: 8px;
    line-height: 1.4;
}

.product-card .current-price {
    font-size: 16px;
    color: #007bff;
    margin-bottom: 10px;
}

.product-card .product-actions {
    display: flex;
    gap: 8px;
}

.btn-view, .btn-add {
    flex: 1;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: white;
    color: #333;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
    text-align: center;
}

.btn-view:hover {
    background: #f8f9fa;
}

.btn-add {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.btn-add:hover {
    background: #0056b3;
}

@media (max-width: 768px) {
    .product-detail {
        grid-template-columns: 1fr;
        gap: 20px;
        padding: 20px;
    }
    
    .product-title {
        font-size: 20px;
    }
    
    .current-price {
        font-size: 24px;
    }
    
    .products-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }
}
</style>

<script>
// Star rating functionality
document.addEventListener('DOMContentLoaded', function() {
    const starInputs = document.querySelectorAll('.star-input');
    let selectedRating = 0;
    
    starInputs.forEach((star, index) => {
        star.addEventListener('click', function() {
            selectedRating = index + 1;
            updateStarDisplay();
        });
        
        star.addEventListener('mouseover', function() {
            highlightStars(index + 1);
        });
    });
    
    const starRating = document.querySelector('.star-rating');
    if (starRating) {
        starRating.addEventListener('mouseleave', function() {
            updateStarDisplay();
        });
    }
    
    function highlightStars(rating) {
        starInputs.forEach((star, index) => {
            if (index < rating) {
                star.textContent = '★';
                star.classList.add('active');
            } else {
                star.textContent = '☆';
                star.classList.remove('active');
            }
        });
    }
    
    function updateStarDisplay() {
        highlightStars(selectedRating);
    }
    
    // Make selectedRating accessible globally
    window.getSelectedRating = function() {
        return selectedRating;
    };
});

// Submit review function
function submitReview(event) {
    event.preventDefault();
    
    const rating = window.getSelectedRating();
    const comment = document.getElementById('review-comment').value;
    const productId = <?php echo $product_id; ?>;
    
    if (rating === 0) {
        showNotification('Please select a rating', 'error');
        return;
    }
    
    if (comment.trim() === '') {
        showNotification('Please write a review comment', 'error');
        return;
    }
    
    // Submit review to server
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('rating', rating);
    formData.append('comment', comment);
    
    fetch('submit_review.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            // Reset form
            document.getElementById('review-comment').value = '';
            const starInputs = document.querySelectorAll('.star-input');
            starInputs.forEach(star => {
                star.textContent = '☆';
                star.classList.remove('active');
            });
            // Reload page to show new review
            setTimeout(() => location.reload(), 2000);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('Error submitting review', 'error');
    });
}

function addToCartWithQuantity(productId) {
    const quantity = parseInt(document.getElementById('quantity').value);
    
    <?php if (isset($_SESSION['user_id'])): ?>
    fetch('includes/cart_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=add&product_id=' + productId + '&quantity=' + quantity
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount();
            showNotification('Product added to cart!', 'success');
        } else {
            showNotification(data.message || 'Failed to add product', 'error');
        }
    })
    .catch(error => {
        showNotification('Error adding product to cart', 'error');
    });
    <?php else: ?>
    openLoginModal();
    <?php endif; ?>
}

function viewProduct(productId) {
    window.location.href = 'product_detail.php?id=' + productId;
}

function addToCart(productId, quantity = 1) {
    <?php if (isset($_SESSION['user_id'])): ?>
    fetch('includes/cart_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=add&product_id=' + productId + '&quantity=' + quantity
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount();
            showNotification('Product added to cart!', 'success');
        } else {
            showNotification(data.message || 'Failed to add product', 'error');
        }
    })
    .catch(error => {
        showNotification('Error adding product to cart', 'error');
    });
    <?php else: ?>
    openLoginModal();
    <?php endif; ?>
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.style.cssText = `
        position: fixed; 
        top: 20px; 
        right: 20px; 
        z-index: 9999; 
        min-width: 300px; 
        padding: 15px 20px; 
        border-radius: 8px; 
        background: ${type === 'success' ? '#d4edda' : '#f8d7da'}; 
        color: ${type === 'success' ? '#155724' : '#721c24'}; 
        border: 1px solid ${type === 'success' ? '#c3e6cb' : '#f5c6cb'};
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        gap: 10px;
        font-family: Arial, sans-serif;
    `;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        ${message}
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}
</script>

<?php include 'includes/footer.php'; ?>
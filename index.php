<?php
require_once 'includes/config.php';
$page_title = 'Home';
include 'includes/header.php';

// Get featured products
try {
    $stmt = $pdo->query("SELECT * FROM products WHERE is_featured = 1 AND status = 'active' ORDER BY created_at DESC LIMIT 6");
    $featured_products = $stmt->fetchAll();
    
    // If no featured products, get latest products
    if (empty($featured_products)) {
        $stmt = $pdo->query("SELECT * FROM products WHERE status = 'active' ORDER BY created_at DESC LIMIT 6");
        $featured_products = $stmt->fetchAll();
    }
    
    $stmt = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order, name LIMIT 6");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $featured_products = [];
    $categories = [];
}
?>

<section class="hero">
    <div class="hero-content">
        <h1>Make Your Home Beautiful</h1>
        <p>Transform your living space with our premium collection of modern furniture designed for Indian homes</p>
        <div class="hero-buttons">
            <a href="products.php" class="btn-shop">
                <i class="fas fa-shopping-bag"></i> Shop Collection
            </a>
            <a href="about.php" class="btn-learn">
                <i class="fas fa-play"></i> Learn More
            </a>
        </div>
    </div>
</section>

<style>
.hero {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: white;
    padding: 80px 20px;
    position: relative;
    overflow: hidden;
}

.hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="%23ffffff" opacity="0.1"/><circle cx="80" cy="40" r="1" fill="%23ffffff" opacity="0.15"/><circle cx="40" cy="80" r="1.5" fill="%23ffffff" opacity="0.1"/></svg>');
    animation: float 20s ease-in-out infinite;
}

.hero-content {
    max-width: 800px;
    margin: 0 auto;
    position: relative;
    z-index: 2;
}

.hero h1 {
    font-size: 4rem;
    font-weight: 800;
    margin-bottom: 24px;
    line-height: 1.1;
    text-shadow: 0 2px 10px rgba(0,0,0,0.1);
    animation: slideUp 1s ease-out;
}

.hero p {
    font-size: 1.3rem;
    margin-bottom: 40px;
    opacity: 0.9;
    line-height: 1.6;
    animation: slideUp 1s ease-out 0.2s both;
}

.hero-buttons {
    display: flex;
    gap: 20px;
    justify-content: center;
    flex-wrap: wrap;
    animation: slideUp 1s ease-out 0.4s both;
}

.btn-shop {
    background: white;
    color: #333;
    padding: 16px 32px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    position: relative;
    overflow: hidden;
}

.btn-shop::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    transition: left 0.5s;
}

.btn-shop:hover::before {
    left: 100%;
}

.btn-shop:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.2);
}

.btn-learn {
    background: rgba(255,255,255,0.1);
    color: white;
    padding: 16px 32px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
    border: 2px solid rgba(255,255,255,0.3);
    backdrop-filter: blur(10px);
    position: relative;
}

.btn-learn:hover {
    background: rgba(255,255,255,0.2);
    transform: translateY(-3px);
    border-color: rgba(255,255,255,0.5);
    box-shadow: 0 8px 25px rgba(255,255,255,0.1);
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-10px) rotate(180deg); }
}

@media (max-width: 768px) {
    .hero h1 {
        font-size: 2.5rem;
    }
    
    .hero p {
        font-size: 1.1rem;
    }
    
    .hero-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .btn-shop, .btn-learn {
        width: 250px;
        justify-content: center;
    }
}
</style>

<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title"><strong>Why Choose INTERNO</strong></h2>
            <p class="section-subtitle"><strong>Experience the difference with our premium furniture and exceptional service</strong></p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-truck"></i>
                </div>
                <h3>Free Delivery</h3>
                <p>Complimentary shipping on all orders over ₹5,000 across India</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Quality Guarantee</h3>
                <p>Premium materials with comprehensive warranty and quality assurance</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-headset"></i>
                </div>
                <h3>24/7 Support</h3>
                <p>Expert customer service available round the clock for assistance</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-undo"></i>
                </div>
                <h3>Easy Returns</h3>
                <p>Hassle-free 30-day return policy for your peace of mind</p>
            </div>
        </div>
    </div>
</section>

<section class="categories-section">
    <div class="container">
        <div class="categories-header">
            <h2 class="categories-title">Shop by Category</h2>
            <p class="categories-subtitle">Find the perfect furniture for every room in your home</p>
        </div>
        <div class="categories-container">
            <?php
            $category_icons = [
                'bedroom' => 'fas fa-bed',
                'office' => 'fas fa-briefcase', 
                'sofa-chairs' => 'fas fa-couch',
                'storage' => 'fas fa-archive',
                'tables' => 'fas fa-utensils'
            ];
            
            foreach (array_slice($categories, 0, 5) as $category):
                $icon = $category_icons[$category['slug']] ?? 'fas fa-couch';
            ?>
            <a href="products.php?category=<?php echo $category['id']; ?>" class="category-item">
                <div class="category-icon-wrapper">
                    <i class="<?php echo $icon; ?>"></i>
                </div>
                <div class="category-content">
                    <h3 class="category-name"><?php echo htmlspecialchars($category['name']); ?></h3>
                    <p class="category-desc"><?php echo htmlspecialchars($category['description'] ?: 'Quality furniture collection'); ?></p>
                </div>
                <div class="category-arrow">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <div class="categories-footer">
            <a href="categories.php" class="view-categories-btn">
                View All Categories
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>



<!-- Featured Products Section -->
<section class="featured-products-premium">
    <div class="container">
        <div class="featured-header">
            <div class="featured-title-wrapper">
                <span class="featured-icon">⭐</span>
                <h2 class="featured-title">Featured Products</h2>
                <div class="featured-subtitle">Handpicked premium furniture for your perfect space</div>
            </div>
        </div>
        
        <?php if (!empty($featured_products)): ?>
            <div class="featured-products-grid">
                <?php foreach ($featured_products as $product): ?>
                    <div class="featured-product-card" onclick="window.location.href='product_detail.php?id=<?php echo $product['id']; ?>'">
                        <div class="product-badges">
                            <span class="featured-badge">
                                <i class="fas fa-star"></i>
                                Featured
                            </span>
                            <?php if (isset($product['original_price']) && $product['original_price'] > $product['price']): ?>
                                <span class="discount-badge">
                                    <?php echo round((($product['original_price'] - $product['price']) / $product['original_price']) * 100); ?>% OFF
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-image-container">
                            <?php if ($product['image']): ?>
                                <img src="assets/images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php else: ?>
                                <div class="image-placeholder">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>

                        </div>
                        
                        <div class="product-details">
                            <div class="product-category"><?php echo htmlspecialchars($product['category_name'] ?? 'Furniture'); ?></div>
                            <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="product-description"><?php echo htmlspecialchars(substr($product['description'] ?? 'Premium quality furniture piece designed for modern living spaces.', 0, 80)); ?>...</p>
                            
                            <div class="product-rating">
                                <div class="stars">
                                    <?php 
                                    $rating = $product['rating'] ?? 4.5;
                                    for ($i = 1; $i <= 5; $i++): 
                                    ?>
                                        <i class="fas fa-star<?php echo $i <= $rating ? '' : ' star-empty'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="rating-value"><?php echo number_format($rating, 1); ?></span>
                                <span class="review-count">(<?php echo $product['reviews'] ?? rand(15, 89); ?> reviews)</span>
                            </div>
                            
                            <div class="product-pricing">
                                <div class="price-row">
                                    <span class="current-price">₹<?php echo number_format($product['price'], 2); ?></span>
                                    <?php if (isset($product['original_price']) && $product['original_price'] > $product['price']): ?>
                                        <span class="original-price">₹<?php echo number_format($product['original_price'], 2); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="savings">
                                    <?php if (isset($product['original_price']) && $product['original_price'] > $product['price']): ?>
                                        You save ₹<?php echo number_format($product['original_price'] - $product['price'], 2); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="product-features">
                                <div class="feature-item">
                                    <i class="fas fa-truck"></i>
                                    <span>Free Delivery</span>
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-shield-alt"></i>
                                    <span>2 Year Warranty</span>
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-undo"></i>
                                    <span>Easy Returns</span>
                                </div>
                            </div>
                            
                            <div class="product-actions">
                                <button class="add-to-cart-primary" onclick="event.stopPropagation(); addToCart(<?php echo $product['id']; ?>)">
                                    <i class="fas fa-shopping-cart"></i>
                                    Add to Cart
                                </button>
                                <button class="buy-now-btn" onclick="event.stopPropagation(); buyNow(<?php echo $product['id']; ?>)">
                                    <i class="fas fa-bolt"></i>
                                    Buy Now
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-featured-products">
                <div class="no-products-icon">
                    <i class="fas fa-star"></i>
                </div>
                <h3>No Featured Products Available</h3>
                <p>Our team is curating amazing products for you. Check back soon!</p>
                <a href="products.php" class="btn btn-primary">
                    <i class="fas fa-th-large"></i>
                    Browse All Products
                </a>
            </div>
        <?php endif; ?>
        
        <div class="featured-footer">
            <a href="products.php" class="view-all-btn">
                <span>View All Products</span>
                <i class="fas fa-arrow-right"></i>
            </a>
            <a href="products.php?featured=1" class="more-featured-btn">
                <i class="fas fa-star"></i>
                <span>More Featured Items</span>
            </a>
        </div>
    </div>
</section>

<style>
.product-card-modern {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(0,0,0,0.08);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    position: relative;
}

.product-card-modern:hover {
    transform: translateY(-12px);
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}

.product-image-container {
    position: relative;
    height: 280px;
    overflow: hidden;
    background: linear-gradient(135deg, #f8fafc, #e2e8f0);
}

.product-image-container img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    transition: transform 0.4s ease;
}

.product-card-modern:hover .product-image-container img {
    transform: scale(1.08);
}

.no-image-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4rem;
    color: #cbd5e1;
}

.product-overlay {
    position: absolute;
    top: 16px;
    right: 16px;
    opacity: 0;
    transition: all 0.3s ease;
}

.product-card-modern:hover .product-overlay {
    opacity: 1;
}

.quick-add-btn {
    width: 48px;
    height: 48px;
    background: rgba(255,255,255,0.95);
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    backdrop-filter: blur(10px);
}

.quick-add-btn:hover {
    background: #6366f1;
    color: white;
    transform: scale(1.1);
}

.product-info-modern {
    padding: 24px;
    text-align: center;
}

.product-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 12px 0;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.product-price-modern {
    font-size: 1.4rem;
    font-weight: 700;
    color: #6366f1;
    margin-bottom: 12px;
}

.product-rating {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.stars {
    display: flex;
    gap: 2px;
}

.stars i {
    color: #fbbf24;
    font-size: 0.9rem;
}

.rating-count {
    font-size: 0.85rem;
    color: #6b7280;
}

.products-slider {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 32px;
    margin-top: 40px;
}

@media (max-width: 768px) {
    .products-slider {
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 24px;
    }
    
    .product-image-container {
        height: 240px;
    }
    
    .product-info-modern {
        padding: 20px;
    }
}
</style>



<script>
function addToCart(productId) {
    <?php if (isset($_SESSION['user_id'])): ?>
    fetch('includes/cart_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=add&product_id=' + productId + '&quantity=1'
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





function buyNow(productId) {
    addToCart(productId);
    setTimeout(() => {
        window.location.href = 'user/cart.php';
    }, 500);
}







function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'}`;
    notification.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        ${message}
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>

<?php include 'includes/footer.php'; ?>
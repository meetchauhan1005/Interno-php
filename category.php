<?php
require_once 'includes/config.php';

// Get category ID from URL
$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$category_id) {
    header('Location: categories.php');
    exit();
}

// Get category details
try {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$category_id]);
    $category = $stmt->fetch();
    
    if (!$category) {
        header('Location: categories.php');
        exit();
    }
    
    // Get products in this category
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND status = 'active' ORDER BY name ASC");
    $stmt->execute([$category_id]);
    $products = $stmt->fetchAll();
    
} catch (PDOException $e) {
    header('Location: categories.php');
    exit();
}

$page_title = $category['name'];
include 'includes/header.php';
?>

<section class="section" style="padding-top: 2rem;">
    <div class="container">
        <div class="category-header">
            <nav class="breadcrumb">
                <a href="index.php">Home</a>
                <span>/</span>
                <a href="categories.php">Categories</a>
                <span>/</span>
                <span><?php echo htmlspecialchars($category['name']); ?></span>
            </nav>
            
            <div class="section-header">
                <h1 class="section-title"><?php echo htmlspecialchars($category['name']); ?></h1>
                <p class="section-subtitle">Explore our <?php echo strtolower($category['name']); ?> collection</p>
            </div>
        </div>
        
        <?php if (empty($products)): ?>
            <div class="no-products">
                <i class="fas fa-box-open"></i>
                <h3>No products available</h3>
                <p>We're working on adding products to this category</p>
                <a href="categories.php" class="btn btn-primary">Browse Other Categories</a>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                <div class="product-card" onclick="window.location.href='product_detail.php?id=<?php echo $product['id']; ?>'">
                    <?php if ($product['featured']): ?>
                        <div class="product-badge featured">Featured</div>
                    <?php endif; ?>
                    <div class="product-image">
                        <?php if (!empty($product['image'])): ?>
                            <img src="assets/images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <?php else: ?>
                            <div class="placeholder-image">
                                <i class="fas fa-couch"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <div class="product-brand">INTERNO</div>
                        <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <div class="product-price">
                            <span class="current-price">₹<?php echo number_format($product['price'], 2); ?></span>
                        </div>
                        <div class="product-actions">
                            <button class="btn btn-primary flex-1" onclick="event.stopPropagation(); addToCart(<?php echo $product['id']; ?>)">
                                <i class="fas fa-shopping-cart"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
.category-header {
    margin-bottom: 3rem;
}

.breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
}

.breadcrumb a {
    color: var(--primary);
    text-decoration: none;
}

.breadcrumb a:hover {
    text-decoration: underline;
}

.breadcrumb span {
    color: var(--gray-500);
}

.no-products {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--gray-600);
}

.no-products i {
    font-size: 4rem;
    margin-bottom: 1rem;
    color: var(--gray-400);
}

.no-products h3 {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    color: var(--primary);
}

.placeholder-image {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    background: var(--gray-200);
    color: var(--gray-400);
    font-size: 3rem;
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
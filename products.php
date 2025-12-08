<?php
require_once 'includes/config.php';
$page_title = 'Products';
include 'includes/header.php';

// Get search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name';

// Get category details if category is selected
$current_category = null;
if ($category_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$category_id]);
        $current_category = $stmt->fetch();
    } catch (PDOException $e) {
        $current_category = null;
    }
}

// Build query
$query = "SELECT p.*, c.name as category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.status = 'active'";
$params = [];

if ($search) {
    $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category_id) {
    $query .= " AND p.category_id = ?";
    $params[] = $category_id;
}

// Add sorting
switch ($sort) {
    case 'price_low':
        $query .= " ORDER BY p.price ASC";
        break;
    case 'price_high':
        $query .= " ORDER BY p.price DESC";
        break;
    case 'newest':
        $query .= " ORDER BY p.created_at DESC";
        break;
    default:
        $query .= " ORDER BY p.name ASC";
}

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
    // Get categories for filter
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
    $categories = [];
}
?>

<div class="page-header">
    <div class="container">
        <h1 class="page-title"><?php echo $current_category ? htmlspecialchars($current_category['name']) : 'Our Products'; ?></h1>
        <p class="page-subtitle"><?php echo $current_category ? 'Explore our ' . strtolower($current_category['name']) . ' collection' : 'Discover premium furniture for every space'; ?></p>
    </div>
</div>

<div class="filters-section">
    <div class="container">
        <form method="GET" class="filters-form">
            <div class="search-input">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <select name="category" class="filter-select">
                <option value="">All Categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>" <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="sort" class="filter-select">
                <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Name</option>
                <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest</option>
            </select>
            <button type="submit" class="btn-filter">Filter</button>
            <?php if ($search || $category_id || $sort != 'name'): ?>
            <button type="button" onclick="clearFilters()" class="btn-clear">Clear</button>
            <?php endif; ?>
        </form>
        <div class="results-info">
            <?php echo count($products); ?> products found
        </div>
    </div>
</div>

<!-- Products Section -->
<section class="products-section">
    <div class="container">
        <?php if (empty($products)): ?>
            <div class="empty-state">
                <i class="fas fa-search"></i>
                <h3>No products found</h3>
                <p>Try adjusting your search or filters</p>
                <a href="products.php" class="btn-primary">View All Products</a>
            </div>
        <?php else: ?>
            <div class="products-grid-new">
                <?php foreach ($products as $product): ?>
                <div class="product-card-new" onclick="window.location.href='product_detail.php?id=<?php echo $product['id']; ?>'">
                    <?php if ($product['featured']): ?>
                        <div class="featured-badge">✨ Featured</div>
                    <?php endif; ?>
                    <div class="product-image-new">
                        <?php if (!empty($product['image'])): ?>
                            <img src="assets/images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <?php else: ?>
                            <div class="image-placeholder">
                                <i class="fas fa-couch"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="product-details">
                        <div class="product-brand-new">INTERNO</div>
                        <h3 class="product-title-new"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <?php if ($product['category_name']): ?>
                            <div class="product-category-new"><?php echo htmlspecialchars($product['category_name']); ?></div>
                        <?php endif; ?>
                        <div class="price-section">
                            <span class="price-new">₹<?php echo number_format($product['price'], 2); ?></span>
                        </div>
                        <button class="add-cart-btn" onclick="event.stopPropagation(); addToCart(<?php echo $product['id']; ?>)">
                            <i class="fas fa-shopping-cart"></i>
                            Add to Cart
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
/* Page Header */
.page-header {
    background: var(--light);
    padding: 2rem 0;
    border-bottom: 1px solid var(--gray-200);
}

.page-title {
    font-size: 2.5rem;
    font-weight: 600;
    color: var(--primary);
    margin-bottom: 0.5rem;
}

.page-subtitle {
    font-size: 1.1rem;
    color: var(--gray-600);
    margin: 0;
}

/* Filters Section */
.filters-section {
    background: var(--gray-50);
    padding: 1.5rem 0;
    border-bottom: 1px solid var(--gray-200);
}

.filters-form {
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}

.search-input {
    position: relative;
    flex: 1;
    min-width: 250px;
}

.search-input i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray-400);
    font-size: 0.9rem;
}

.search-input input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.5rem;
    border: 1px solid var(--gray-300);
    border-radius: var(--radius);
    font-size: 0.9rem;
    background: var(--light);
    transition: var(--transition);
}

.search-input input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.filter-select {
    padding: 0.75rem 1rem;
    border: 1px solid var(--gray-300);
    border-radius: var(--radius);
    background: var(--light);
    font-size: 0.9rem;
    cursor: pointer;
    transition: var(--transition);
}

.filter-select:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.btn-filter, .btn-clear {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: var(--radius);
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    font-size: 0.9rem;
}

.btn-filter {
    background: var(--primary);
    color: var(--light);
}

.btn-filter:hover {
    background: var(--primary-dark);
    transform: translateY(-1px);
}

.btn-clear {
    background: var(--gray-200);
    color: var(--gray-700);
}

.btn-clear:hover {
    background: var(--gray-300);
}

.results-info {
    color: var(--gray-600);
    font-size: 0.9rem;
    font-weight: 500;
}

/* Products Section */
.products-section {
    padding: 2rem 0;
    background: var(--light);
}

.products-grid-new {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 2rem;
}

.product-card-new {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    cursor: pointer;
    border: 1px solid #f1f5f9;
}

.product-card-new:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.featured-badge {
    position: absolute;
    top: 1rem;
    left: 1rem;
    background: linear-gradient(135deg, #fbbf24, #f59e0b);
    color: white;
    padding: 0.5rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    z-index: 10;
}

.product-image-new {
    height: 250px;
    background: #f8fafc;
    position: relative;
    overflow: hidden;
}

.product-image-new img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}

.product-card-new:hover .product-image-new img {
    transform: scale(1.05);
}

.image-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #9ca3af;
    font-size: 3rem;
}

.product-details {
    padding: 1.5rem;
}

.product-brand-new {
    color: #06b6d4;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
}

.product-title-new {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.5rem;
    line-height: 1.3;
}

.product-category-new {
    color: #64748b;
    font-size: 0.8rem;
    margin-bottom: 1rem;
}

.price-section {
    margin-bottom: 1.5rem;
}

.price-new {
    font-size: 1.5rem;
    font-weight: 700;
    color: #6366f1;
}

.add-cart-btn {
    width: 100%;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: white;
    border: none;
    padding: 0.75rem;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.add-cart-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #6b7280;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    color: #9ca3af;
}

.empty-state h3 {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    color: #374151;
}

.btn-primary {
    display: inline-block;
    background: #6366f1;
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    margin-top: 1rem;
    transition: all 0.3s;
}

.btn-primary:hover {
    background: #5856eb;
    transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 768px) {
    .products-hero h1 {
        font-size: 2rem;
    }
    
    .modern-filters {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-box {
        min-width: auto;
    }
    
    .results-count {
        margin-left: 0;
        text-align: center;
    }
    
    .products-grid-new {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
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

// Auto-submit filter form when selections change
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.querySelector('select[name="category"]');
    const sortSelect = document.querySelector('select[name="sort"]');
    const searchInput = document.querySelector('input[name="search"]');
    const filterForm = document.querySelector('.filters-form');
    
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            filterForm.submit();
        });
    }
    
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            filterForm.submit();
        });
    }
    
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                filterForm.submit();
            }
        });
    }
});

// Clear filters function
function clearFilters() {
    window.location.href = 'products.php';
}
</script>

<?php include 'includes/footer.php'; ?>
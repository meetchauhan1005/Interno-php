<?php
require_once 'includes/config.php';
$page_title = 'Categories';
include 'includes/header.php';
?>

<section class="section" style="padding-top: 2rem;">
    <div class="container">
        <div class="section-header">
            <h1 class="section-title">Product Categories</h1>
            <p class="section-subtitle">Explore our diverse range of product categories</p>
        </div>
        
        <div class="modern-categories-grid">
            <?php
            try {
                $stmt = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order, name");
                $categories = $stmt->fetchAll();
                
                $category_icons = [
                    'bedroom' => 'fas fa-bed',
                    'office' => 'fas fa-briefcase',
                    'sofa-chairs' => 'fas fa-couch',
                    'storage' => 'fas fa-archive',
                    'tables' => 'fas fa-utensils'
                ];
                
                foreach ($categories as $category):
                    $icon = $category_icons[$category['slug']] ?? 'fas fa-couch';
                    
                    // Get product count for this category
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ? AND status = 'active'");
                    $stmt->execute([$category['id']]);
                    $productCount = $stmt->fetchColumn();
            ?>
            <a href="products.php?category=<?php echo $category['id']; ?>" class="modern-category-card">
                <div class="category-gradient">
                    <div class="category-icon-modern">
                        <i class="<?php echo $icon; ?>"></i>
                    </div>
                    <?php if ($productCount > 0): ?>
                        <div class="product-count-badge"><?php echo $productCount; ?></div>
                    <?php endif; ?>
                </div>
                <div class="category-content">
                    <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                    <p><?php echo htmlspecialchars($category['description'] ?: 'Explore our collection'); ?></p>
                    <div class="category-stats">
                        <span class="product-count"><?php echo $productCount; ?> products</span>
                        <span class="explore-btn">Explore <i class="fas fa-arrow-right"></i></span>
                    </div>
                </div>
            </a>
            <?php 
                endforeach;
            } catch (PDOException $e) {
                echo '<p>Unable to load categories at this time.</p>';
            }
            ?>
        </div>
    </div>
</section>

<style>
.modern-categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 2rem;
    margin-top: 3rem;
}

.modern-category-card {
    background: var(--light);
    border-radius: var(--radius-lg);
    overflow: hidden;
    text-decoration: none;
    color: inherit;
    transition: var(--transition);
    box-shadow: var(--shadow);
    border: 1px solid var(--gray-200);
}

.modern-category-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-xl);
    border-color: var(--primary);
}

.category-gradient {
    height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.category-gradient {
    background: var(--gray-100);
}

.category-icon-modern {
    width: 80px;
    height: 80px;
    background: var(--primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--light);
    font-size: 2.5rem;
}

.category-content {
    padding: 2rem;
    text-align: center;
}

.category-content h3 {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
    color: var(--gray-900);
}

.category-content p {
    color: var(--gray-600);
    margin-bottom: 1rem;
    line-height: 1.6;
}

.explore-btn {
    color: var(--primary);
    font-weight: 600;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: var(--transition);
}

.modern-category-card:hover .explore-btn {
    transform: translateX(5px);
}

.product-count-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: var(--primary);
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    min-width: 24px;
    text-align: center;
}

.category-stats {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1rem;
}

.product-count {
    font-size: 0.8rem;
    color: var(--gray-500);
    font-weight: 500;
}

.explore-btn {
    color: var(--primary);
    font-weight: 600;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: var(--transition);
}

@media (max-width: 768px) {
    .modern-categories-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .category-gradient {
        height: 150px;
    }
    
    .category-icon-modern {
        width: 60px;
        height: 60px;
        font-size: 2rem;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
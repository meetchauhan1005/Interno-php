<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../user/login.php');
    exit();
}

// Get all website data
try {
    // Products
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
    $totalProducts = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as active FROM products WHERE status = 'active'");
    $activeProducts = $stmt->fetch()['active'];
    
    // Categories
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    $categories = $stmt->fetchAll();
    
    // Users
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'customer'");
    $totalUsers = $stmt->fetch()['total'];
    
    // Orders
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $totalOrders = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT SUM(total_amount) as revenue FROM orders WHERE status = 'delivered'");
    $totalRevenue = $stmt->fetch()['revenue'] ?? 0;
    
    // Messages
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM contact_messages");
    $totalMessages = $stmt->fetch()['total'];
    
    // Recent products
    $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 5");
    $recentProducts = $stmt->fetchAll();
    
    // Recent orders
    $stmt = $pdo->query("SELECT o.*, u.username FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5");
    $recentOrders = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Data Overview - INTERNO Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="admin-container">
    <div class="admin-header">
        <div class="header-left">
            <div class="logo">
                <span style="font-size: 1.2rem; margin-right: 8px;">🛋️</span>
                <i class="fas fa-shield-alt"></i> Admin Panel
            </div>
        </div>
        <div class="header-right">
            <a href="../index.php" class="btn-view-site">
                <i class="fas fa-external-link-alt"></i> View Site
            </a>
            <a href="../user/logout.php" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
    
    <div class="admin-content">
        <div class="page-header">
            <h1><i class="fas fa-database"></i> Website Data Overview</h1>
            <p>Complete overview of all website data and statistics</p>
        </div>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card products">
                <div class="stat-icon">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $totalProducts; ?></h3>
                    <p>Total Products</p>
                    <small><?php echo $activeProducts; ?> active</small>
                </div>
            </div>
            
            <div class="stat-card categories">
                <div class="stat-icon">
                    <i class="fas fa-tags"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo count($categories); ?></h3>
                    <p>Categories</p>
                    <small>5 main categories</small>
                </div>
            </div>
            
            <div class="stat-card users">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $totalUsers; ?></h3>
                    <p>Customers</p>
                    <small>Registered users</small>
                </div>
            </div>
            
            <div class="stat-card orders">
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $totalOrders; ?></h3>
                    <p>Total Orders</p>
                    <small>All time</small>
                </div>
            </div>
            
            <div class="stat-card revenue">
                <div class="stat-icon">
                    <i class="fas fa-rupee-sign"></i>
                </div>
                <div class="stat-content">
                    <h3>₹<?php echo number_format($totalRevenue, 0); ?></h3>
                    <p>Revenue</p>
                    <small>Delivered orders</small>
                </div>
            </div>
            
            <div class="stat-card messages">
                <div class="stat-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $totalMessages; ?></h3>
                    <p>Messages</p>
                    <small>Contact inquiries</small>
                </div>
            </div>
        </div>
        
        <!-- Categories Overview -->
        <div class="data-section">
            <h2><i class="fas fa-tags"></i> Categories</h2>
            <div class="categories-grid">
                <?php foreach ($categories as $category): ?>
                <div class="category-item">
                    <div class="category-icon">
                        <?php
                        $icons = [
                            'bedroom' => 'fas fa-bed',
                            'office' => 'fas fa-briefcase', 
                            'sofa-chairs' => 'fas fa-couch',
                            'storage' => 'fas fa-archive',
                            'tables' => 'fas fa-utensils'
                        ];
                        $icon = $icons[$category['slug']] ?? 'fas fa-couch';
                        ?>
                        <i class="<?php echo $icon; ?>"></i>
                    </div>
                    <div class="category-info">
                        <h4><?php echo htmlspecialchars($category['name']); ?></h4>
                        <p><?php echo htmlspecialchars($category['description']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Recent Products -->
        <div class="data-section">
            <h2><i class="fas fa-box"></i> Recent Products</h2>
            <div class="products-table">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Added</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentProducts as $product): ?>
                        <tr>
                            <td>
                                <div class="product-info">
                                    <?php if ($product['image']): ?>
                                        <img src="../assets/images/<?php echo $product['image']; ?>" alt="Product">
                                    <?php else: ?>
                                        <div class="no-image"><i class="fas fa-image"></i></div>
                                    <?php endif; ?>
                                    <span><?php echo htmlspecialchars($product['name']); ?></span>
                                </div>
                            </td>
                            <td>₹<?php echo number_format($product['price'], 2); ?></td>
                            <td><?php echo $product['stock_quantity']; ?></td>
                            <td>
                                <span class="status <?php echo $product['status']; ?>">
                                    <?php echo ucfirst($product['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($product['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Recent Orders -->
        <div class="data-section">
            <h2><i class="fas fa-shopping-cart"></i> Recent Orders</h2>
            <div class="orders-table">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['username'] ?? 'Guest'); ?></td>
                            <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <span class="status <?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
body {
    margin: 0;
    background: #f8f9fa;
    font-family: 'Inter', sans-serif;
}

.admin-container {
    min-height: 100vh;
}

.admin-header {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
    padding: 20px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.header-left {
    display: flex;
    align-items: center;
    gap: 25px;
}

.logo {
    font-size: 18px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 8px;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 20px;
}

.btn-view-site,
.btn-logout {
    color: white;
    text-decoration: none;
    padding: 8px 16px;
    border-radius: 20px;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
}

.btn-view-site {
    background: rgba(255,255,255,0.1);
}

.btn-logout {
    background: #e74c3c;
}

.btn-view-site:hover {
    background: rgba(255,255,255,0.2);
}

.btn-logout:hover {
    background: #c0392b;
}

.admin-content {
    padding: 40px;
}

.page-header h1 {
    font-size: 36px;
    color: #2c3e50;
    margin: 0 0 8px 0;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 12px;
}

.page-header p {
    color: #7f8c8d;
    margin: 0 0 30px 0;
    font-size: 16px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card {
    background: white;
    padding: 24px;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 16px;
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: white;
}

.stat-card.products .stat-icon { background: #3b82f6; }
.stat-card.categories .stat-icon { background: #8b5cf6; }
.stat-card.users .stat-icon { background: #10b981; }
.stat-card.orders .stat-icon { background: #f59e0b; }
.stat-card.revenue .stat-icon { background: #ef4444; }
.stat-card.messages .stat-icon { background: #6366f1; }

.stat-content h3 {
    margin: 0 0 4px 0;
    font-size: 24px;
    font-weight: 700;
    color: #1f2937;
}

.stat-content p {
    margin: 0 0 4px 0;
    font-size: 14px;
    color: #6b7280;
    font-weight: 500;
}

.stat-content small {
    font-size: 12px;
    color: #9ca3af;
}

.data-section {
    background: white;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.data-section h2 {
    margin: 0 0 20px 0;
    font-size: 20px;
    font-weight: 600;
    color: #1f2937;
    display: flex;
    align-items: center;
    gap: 8px;
}

.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 16px;
}

.category-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    background: #f9fafb;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}

.category-icon {
    width: 40px;
    height: 40px;
    background: #6366f1;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 16px;
}

.category-info h4 {
    margin: 0 0 4px 0;
    font-size: 14px;
    font-weight: 600;
    color: #1f2937;
}

.category-info p {
    margin: 0;
    font-size: 12px;
    color: #6b7280;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    text-align: left;
    padding: 12px;
    border-bottom: 1px solid #e5e7eb;
}

th {
    background: #f9fafb;
    font-weight: 600;
    color: #374151;
    font-size: 14px;
}

.product-info {
    display: flex;
    align-items: center;
    gap: 8px;
}

.product-info img {
    width: 32px;
    height: 32px;
    object-fit: cover;
    border-radius: 4px;
}

.no-image {
    width: 32px;
    height: 32px;
    background: #f3f4f6;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
    font-size: 12px;
}

.status {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.status.active { background: #d1fae5; color: #065f46; }
.status.inactive { background: #fee2e2; color: #991b1b; }
.status.pending { background: #fef3c7; color: #92400e; }
.status.delivered { background: #d1fae5; color: #065f46; }
.status.shipped { background: #dbeafe; color: #1e40af; }

@media (max-width: 768px) {
    .admin-content {
        padding: 20px;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .categories-grid {
        grid-template-columns: 1fr;
    }
    
    table {
        font-size: 14px;
    }
    
    th, td {
        padding: 8px;
    }
}
</style>

</body>
</html>
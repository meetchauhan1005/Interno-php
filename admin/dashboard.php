<?php
session_start();
require_once '../includes/config.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../user/login.php');
    exit();
}

$page_title = 'Admin Dashboard';

// Get statistics
try {
    $stats = [];
    
    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role != 'admin' OR role IS NULL");
    $stats['users'] = $stmt->fetch()['count'] ?? 0;
    
    // Total products
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
    $stats['products'] = $stmt->fetch()['count'] ?? 0;
    
    // Total orders - handle if table doesn't exist
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
        $stats['orders'] = $stmt->fetch()['count'] ?? 0;
    } catch (PDOException $e) {
        $stats['orders'] = 0;
    }
    
    // Total revenue - handle if table doesn't exist
    try {
        $stmt = $pdo->query("SELECT SUM(total_amount) as revenue FROM orders WHERE status != 'cancelled'");
        $result = $stmt->fetch();
        $stats['revenue'] = $result['revenue'] ?? 0;
    } catch (PDOException $e) {
        $stats['revenue'] = 0;
    }
    
    // Recent orders - handle if table doesn't exist
    try {
        $stmt = $pdo->query("SELECT o.*, COALESCE(u.username, 'Guest') as username FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5");
        $recent_orders = $stmt->fetchAll();
    } catch (PDOException $e) {
        $recent_orders = [];
    }
    
    // Low stock products
    try {
        $stmt = $pdo->query("SELECT * FROM products WHERE stock_quantity <= 10 ORDER BY stock_quantity ASC LIMIT 5");
        $low_stock = $stmt->fetchAll();
    } catch (PDOException $e) {
        $low_stock = [];
    }
    
    // Order data for chart - handle if table doesn't exist
    try {
        $stmt = $pdo->query("SELECT id, total_amount FROM orders WHERE status != 'cancelled' ORDER BY created_at DESC LIMIT 10");
        $order_sales = $stmt->fetchAll();
    } catch (PDOException $e) {
        $order_sales = [];
    }
    
    // If no sales data, create dummy data
    if (empty($order_sales)) {
        $order_sales = [
            ['id' => 1, 'total_amount' => 2500],
            ['id' => 2, 'total_amount' => 3200],
            ['id' => 3, 'total_amount' => 1800],
            ['id' => 4, 'total_amount' => 4100],
            ['id' => 5, 'total_amount' => 2900]
        ];
    }
    
    // Order status data for chart - handle if table doesn't exist
    try {
        $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
        $order_status = $stmt->fetchAll();
    } catch (PDOException $e) {
        $order_status = [];
    }
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - INTERNO Admin</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20viewBox='0%200%2016%2016'%3E%3Ctext%20x='8'%20y='14'%20font-size='12'%20text-anchor='middle'%3E🛋️%3C/text%3E%3C/svg%3E">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-3d@1.0.0/dist/chartjs-3d.min.js"></script>
</head>
<body>

<div class="admin-container">
    <div class="admin-header">
        <div class="header-left">
            <div class="logo">🛋️ INTERNO</div>
            <div class="nav-tabs">
                <a href="dashboard.php" class="nav-tab active">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="products.php" class="nav-tab">
                    <i class="fas fa-box"></i> Products
                </a>
                <a href="categories.php" class="nav-tab">
                    <i class="fas fa-tags"></i> Categories
                </a>
                <a href="orders.php" class="nav-tab">
                    <i class="fas fa-shopping-cart"></i> Orders
                </a>
                <a href="users.php" class="nav-tab">
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="messages.php" class="nav-tab">
                    <i class="fas fa-envelope"></i> Messages
                </a>
                <a href="notifications.php" class="nav-tab">
                    <i class="fas fa-bell"></i> Notifications
                </a>
            </div>
        </div>
        <div class="header-right">
            <span class="welcome">Hello, admin</span>
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
            <h1>Dashboard</h1>
            <p>Welcome back, <strong>admin</strong></p>

        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="stats-grid">
            <div class="stat-card blue">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number"><?php echo number_format($stats['users']); ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            
            <div class="stat-card green">
                <div class="stat-icon">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-number"><?php echo number_format($stats['products']); ?></div>
                <div class="stat-label">Total Products</div>
            </div>
            
            <div class="stat-card orange">
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-number"><?php echo number_format($stats['orders']); ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
        </div>
        
        <div class="charts-grid">
            <div class="step-chart-card">
                <div class="chart-header">
                    <h3>Sales Overview</h3>
                    <p>Recent Orders Performance</p>
                </div>
                <div class="step-chart">
                    <canvas id="salesChart" width="800" height="400"></canvas>
                </div>
            </div>
            
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Order Status</h3>
                    <p>Current distribution</p>
                </div>
                <canvas id="orderChart"></canvas>
            </div>
        </div>
        
        <div class="recent-activity">
            <div class="activity-card">
                <div class="activity-header">
                    <h3>Recent Orders</h3>
                    <a href="orders.php" class="view-all">View All</a>
                </div>
                <div class="activity-list">
                    <?php if (!empty($recent_orders)): ?>
                        <?php foreach (array_slice($recent_orders, 0, 5) as $order): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">Order #<?php echo $order['id']; ?></div>
                                <div class="activity-meta"><?php echo $order['username']; ?> • ₹<?php echo number_format($order['total_amount'], 2); ?></div>
                            </div>
                            <div class="activity-time"><?php echo date('M j', strtotime($order['created_at'])); ?></div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-activity">No recent orders</div>
                    <?php endif; ?>
                </div>
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
    flex: 1;
}

.logo {
    font-size: 18px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 140px;
}

.nav-tabs {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.nav-tab {
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    padding: 8px 16px;
    border-radius: 20px;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 500;
    font-size: 14px;
    white-space: nowrap;
}

.nav-tab:hover,
.nav-tab.active {
    background: rgba(255,255,255,0.2);
    color: white;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 20px;
}

.welcome {
    font-weight: 500;
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

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 40px;
}

.page-header h1 {
    font-size: 36px;
    color: #2c3e50;
    margin: 0 0 8px 0;
    font-weight: 700;
}

.page-header p {
    color: #7f8c8d;
    margin: 0;
    font-size: 16px;
}

.page-actions {
    display: flex;
    gap: 15px;
}

.btn-secondary,
.btn-danger {
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
}

.btn-secondary {
    background: #3498db;
    color: white;
}

.btn-danger {
    background: #e74c3c;
    color: white;
}

.btn-secondary:hover {
    background: #2980b9;
    transform: translateY(-2px);
}

.btn-danger:hover {
    background: #c0392b;
    transform: translateY(-2px);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 30px;
    margin-bottom: 40px;
}

.stat-card {
    background: white;
    padding: 40px;
    border-radius: 20px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: all 0.3s;
    position: relative;
    overflow: hidden;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.stat-card.blue {
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    color: white;
}

.stat-card.green {
    background: linear-gradient(135deg, #06b6d4, #0891b2);
    color: white;
}

.stat-card.orange {
    background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    color: white;
}

.stat-icon {
    width: 80px;
    height: 80px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 32px;
}

.stat-number {
    font-size: 48px;
    font-weight: 700;
    margin-bottom: 10px;
}

.stat-label {
    font-size: 18px;
    font-weight: 500;
    opacity: 0.9;
}

.charts-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
    margin-bottom: 40px;
}

.chart-card {
    background: white;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    position: relative;
    overflow: hidden;
}

.step-chart-card {
    background: white;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.step-chart {
    position: relative;
    height: 400px;
    margin-top: 20px;
}

#salesChart {
    width: 100% !important;
    height: 100% !important;
}

.chart-card canvas {
    max-height: 300px !important;
    width: 100% !important;
}

.chart-header {
    margin-bottom: 25px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.header-content h3 {
    font-size: 20px;
    color: #2c3e50;
    margin: 0 0 5px 0;
    font-weight: 600;
}



.header-content p {
    color: #7f8c8d;
    margin: 0;
    font-size: 14px;
}



.revenue-stats {
    text-align: right;
}

.revenue-amount {
    display: block;
    font-size: 28px;
    font-weight: 700;
    color: white;
    line-height: 1;
}

.revenue-label {
    display: block;
    font-size: 12px;
    color: rgba(255,255,255,0.8);
    margin-top: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}



.recent-activity {
    margin-bottom: 40px;
}

.activity-card {
    background: white;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.activity-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.activity-header h3 {
    font-size: 20px;
    color: #2c3e50;
    margin: 0;
    font-weight: 600;
}

.view-all {
    color: #6366f1;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
}

.view-all:hover {
    text-decoration: underline;
}

.activity-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 12px;
    transition: all 0.3s;
}

.activity-item:hover {
    background: #e9ecef;
}

.activity-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 16px;
}

.activity-content {
    flex: 1;
}

.activity-title {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 3px;
}

.activity-meta {
    font-size: 13px;
    color: #7f8c8d;
}

.activity-time {
    font-size: 12px;
    color: #95a5a6;
    font-weight: 500;
}

.no-activity {
    text-align: center;
    color: #7f8c8d;
    padding: 40px;
    font-style: italic;
}

@media (max-width: 768px) {
    .charts-grid {
        grid-template-columns: 1fr;
    }
    
    .chart-header {
        flex-direction: column;
        gap: 15px;
    }
    
    .revenue-stats {
        text-align: left;
    }
    .admin-sidebar {
        width: 100%;
        position: relative;
        height: auto;
    }
    
    .admin-main {
        margin-left: 0;
    }
    
    .stats-row {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .content-grid {
        grid-template-columns: 1fr;
    }
    
    .actions-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<script>

const salesData = <?php echo json_encode($order_sales); ?>;
const salesLabels = salesData.map(item => 'Order #' + item.id);
const salesValues = salesData.map(item => parseFloat(item.total_amount) || 0);

// Sales Bar Chart
const salesCtx = document.getElementById('salesChart').getContext('2d');
const salesChart = new Chart(salesCtx, {
    type: 'bar',
    data: {
        labels: salesLabels.length ? salesLabels : ['Order #1', 'Order #2', 'Order #3', 'Order #4', 'Order #5'],
        datasets: [{
            label: 'Amount',
            data: salesValues.length ? salesValues : [2500, 3200, 1800, 4100, 2900],
            backgroundColor: ['#4f46e5', '#10b981', '#ef4444', '#8b5cf6', '#f59e0b', '#06b6d4', '#ec4899', '#14b8a6', '#f97316', '#84cc16'],
            borderRadius: 4,
            borderSkipped: false,
            categoryPercentage: 0.8,
            barPercentage: 0.6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '₹' + (value / 1000) + 'K';
                    }
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});

// Order Status Chart
const orderCtx = document.getElementById('orderChart').getContext('2d');
const orderData = <?php echo json_encode($order_status); ?>;
const orderLabels = orderData.map(item => item.status.charAt(0).toUpperCase() + item.status.slice(1));
const orderValues = orderData.map(item => parseInt(item.count));
const orderColors = {
    'Pending': '#f59e0b',
    'Processing': '#06b6d4', 
    'Shipped': '#8b5cf6',
    'Delivered': '#10b981',
    'Cancelled': '#ef4444'
};
const backgroundColors = orderLabels.map(label => orderColors[label] || '#6b7280');

const orderChart = new Chart(orderCtx, {
    type: 'doughnut',
    data: {
        labels: orderLabels.length ? orderLabels : ['No Orders'],
        datasets: [{
            data: orderValues.length ? orderValues : [1],
            backgroundColor: backgroundColors.length ? backgroundColors : ['#e5e7eb'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    usePointStyle: true
                }
            }
        }
    }
});

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    // Set chart container heights
    const salesCanvas = document.getElementById('salesChart');
    const orderCanvas = document.getElementById('orderChart');
    
    if (salesCanvas) salesCanvas.style.height = '300px';
    if (orderCanvas) orderCanvas.style.height = '300px';
});
</script>

</body>
</html>
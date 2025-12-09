<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../user/login.php');
    exit();
}

// Get notifications data
try {
    // Create tables if they don't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        subject VARCHAR(200) NOT NULL,
        message TEXT NOT NULL,
        status ENUM('unread','read') DEFAULT 'unread',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // New messages (unread)
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'unread'");
    $newMessages = $stmt->fetch()['count'];
    
    // New orders (pending)
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
    $newOrders = $stmt->fetch()['count'];
    
    // Low stock products
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM products WHERE stock_quantity <= 5 AND status = 'active'");
    $lowStock = $stmt->fetch()['count'];
    
    // Recent activities
    $activities = [];
    
    // Recent messages
    $stmt = $pdo->query("SELECT 'message' as type, name, email, subject, created_at FROM contact_messages ORDER BY created_at DESC LIMIT 5");
    $recentMessages = $stmt->fetchAll();
    foreach ($recentMessages as $msg) {
        $activities[] = [
            'type' => 'message',
            'title' => 'New message from ' . $msg['name'],
            'description' => $msg['subject'],
            'time' => $msg['created_at'],
            'icon' => 'fas fa-envelope',
            'color' => 'blue'
        ];
    }
    
    // Recent orders
    $stmt = $pdo->query("SELECT 'order' as type, order_number, total_amount, created_at FROM orders ORDER BY created_at DESC LIMIT 5");
    $recentOrders = $stmt->fetchAll();
    foreach ($recentOrders as $order) {
        $activities[] = [
            'type' => 'order',
            'title' => 'New order ' . $order['order_number'],
            'description' => '₹' . number_format($order['total_amount'], 2),
            'time' => $order['created_at'],
            'icon' => 'fas fa-shopping-cart',
            'color' => 'green'
        ];
    }
    
    // Sort activities by time
    usort($activities, function($a, $b) {
        return strtotime($b['time']) - strtotime($a['time']);
    });
    
} catch (PDOException $e) {
    $newMessages = $newOrders = $lowStock = 0;
    $activities = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - INTERNO Admin</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20viewBox='0%200%2016%2016'%3E%3Ctext%20x='8'%20y='14'%20font-size='12'%20text-anchor='middle'%3E🛋️%3C/text%3E%3C/svg%3E">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="admin-container">
    <div class="admin-header">
        <div class="header-left">
            <div class="logo">🛋️ INTERNO</div>
            <button class="mobile-menu-toggle" onclick="toggleAdminMenu()">
                <i class="fas fa-bars"></i>
            </button>
            <div class="nav-tabs" id="adminNavTabs">
                <a href="dashboard.php" class="nav-tab">
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
                <a href="notifications.php" class="nav-tab active">
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
            <h1><i class="fas fa-bell"></i> Notifications</h1>
            <p>Stay updated with important activities</p>
        </div>
        
        <div class="notification-summary">
            <div class="summary-card messages">
                <div class="summary-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="summary-content">
                    <h3><?php echo $newMessages; ?></h3>
                    <p>New Messages</p>
                    <a href="messages.php">View Messages</a>
                </div>
            </div>
            
            <div class="summary-card orders">
                <div class="summary-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="summary-content">
                    <h3><?php echo $newOrders; ?></h3>
                    <p>Pending Orders</p>
                    <a href="orders.php">View Orders</a>
                </div>
            </div>
            
            <div class="summary-card stock">
                <div class="summary-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="summary-content">
                    <h3><?php echo $lowStock; ?></h3>
                    <p>Low Stock Items</p>
                    <a href="products.php">View Products</a>
                </div>
            </div>
        </div>
        
        <div class="activities-section">
            <h2>Recent Activities</h2>
            <div class="activities-list">
                <?php if (empty($activities)): ?>
                    <div class="no-activities">
                        <i class="fas fa-inbox"></i>
                        <h3>No Recent Activities</h3>
                        <p>All caught up! No new activities to show.</p>
                        <a href="messages.php" class="btn btn-primary">Go to Messages</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($activities as $activity): ?>
                    <div class="activity-item" onclick="window.location.href='messages.php'" style="cursor: pointer;">
                        <div class="activity-icon <?php echo $activity['color']; ?>">
                            <i class="<?php echo $activity['icon']; ?>"></i>
                        </div>
                        <div class="activity-content">
                            <h4><?php echo htmlspecialchars($activity['title']); ?></h4>
                            <p><?php echo htmlspecialchars($activity['description']); ?></p>
                            <span class="activity-time"><?php echo date('M j, Y g:i A', strtotime($activity['time'])); ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <div class="view-all-messages">
                        <a href="messages.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-envelope"></i> View All in Messages
                        </a>
                    </div>
                <?php endif; ?>
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

.notification-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.summary-card {
    background: white;
    padding: 24px;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 20px;
    border: 1px solid #e5e7eb;
}

.summary-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
}

.summary-card.messages .summary-icon {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
}

.summary-card.orders .summary-icon {
    background: linear-gradient(135deg, #10b981, #059669);
}

.summary-card.stock .summary-icon {
    background: linear-gradient(135deg, #f59e0b, #d97706);
}

.summary-content h3 {
    margin: 0 0 4px 0;
    font-size: 28px;
    font-weight: 700;
    color: #1f2937;
}

.summary-content p {
    margin: 0 0 8px 0;
    font-size: 14px;
    color: #6b7280;
    font-weight: 500;
}

.summary-content a {
    color: #6366f1;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
}

.summary-content a:hover {
    text-decoration: underline;
}

.activities-section h2 {
    font-size: 24px;
    color: #1f2937;
    margin-bottom: 20px;
    font-weight: 600;
}

.activities-list {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    overflow: hidden;
}

.no-activities {
    text-align: center;
    padding: 60px 20px;
}

.no-activities i {
    font-size: 4rem;
    color: #9ca3af;
    margin-bottom: 1rem;
}

.no-activities h3 {
    color: #374151;
    margin-bottom: 0.5rem;
}

.no-activities p {
    color: #6b7280;
    margin: 0;
}

.activity-item {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 20px;
    border-bottom: 1px solid #e5e7eb;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 16px;
    flex-shrink: 0;
}

.activity-icon.blue {
    background: #3b82f6;
}

.activity-icon.green {
    background: #10b981;
}

.activity-icon.orange {
    background: #f59e0b;
}

.activity-content h4 {
    margin: 0 0 4px 0;
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
}

.activity-content p {
    margin: 0 0 8px 0;
    color: #6b7280;
    font-size: 14px;
}

.activity-time {
    font-size: 12px;
    color: #9ca3af;
    font-weight: 500;
}

.activity-item:hover {
    background: #f9fafb;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.view-all-messages {
    padding: 20px;
    text-align: center;
    border-top: 1px solid #e5e7eb;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-primary {
    background: #6366f1;
    color: white;
}

.btn-primary:hover {
    background: #5856eb;
    transform: translateY(-1px);
}

.btn-lg {
    padding: 16px 32px;
    font-size: 16px;
}

.no-activities a.btn {
    margin-top: 16px;
}

.mobile-menu-toggle {
    display: none;
    background: rgba(255,255,255,0.2);
    border: none;
    border-radius: 8px;
    color: white;
    cursor: pointer;
    padding: 8px 12px;
    min-width: 44px;
    min-height: 44px;
    transition: all 0.3s;
}

.mobile-menu-toggle:hover {
    background: rgba(255,255,255,0.3);
}

.mobile-menu-toggle i {
    font-size: 1.25rem;
}

@media (max-width: 1024px) {
    .admin-header {
        padding: 15px 20px;
        flex-wrap: wrap;
    }
    
    .header-left {
        width: 100%;
        justify-content: space-between;
    }
    
    .mobile-menu-toggle {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .nav-tabs {
        display: none;
        width: 100%;
        flex-direction: column;
        gap: 8px;
        margin-top: 15px;
        background: rgba(255,255,255,0.1);
        padding: 15px;
        border-radius: 12px;
    }
    
    .nav-tabs.active {
        display: flex;
    }
    
    .nav-tab {
        width: 100%;
        padding: 12px 16px;
        justify-content: flex-start;
        background: rgba(255,255,255,0.05);
    }
    
    .header-right {
        width: 100%;
        justify-content: flex-end;
        margin-top: 15px;
        flex-wrap: wrap;
    }
    
    .welcome {
        width: 100%;
        text-align: center;
        margin-bottom: 10px;
    }
}

@media (max-width: 768px) {
    .admin-content {
        padding: 20px;
    }
    
    .notification-summary {
        grid-template-columns: 1fr;
    }
    
    .nav-tabs {
        flex-wrap: wrap;
    }
    
    .admin-header {
        padding: 12px 15px;
    }
    
    .logo {
        font-size: 16px;
    }
    
    .btn-view-site,
    .btn-logout {
        padding: 8px 12px;
        font-size: 13px;
    }
}

@media (max-width: 480px) {
    .admin-header {
        padding: 10px 12px;
    }
    
    .logo {
        font-size: 15px;
    }
    
    .mobile-menu-toggle {
        padding: 6px 10px;
        min-width: 40px;
        min-height: 40px;
    }
}
</style>

<script>
function toggleAdminMenu() {
    const navTabs = document.getElementById('adminNavTabs');
    const toggle = document.querySelector('.mobile-menu-toggle');
    const icon = toggle.querySelector('i');
    
    navTabs.classList.toggle('active');
    
    if (navTabs.classList.contains('active')) {
        icon.classList.remove('fa-bars');
        icon.classList.add('fa-times');
    } else {
        icon.classList.remove('fa-times');
        icon.classList.add('fa-bars');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('.nav-tab');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 1024) {
                const navTabs = document.getElementById('adminNavTabs');
                if (navTabs.classList.contains('active')) {
                    toggleAdminMenu();
                }
            }
        });
    });
    
    window.addEventListener('resize', function() {
        if (window.innerWidth > 1024) {
            const navTabs = document.getElementById('adminNavTabs');
            const toggle = document.querySelector('.mobile-menu-toggle');
            if (navTabs && toggle) {
                const icon = toggle.querySelector('i');
                navTabs.classList.remove('active');
                if (icon) {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            }
        }
    });
});
</script>

</body>
</html>
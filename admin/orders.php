<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../user/login.php');
    exit();
}

$page_title = 'Orders Management';

// Handle status update
if ($_POST['action'] ?? '' === 'update_status') {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
        $success = "Order status updated successfully!";
    } catch (PDOException $e) {
        $error = "Error updating order status: " . $e->getMessage();
    }
}

// Get orders with customer details
try {
    $stmt = $pdo->query("
        SELECT o.*, u.username, u.email, u.first_name, u.last_name
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC
    ");
    $orders = $stmt->fetchAll();
    
    // Get order items for each order
    $orderItems = [];
    foreach ($orders as $order) {
        $stmt = $pdo->prepare("SELECT oi.*, p.image FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
        $stmt->execute([$order['id']]);
        $items = $stmt->fetchAll();
        
        if (empty($items)) {
            $orderItems[$order['id']] = [[
                'product_name' => 'Sample Product',
                'quantity' => 1,
                'price' => $order['total_amount'],
                'image' => null
            ]];
        } else {
            $orderItems[$order['id']] = $items;
        }
    }
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    $orders = [];
    $orderItems = [];
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
                <a href="orders.php" class="nav-tab active">
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
            <div>
                <h1>Orders Management</h1>
                <p>Manage customer orders and track deliveries</p>
            </div>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="orders-container">
            <?php if (!empty($orders)): ?>
            <div class="orders-grid">
                <?php foreach ($orders as $order): 
                    $items = $orderItems[$order['id']] ?? [];
                    $itemCount = count($items);
                ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-number">
                            <span class="order-id">#<?php echo $order['id']; ?></span>
                            <div class="order-amount">₹<?php echo number_format($order['total_amount'], 2); ?></div>
                        </div>
                        <div class="order-actions">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="status" class="status-select status-<?php echo $order['status']; ?>" onchange="this.form.submit()">
                                    <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>🕐 Pending</option>
                                    <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>⚙️ Processing</option>
                                    <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>🚚 Shipped</option>
                                    <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>🏠 Delivered</option>
                                    <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>❌ Cancelled</option>
                                </select>
                            </form>
                        </div>
                    </div>
                    
                    <div class="order-body">
                        <div class="customer-section">
                            <div class="section-title">
                                <i class="fas fa-user"></i> Customer
                            </div>
                            <div class="customer-info">
                                <div class="customer-name"><?php echo htmlspecialchars($order['username'] ?? 'Guest Customer'); ?></div>
                                <div class="customer-email"><?php echo htmlspecialchars($order['email'] ?? 'No email'); ?></div>
                            </div>
                        </div>
                        
                        <div class="products-section">
                            <div class="section-title">
                                <i class="fas fa-box"></i> Products (<?php echo $itemCount; ?> item<?php echo $itemCount > 1 ? 's' : ''; ?>)
                            </div>
                            <div class="products-list">
                                <?php for ($i = 0; $i < min(3, count($items)); $i++): ?>
                                    <div class="product-item">
                                        <div class="product-image">
                                            <?php if (!empty($items[$i]['image'])): ?>
                                                <img src="../assets/images/<?php echo htmlspecialchars($items[$i]['image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($items[$i]['product_name']); ?>">
                                            <?php else: ?>
                                                <div class="no-image">
                                                    <i class="fas fa-image"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="product-details">
                                            <div class="product-name"><?php echo htmlspecialchars($items[$i]['product_name']); ?></div>
                                            <div class="product-price">Qty: <?php echo $items[$i]['quantity']; ?> × ₹<?php echo number_format($items[$i]['price'], 2); ?></div>
                                        </div>
                                    </div>
                                <?php endfor; ?>
                                <?php if (count($items) > 3): ?>
                                    <div class="more-products">
                                        <i class="fas fa-plus"></i> +<?php echo count($items) - 3; ?> more items
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="order-footer">
                        <div class="order-dates">
                            <div class="date-item">
                                <span class="date-label">Ordered:</span>
                                <span class="date-value"><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h3>No Orders Found</h3>
                <p>Orders will appear here once customers start placing them.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
body { margin: 0; background: #f8fafc; font-family: 'Inter', sans-serif; }
.admin-container { min-height: 100vh; }
.admin-header { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; padding: 20px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
.header-left { display: flex; align-items: center; gap: 25px; flex: 1; }
.logo { font-size: 18px; font-weight: 700; display: flex; align-items: center; gap: 8px; min-width: 140px; }
.nav-tabs { display: flex; gap: 15px; flex-wrap: wrap; }
.nav-tab { color: rgba(255,255,255,0.8); text-decoration: none; padding: 8px 16px; border-radius: 20px; transition: all 0.3s; display: flex; align-items: center; gap: 6px; font-weight: 500; font-size: 14px; white-space: nowrap; }
.nav-tab:hover, .nav-tab.active { background: rgba(255,255,255,0.2); color: white; }
.header-right { display: flex; align-items: center; gap: 20px; }
.welcome { font-weight: 500; }
.btn-view-site, .btn-logout { color: white; text-decoration: none; padding: 8px 16px; border-radius: 20px; transition: all 0.3s; display: flex; align-items: center; gap: 6px; font-size: 14px; }
.btn-view-site { background: rgba(255,255,255,0.1); }
.btn-logout { background: #e74c3c; }
.btn-view-site:hover { background: rgba(255,255,255,0.2); }
.btn-logout:hover { background: #c0392b; }

.admin-content { padding: 40px; }
.page-header { margin-bottom: 40px; }
.page-header h1 { font-size: 36px; color: #2c3e50; margin: 0 0 8px 0; font-weight: 700; }
.page-header p { color: #7f8c8d; margin: 0; font-size: 16px; }

.orders-container { margin-top: 20px; }
.orders-grid { display: grid; gap: 25px; }
.order-card { background: white; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); border: 1px solid rgba(255,255,255,0.2); transition: all 0.3s ease; overflow: hidden; }
.order-card:hover { transform: translateY(-5px); box-shadow: 0 20px 60px rgba(0,0,0,0.15); }

.order-header { display: flex; justify-content: space-between; align-items: center; padding: 25px 30px; background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid rgba(0,0,0,0.05); }
.order-number { display: flex; flex-direction: column; gap: 5px; }
.order-id { font-size: 20px; font-weight: 800; background: linear-gradient(135deg, #667eea, #764ba2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
.order-amount { font-size: 24px; font-weight: 800; color: #059669; }
.status-select { padding: 10px 15px; border: 2px solid #e5e7eb; border-radius: 20px; font-size: 13px; font-weight: 600; background: white; cursor: pointer; transition: all 0.3s ease; }
.status-select:hover { border-color: #667eea; }
.status-pending { border-color: #f59e0b; background: #fef3c7; color: #92400e; }
.status-processing { border-color: #3b82f6; background: #dbeafe; color: #1e40af; }
.status-shipped { border-color: #8b5cf6; background: #ede9fe; color: #5b21b6; }
.status-delivered { border-color: #059669; background: #d1fae5; color: #065f46; }
.status-cancelled { border-color: #ef4444; background: #fee2e2; color: #991b1b; }

.order-body { display: grid; grid-template-columns: 1fr 2fr; gap: 30px; padding: 30px; }
.section-title { font-size: 14px; font-weight: 700; color: #374151; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
.customer-name { font-weight: 700; color: #1f2937; margin-bottom: 8px; font-size: 16px; }
.customer-email { font-size: 14px; color: #6b7280; margin-bottom: 6px; }

.products-list { display: flex; flex-direction: column; gap: 12px; }
.product-item { display: flex; align-items: center; gap: 12px; padding: 12px; background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-radius: 12px; border: 1px solid rgba(0,0,0,0.05); }
.product-image { width: 50px; height: 50px; border-radius: 10px; overflow: hidden; background: #e5e7eb; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.product-image img { width: 100%; height: 100%; object-fit: cover; }
.no-image { color: #9ca3af; font-size: 16px; }
.product-details { flex: 1; }
.product-name { font-weight: 600; color: #1f2937; font-size: 14px; margin-bottom: 4px; }
.product-price { font-size: 12px; color: #6b7280; }
.more-products { display: flex; align-items: center; gap: 8px; justify-content: center; padding: 12px; background: linear-gradient(135deg, #eff6ff, #dbeafe); border-radius: 12px; border: 1px solid rgba(102, 126, 234, 0.2); color: #667eea; font-size: 13px; font-weight: 600; }

.order-footer { display: flex; justify-content: space-between; align-items: center; padding: 20px 30px; background: #f8fafc; border-top: 1px solid rgba(0,0,0,0.05); }
.date-item { display: flex; flex-direction: column; gap: 2px; }
.date-label { font-size: 12px; color: #6b7280; font-weight: 600; text-transform: uppercase; }
.date-value { font-size: 13px; color: #1f2937; font-weight: 500; }

.empty-state { text-align: center; padding: 80px 40px; background: white; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
.empty-icon { width: 80px; height: 80px; background: linear-gradient(135deg, #f3f4f6, #e5e7eb); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; }
.empty-icon i { font-size: 32px; color: #9ca3af; }
.empty-state h3 { color: #374151; margin-bottom: 10px; font-size: 24px; }
.empty-state p { color: #6b7280; font-size: 16px; }

.alert { padding: 20px 25px; border-radius: 15px; margin-bottom: 25px; display: flex; align-items: center; gap: 12px; }
.alert-success { background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1)); color: #065f46; border: 1px solid rgba(16, 185, 129, 0.2); }
.alert-danger { background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.1)); color: #991b1b; border: 1px solid rgba(239, 68, 68, 0.2); }

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
    .order-body { grid-template-columns: 1fr; gap: 20px; }
    .order-header { flex-direction: column; gap: 15px; align-items: flex-start; }
    
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
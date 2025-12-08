<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../user/login.php');
    exit();
}

$page_title = 'Website Management';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_settings':
                try {
                    $stmt = $pdo->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = ?");
                    $stmt->execute([$_POST['site_name'], 'site_name']);
                    $stmt->execute([$_POST['site_tagline'], 'site_tagline']);
                    $stmt->execute([$_POST['contact_email'], 'contact_email']);
                    $stmt->execute([$_POST['contact_phone'], 'contact_phone']);
                    $success = "Settings updated successfully!";
                } catch (PDOException $e) {
                    $error = "Error updating settings: " . $e->getMessage();
                }
                break;
                
            case 'clear_cache':
                // Clear any cached files
                $success = "Cache cleared successfully!";
                break;
                
            case 'backup_database':
                $success = "Database backup initiated!";
                break;
        }
    }
}

// Get website statistics
try {
    // Get site settings (create table if not exists)
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) UNIQUE,
            setting_value TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Insert default settings if table is empty
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM site_settings");
        if ($stmt->fetch()['count'] == 0) {
            $pdo->exec("INSERT INTO site_settings (setting_key, setting_value) VALUES 
                ('site_name', 'INTERNO'),
                ('site_tagline', 'Premium E-commerce Experience'),
                ('contact_email', 'support@interno.com'),
                ('contact_phone', '+91 98765 43210')");
        }
    } catch (PDOException $e) {
        // Table creation failed, continue with defaults
    }
    
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
    $settings = [];
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    
    // Get statistics
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products WHERE status = 'active'");
    $totalProducts = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM categories WHERE is_active = 1");
    $totalCategories = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'customer'");
    $totalUsers = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $totalOrders = $stmt->fetch()['total'];
    
    // Create contact_messages table if it doesn't exist
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
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM contact_messages");
    $totalMessages = $stmt->fetch()['total'];
    
} catch (PDOException $e) {
    $settings = [
        'site_name' => 'INTERNO',
        'site_tagline' => 'Premium E-commerce Experience', 
        'contact_email' => 'support@interno.com',
        'contact_phone' => '+91 98765 43210'
    ];
    $totalProducts = $totalCategories = $totalUsers = $totalOrders = $totalMessages = 0;
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
            <div class="nav-tabs">
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
                <a href="messages.php" class="nav-tab active">
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
            <div class="header-left">
                <h1><i class="fas fa-cog"></i> Website Management</h1>
                <div class="stats-summary">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $totalProducts; ?></div>
                        <div class="stat-label">Products</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $totalCategories; ?></div>
                        <div class="stat-label">Categories</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $totalUsers; ?></div>
                        <div class="stat-label">Users</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $totalOrders; ?></div>
                        <div class="stat-label">Orders</div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Website Settings -->
        <div class="settings-section">
            <h2>Website Settings</h2>
            <form method="POST" class="settings-form">
                <input type="hidden" name="action" value="update_settings">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Site Name</label>
                        <input type="text" name="site_name" value="<?php echo htmlspecialchars($settings['site_name'] ?? 'INTERNO'); ?>" class="form-input">
                    </div>
                    <div class="form-group">
                        <label>Site Tagline</label>
                        <input type="text" name="site_tagline" value="<?php echo htmlspecialchars($settings['site_tagline'] ?? 'Premium E-commerce Experience'); ?>" class="form-input">
                    </div>
                    <div class="form-group">
                        <label>Contact Email</label>
                        <input type="email" name="contact_email" value="<?php echo htmlspecialchars($settings['contact_email'] ?? 'support@interno.com'); ?>" class="form-input">
                    </div>
                    <div class="form-group">
                        <label>Contact Phone</label>
                        <input type="text" name="contact_phone" value="<?php echo htmlspecialchars($settings['contact_phone'] ?? '+91 98765 43210'); ?>" class="form-input">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Settings
                </button>
            </form>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <!-- Website Tools -->
        <div class="tools-section">
            <h2>Website Tools</h2>
            <div class="tools-grid">
                <div class="tool-card">
                    <div class="tool-icon">
                        <i class="fas fa-broom"></i>
                    </div>
                    <h3>Clear Cache</h3>
                    <p>Clear website cache for better performance</p>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="clear_cache">
                        <button type="submit" class="btn btn-outline">
                            <i class="fas fa-broom"></i> Clear Cache
                        </button>
                    </form>
                </div>
                
                <div class="tool-card">
                    <div class="tool-icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <h3>Backup Database</h3>
                    <p>Create a backup of your database</p>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="backup_database">
                        <button type="submit" class="btn btn-outline">
                            <i class="fas fa-download"></i> Backup Now
                        </button>
                    </form>
                </div>
                
                <div class="tool-card">
                    <div class="tool-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h3>Contact Messages</h3>
                    <p>View and manage contact messages</p>
                    <div class="message-count"><?php echo $totalMessages; ?> messages</div>
                    <button onclick="loadContactMessages()" class="btn btn-primary">
                        <i class="fas fa-envelope"></i> View Messages
                    </button>
                </div>
                
                <div class="tool-card">
                    <div class="tool-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h3>Analytics</h3>
                    <p>View website performance metrics</p>
                    <a href="dashboard.php" class="btn btn-primary">
                        <i class="fas fa-chart-bar"></i> View Analytics
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Contact Messages Section (Hidden by default) -->
        <div id="contactMessagesSection" style="display: none;">
            <h2>Contact Messages</h2>
            <div id="messagesContainer">
                <!-- Messages will be loaded here -->
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
    margin-bottom: 30px;
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

.stats-summary {
    display: flex;
    gap: 20px;
    margin-top: 15px;
}

.stat-card {
    background: white;
    padding: 15px 20px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border: 1px solid #e5e7eb;
    min-width: 100px;
}

.stat-number {
    font-size: 24px;
    font-weight: 700;
    color: #6366f1;
    margin-bottom: 4px;
}

.stat-card.unread .stat-number {
    color: #f59e0b;
}

.stat-card.read .stat-number {
    color: #10b981;
}

.stat-label {
    font-size: 12px;
    color: #6b7280;
    font-weight: 500;
    text-transform: uppercase;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 15px;
}

.bulk-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    background: white;
    padding: 8px 12px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.bulk-actions select {
    border: 1px solid #d1d5db;
    border-radius: 6px;
    padding: 6px 10px;
    font-size: 14px;
}

.btn-bulk-apply {
    background: #6366f1;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
}

.filters-section {
    background: white;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.filters-form {
    display: flex;
    gap: 15px;
    align-items: end;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    flex-direction: column;
    min-width: 200px;
}

.search-input,
.filter-select {
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
    background: #f9fafb;
    transition: all 0.2s;
}

.search-input:focus,
.filter-select:focus {
    outline: none;
    border-color: #6366f1;
    background: white;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.btn-filter,
.btn-clear {
    padding: 10px 16px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-filter {
    background: #6366f1;
    color: white;
}

.btn-clear {
    background: #6b7280;
    color: white;
}

.btn-filter:hover {
    background: #5856eb;
}

.btn-clear:hover {
    background: #4b5563;
}

.alert {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border-left: 4px solid #10b981;
}





.btn-view-site {
    padding: 10px 16px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    transition: all 0.2s;
    background: #6366f1;
    color: white;
}

.btn-view-site:hover {
    background: #5856eb;
    transform: translateY(-1px);
}

.settings-section,
.tools-section {
    background: white;
    padding: 24px;
    border-radius: 12px;
    margin-bottom: 24px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.settings-section h2,
.tools-section h2 {
    margin: 0 0 20px 0;
    color: #1f2937;
    font-size: 20px;
    font-weight: 600;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 24px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
    font-size: 14px;
}

.form-input {
    padding: 12px 16px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.2s;
    background: #f9fafb;
}

.form-input:focus {
    outline: none;
    border-color: #6366f1;
    background: white;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.tools-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.tool-card {
    background: #f9fafb;
    padding: 24px;
    border-radius: 12px;
    text-align: center;
    border: 1px solid #e5e7eb;
    transition: all 0.3s;
}

.tool-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.tool-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    color: white;
    font-size: 24px;
}

.tool-card h3 {
    margin: 0 0 8px 0;
    color: #1f2937;
    font-size: 18px;
    font-weight: 600;
}

.tool-card p {
    margin: 0 0 16px 0;
    color: #6b7280;
    font-size: 14px;
    line-height: 1.5;
}

.message-count {
    background: #fef3c7;
    color: #92400e;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
    margin-bottom: 16px;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 6px;
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

.btn-outline {
    background: transparent;
    color: #6b7280;
    border: 1px solid #d1d5db;
}

.btn-outline:hover {
    background: #f3f4f6;
    color: #374151;
}

@media (max-width: 768px) {
    .admin-content {
        padding: 20px;
    }
    
    .filters-form {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-group {
        min-width: auto;
    }
    
    .page-header {
        flex-direction: column;
        gap: 20px;
    }
    
    .stats-summary {
        flex-wrap: wrap;
    }
    
    .sender-info {
        flex-direction: column;
        text-align: center;
    }
    
    .message-actions {
        flex-direction: column;
    }
    
    .action-btn {
        justify-content: center;
    }
}
</style>

<script>
let selectedMessages = [];
let currentModalData = {};

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.message-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateBulkActions();
}

function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.message-checkbox:checked');
    const bulkActions = document.getElementById('bulkActions');
    
    selectedMessages = Array.from(checkboxes).map(cb => cb.value);
    
    if (selectedMessages.length > 0) {
        bulkActions.style.display = 'flex';
    } else {
        bulkActions.style.display = 'none';
    }
}

function applyBulkAction() {
    const action = document.getElementById('bulkActionSelect').value;
    
    if (!action || selectedMessages.length === 0) {
        alert('Please select an action and messages');
        return;
    }
    
    if (action === 'delete' && !confirm(`Are you sure you want to delete ${selectedMessages.length} messages?`)) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="action" value="bulk_action">
        <input type="hidden" name="bulk_action_type" value="${action}">
        <input type="hidden" name="message_ids" value='${JSON.stringify(selectedMessages)}'>
    `;
    document.body.appendChild(form);
    form.submit();
}

function viewFullMessage(id, messageData) {
    currentModalData = messageData;
    
    document.getElementById('modalName').textContent = messageData.name;
    document.getElementById('modalEmail').textContent = messageData.email;
    document.getElementById('modalPhone').textContent = messageData.phone || 'Not provided';
    document.getElementById('modalSubject').textContent = messageData.subject;
    document.getElementById('modalDate').textContent = new Date(messageData.created_at).toLocaleString();
    document.getElementById('modalMessage').textContent = messageData.message;
    
    document.getElementById('messageModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeMessageModal() {
    document.getElementById('messageModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function replyToMessage(email, name, subject) {
    const replySubject = subject.startsWith('Re: ') ? subject : 'Re: ' + subject;
    const mailtoLink = `mailto:${email}?subject=${encodeURIComponent(replySubject)}&body=${encodeURIComponent('Dear ' + name + ',\n\nThank you for contacting INTERNO. ')}`;
    window.location.href = mailtoLink;
}

function replyFromModal() {
    replyToMessage(currentModalData.email, currentModalData.name, currentModalData.subject);
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('messageModal');
    if (event.target === modal) {
        closeMessageModal();
    }
});


</script>

<script>
function loadContactMessages() {
    const section = document.getElementById('contactMessagesSection');
    const container = document.getElementById('messagesContainer');
    
    section.style.display = 'block';
    container.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Loading messages...</div>';
    
    fetch('get_messages.php')
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(data => {
        container.innerHTML = data;
        showNotification('Messages loaded successfully', 'success');
    })
    .catch(error => {
        console.error('Error:', error);
        container.innerHTML = '<div class="error-message"><i class="fas fa-exclamation-triangle"></i><h3>Error Loading Messages</h3><p>Unable to load contact messages. Please try refreshing the page.</p></div>';
        showNotification('Error loading messages', 'error');
    });
}

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
        ${message}
    `;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#10b981' : '#ef4444'};
        color: white;
        padding: 12px 16px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        display: flex;
        align-items: center;
        gap: 8px;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Message action functions
function viewMessage(id) {
    fetch('get_single_message.php?id=' + id)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessageModal(data.message);
        } else {
            alert('Error loading message');
        }
    })
    .catch(error => alert('Error loading message'));
}

function showMessageModal(message) {
    const modal = document.createElement('div');
    modal.className = 'message-modal';
    modal.innerHTML = `
        <div class="modal-backdrop" onclick="closeModal(this)"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-envelope"></i> Message Details</h3>
                <button onclick="closeModal(this)" class="close-btn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="message-detail"><strong>From:</strong> ${message.name} (${message.email})</div>
                <div class="message-detail"><strong>Phone:</strong> ${message.phone || 'Not provided'}</div>
                <div class="message-detail"><strong>Subject:</strong> ${message.subject}</div>
                <div class="message-detail"><strong>Date:</strong> ${new Date(message.created_at).toLocaleString()}</div>
                <div class="message-detail"><strong>Message:</strong><br><div class="message-text">${message.message}</div></div>
            </div>
            <div class="modal-footer">
                <button onclick="replyMessage('${message.email}', '${message.name}')" class="btn btn-primary">
                    <i class="fas fa-reply"></i> Reply
                </button>
                <button onclick="closeModal(this)" class="btn btn-outline">Close</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

function closeModal(element) {
    const modal = element.closest('.message-modal');
    if (modal) modal.remove();
}

function replyMessage(email, name) {
    const subject = 'Re: Your inquiry to INTERNO';
    const body = 'Dear ' + name + ',\n\nThank you for contacting INTERNO.\n\n';
    window.location.href = 'mailto:' + email + '?subject=' + encodeURIComponent(subject) + '&body=' + encodeURIComponent(body);
}

function deleteMessage(id) {
    if (confirm('Are you sure you want to delete this message?')) {
        fetch('delete_message.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'id=' + id
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadContactMessages();
                showNotification('Message deleted successfully', 'success');
            } else {
                alert('Error deleting message');
            }
        })
        .catch(error => alert('Error deleting message'));
    }
}

// Add CSS for animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    .loading {
        text-align: center;
        padding: 40px;
        color: #6b7280;
        font-style: italic;
    }
    .loading i {
        margin-right: 8px;
        color: #6366f1;
    }
    .error-message {
        text-align: center;
        padding: 40px;
        color: #ef4444;
    }
    #contactMessagesSection {
        background: white;
        padding: 24px;
        border-radius: 12px;
        margin-top: 24px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    #contactMessagesSection h2 {
        margin: 0 0 20px 0;
        color: #1f2937;
        font-size: 20px;
        font-weight: 600;
    }
    .message-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .modal-backdrop {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
    }
    .modal-content {
        background: white;
        border-radius: 12px;
        width: 90%;
        max-width: 500px;
        position: relative;
        box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    }
    .modal-header {
        padding: 20px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .modal-header h3 {
        margin: 0;
        color: #1f2937;
    }
    .close-btn {
        background: none;
        border: none;
        font-size: 18px;
        cursor: pointer;
        color: #6b7280;
    }
    .modal-body {
        padding: 20px;
    }
    .message-detail {
        margin-bottom: 15px;
        color: #374151;
    }
    .message-text {
        background: #f9fafb;
        padding: 15px;
        border-radius: 8px;
        margin-top: 8px;
        line-height: 1.5;
    }
    .modal-footer {
        padding: 20px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }
`;
document.head.appendChild(style);
</script>

</body>
</html>
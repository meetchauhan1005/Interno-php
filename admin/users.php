<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../user/login.php');
    exit();
}

$page_title = 'Users Management';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_user':
                try {
                    $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, first_name, last_name, phone, role, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $names = explode(' ', $_POST['full_name'], 2);
                    $stmt->execute([
                        $_POST['username'],
                        $_POST['email'],
                        $hashed_password,
                        $names[0] ?? '',
                        $names[1] ?? '',
                        $_POST['phone'],
                        $_POST['role'],
                        $_POST['is_active'] ? 'active' : 'inactive'
                    ]);
                    $success = "User added successfully!";
                } catch (PDOException $e) {
                    $error = "Error adding user: " . $e->getMessage();
                }
                break;
                
            case 'update_user':
                try {
                    $names = explode(' ', $_POST['full_name'], 2);
                    if (!empty($_POST['password'])) {
                        $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE users SET username=?, email=?, password=?, first_name=?, last_name=?, phone=?, role=?, status=? WHERE id=?");
                        $stmt->execute([
                            $_POST['username'],
                            $_POST['email'],
                            $hashed_password,
                            $names[0] ?? '',
                            $names[1] ?? '',
                            $_POST['phone'],
                            $_POST['role'],
                            $_POST['is_active'] ? 'active' : 'inactive',
                            $_POST['user_id']
                        ]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE users SET username=?, email=?, first_name=?, last_name=?, phone=?, role=?, status=? WHERE id=?");
                        $stmt->execute([
                            $_POST['username'],
                            $_POST['email'],
                            $names[0] ?? '',
                            $names[1] ?? '',
                            $_POST['phone'],
                            $_POST['role'],
                            $_POST['is_active'] ? 'active' : 'inactive',
                            $_POST['user_id']
                        ]);
                    }
                    $success = "User updated successfully!";
                } catch (PDOException $e) {
                    $error = "Error updating user: " . $e->getMessage();
                }
                break;
                
            case 'delete_user':
                try {
                    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
                    $stmt->execute([$_POST['user_id']]);
                    $success = "User deleted successfully!";
                } catch (PDOException $e) {
                    $error = "Error deleting user: " . $e->getMessage();
                }
                break;
        }
    }
}

try {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
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
                <a href="users.php" class="nav-tab active">
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
            <h1>Users</h1>
            <p>Manage customer accounts</p>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="content-section">
            <div class="section-header">
                <h3>All Users (<?php echo count($users); ?>)</h3>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Full Name</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td>#<?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '') ?: 'N/A'); ?></td>
                            <td>
                                <span class="role-badge role-<?php echo $user['role']; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)" class="btn-edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($user['role'] !== 'admin'): ?>
                                    <button onclick="deleteUser(<?php echo $user['id']; ?>)" class="btn-delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit User Modal -->
<div id="userModal" class="user-modal" style="display: none;">
    <div class="user-modal-content">
        <div class="user-modal-header">
            <h3 id="modalTitle">Add New User</h3>
            <button onclick="closeUserModal()" class="modal-close">&times;</button>
        </div>
        <form id="userForm" method="POST">
            <input type="hidden" name="action" id="formAction" value="add_user">
            <input type="hidden" name="user_id" id="userId">
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Full Name: <span class="required">*</span></label>
                    <input type="text" name="full_name" id="userFullName" required>
                </div>
                
                <div class="form-group">
                    <label>Username: <span class="required">*</span></label>
                    <input type="text" name="username" id="userUsername" required>
                </div>
                
                <div class="form-group">
                    <label>Email: <span class="required">*</span></label>
                    <input type="email" name="email" id="userEmail" required>
                </div>
                
                <div class="form-group">
                    <label>Phone:</label>
                    <input type="tel" name="phone" id="userPhone">
                </div>
                
                <div class="form-group">
                    <label>Password: <span id="passwordRequired">*</span></label>
                    <input type="password" name="password" id="userPassword" minlength="6">
                    <small id="passwordHelp">Leave blank to keep current password</small>
                </div>
                
                <div class="form-group">
                    <label>Role:</label>
                    <select name="role" id="userRole">
                        <option value="customer">Customer</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Status:</label>
                    <select name="is_active" id="userStatus">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
            
            <div class="modal-actions">
                <button type="button" onclick="closeUserModal()" class="btn btn-outline">Cancel</button>
                <button type="submit" class="btn btn-primary" id="submitBtn">Add User</button>
            </div>
        </form>
    </div>
</div>

<style>
body { margin: 0; background: #f8f9fa; font-family: 'Inter', sans-serif; }
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
.page-header { margin-bottom: 30px; }
.page-header h1 { font-size: 36px; color: #2c3e50; margin: 0 0 8px 0; font-weight: 700; }
.page-header p { color: #7f8c8d; margin: 0; font-size: 16px; }
.content-section { background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden; }
.section-header { padding: 25px; border-bottom: 1px solid #eee; }
.section-header h3 { margin: 0; font-size: 18px; color: #2c3e50; font-weight: 600; }
.table-responsive { overflow-x: auto; }
.table { width: 100%; border-collapse: collapse; }
.table th, .table td { padding: 15px 25px; text-align: left; border-bottom: 1px solid #eee; font-size: 14px; }
.table th { background: #f8f9fa; font-weight: 600; color: #666; }
.role-badge { padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; }
.role-admin { background: #e3f2fd; color: #1976d2; }
.role-customer { background: #f3e5f5; color: #7b1fa2; }
.btn-edit { background: #3498db; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; margin-right: 8px; font-size: 12px; }
.btn-delete { background: #e74c3c; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 12px; }
.btn-edit:hover { background: #2980b9; }
.btn-delete:hover { background: #c0392b; }

.action-buttons {
    display: flex;
    gap: 8px;
}

.btn-edit,
.btn-delete {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.btn-edit {
    background: #e3f2fd;
    color: #1976d2;
}

.btn-delete {
    background: #ffebee;
    color: #d32f2f;
}

.btn-edit:hover {
    background: #bbdefb;
}

.btn-delete:hover {
    background: #ffcdd2;
}

/* User Modal */
.user-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.user-modal-content {
    background: white;
    border-radius: 16px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 25px 50px rgba(0,0,0,0.25);
}

.user-modal-header {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
    padding: 24px 32px;
    border-radius: 16px 16px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.user-modal-header h3 {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
}

.modal-close {
    background: none;
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: background 0.2s;
}

.modal-close:hover {
    background: rgba(255, 255, 255, 0.2);
}

#userForm {
    padding: 32px;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
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

.required {
    color: #ef4444;
}

.form-group input,
.form-group select {
    padding: 12px 16px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.2s;
    background: #f9fafb;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #6366f1;
    background: white;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.form-group small {
    font-size: 12px;
    color: #6b7280;
    margin-top: 4px;
    display: none;
}

.modal-actions {
    display: flex;
    gap: 16px;
    justify-content: flex-end;
    margin-top: 32px;
    padding-top: 24px;
    border-top: 1px solid #e5e7eb;
}

.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 500;
}

.alert-success {
    background: #E8F5E8;
    color: #2E7D32;
    border-left: 4px solid #10b981;
}

.alert-danger {
    background: #FFEBEE;
    color: #C62828;
    border-left: 4px solid #ef4444;
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
    .form-grid {
        grid-template-columns: 1fr;
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
function openAddUserModal() {
    document.getElementById('modalTitle').textContent = 'Add New User';
    document.getElementById('formAction').value = 'add_user';
    document.getElementById('submitBtn').textContent = 'Add User';
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';
    document.getElementById('passwordRequired').style.display = 'inline';
    document.getElementById('passwordHelp').style.display = 'none';
    document.getElementById('userPassword').required = true;
    document.getElementById('userModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function editUser(user) {
    document.getElementById('modalTitle').textContent = 'Edit User';
    document.getElementById('formAction').value = 'update_user';
    document.getElementById('submitBtn').textContent = 'Update User';
    document.getElementById('userId').value = user.id;
    document.getElementById('userFullName').value = (user.first_name || '') + ' ' + (user.last_name || '');
    document.getElementById('userUsername').value = user.username;
    document.getElementById('userEmail').value = user.email;
    document.getElementById('userPhone').value = user.phone || '';
    document.getElementById('userRole').value = user.role;
    document.getElementById('userStatus').value = user.status === 'active' ? '1' : '0';
    document.getElementById('passwordRequired').style.display = 'none';
    document.getElementById('passwordHelp').style.display = 'block';
    document.getElementById('userPassword').required = false;
    document.getElementById('userModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeUserModal() {
    document.getElementById('userModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_user">
            <input type="hidden" name="user_id" value="${userId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

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

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('userModal');
    if (event.target === modal) {
        closeUserModal();
    }
});

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
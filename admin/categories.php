<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../user/login.php');
    exit();
}

$page_title = 'Categories Management';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_category':
                try {
                    $stmt = $pdo->prepare("INSERT INTO categories (name, description, slug, is_active) VALUES (?, ?, ?, ?)");
                    $slug = strtolower(str_replace(' ', '-', $_POST['name']));
                    $stmt->execute([$_POST['name'], $_POST['description'], $slug, $_POST['is_active']]);
                    $success = "Category added successfully!";
                } catch (PDOException $e) {
                    $error = "Error adding category: " . $e->getMessage();
                }
                break;
                
            case 'edit_category':
                try {
                    $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ?, is_active = ? WHERE id = ?");
                    $stmt->execute([$_POST['name'], $_POST['description'], $_POST['is_active'], $_POST['category_id']]);
                    $success = "Category updated successfully!";
                } catch (PDOException $e) {
                    $error = "Error updating category: " . $e->getMessage();
                }
                break;
                
            case 'delete_category':
                try {
                    // Check if category has products
                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
                    $stmt->execute([$_POST['category_id']]);
                    $productCount = $stmt->fetch()['count'];
                    
                    // Delete category (products will have category_id set to NULL due to foreign key constraint)
                    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
                    $stmt->execute([$_POST['category_id']]);
                    
                    if ($productCount > 0) {
                        $success = "Category deleted successfully! $productCount products have been moved to 'Uncategorized'.";
                    } else {
                        $success = "Category deleted successfully!";
                    }
                } catch (PDOException $e) {
                    $error = "Error deleting category: " . $e->getMessage();
                }
                break;
        }
    }
}

// Get categories with product counts
try {
    $stmt = $pdo->query("
        SELECT c.*, COUNT(p.id) as product_count 
        FROM categories c 
        LEFT JOIN products p ON c.id = p.category_id 
        GROUP BY c.id 
        ORDER BY c.name ASC
    ");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
    $error = "Error loading categories: " . $e->getMessage();
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
                <a href="categories.php" class="nav-tab active">
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
            <div class="header-title">
                <div class="title-icon">
                    <i class="fas fa-tags"></i>
                </div>
                <div>
                    <h1>Categories Management</h1>
                    <p>Manage product categories and organize your inventory</p>
                </div>
            </div>
            <div class="header-actions">
                <button onclick="openAddModal()" class="btn-add">
                    <i class="fas fa-plus"></i> Add Category
                </button>
            </div>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="categories-container">
            <?php if (!empty($categories)): ?>
            <div class="categories-grid">
                <?php foreach ($categories as $category): 
                    $icons = [
                        'bedroom' => 'fas fa-bed',
                        'office' => 'fas fa-briefcase',
                        'sofa-chairs' => 'fas fa-couch',
                        'storage' => 'fas fa-archive',
                        'tables' => 'fas fa-utensils'
                    ];
                    $icon = $icons[$category['slug']] ?? 'fas fa-tag';
                ?>
                <div class="category-card <?php echo $category['is_active'] ? 'active' : 'inactive'; ?>">
                    <div class="category-header">
                        <div class="category-icon">
                            <i class="<?php echo $icon; ?>"></i>
                        </div>
                        <div class="category-status">
                            <?php if ($category['is_active']): ?>
                                <span class="status-badge active">Active</span>
                            <?php else: ?>
                                <span class="status-badge inactive">Inactive</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="category-body">
                        <h3 class="category-name"><?php echo htmlspecialchars($category['name']); ?></h3>
                        <p class="category-description"><?php echo htmlspecialchars($category['description'] ?: 'No description available'); ?></p>
                        <div class="category-stats">
                            <div class="stat-item">
                                <i class="fas fa-box"></i>
                                <span><?php echo $category['product_count']; ?> Products</span>
                            </div>
                            <div class="stat-item">
                                <i class="fas fa-link"></i>
                                <span><?php echo htmlspecialchars($category['slug']); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="category-actions">
                        <button onclick="editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)" class="btn-edit">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button onclick="deleteCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name']); ?>', <?php echo $category['product_count']; ?>)" class="btn-delete">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-tags"></i>
                </div>
                <h3>No Categories Found</h3>
                <p>Start by creating your first product category</p>
                <button onclick="openAddModal()" class="btn-add">
                    <i class="fas fa-plus"></i> Add First Category
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add/Edit Category Modal -->
<div id="categoryModal" class="modal">
    <div class="modal-overlay" onclick="closeModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Add Category</h2>
            <button class="close-btn" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form method="POST" id="categoryForm">
                <input type="hidden" name="action" id="formAction" value="add_category">
                <input type="hidden" name="category_id" id="categoryId">
                
                <div class="form-group">
                    <label for="categoryName">Category Name *</label>
                    <input type="text" name="name" id="categoryName" required>
                </div>
                
                <div class="form-group">
                    <label for="categoryDescription">Description</label>
                    <textarea name="description" id="categoryDescription" rows="3" placeholder="Enter category description..."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="categoryStatus">Status</label>
                    <select name="is_active" id="categoryStatus">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i> Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Category Modal */
.auth-modal {
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

.auth-modal-content {
    background: white;
    border-radius: 16px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

.auth-modal-header {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
    padding: 24px 32px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.auth-modal-header h3 {
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
    padding: 8px;
    border-radius: 50%;
    transition: all 0.3s;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-close:hover {
    background: rgba(255, 255, 255, 0.2);
}

.auth-modal form {
    padding: 32px;
}

.form-group {
    margin-bottom: 24px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #1f2937;
    font-size: 14px;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s;
    outline: none;
    background: white;
    box-sizing: border-box;
    font-family: inherit;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
}

.modal-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 32px;
    padding-top: 24px;
    border-top: 1px solid #e5e7eb;
}

.btn {
    padding: 12px 24px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
    font-size: 14px;
    text-decoration: none;
    border: 2px solid transparent;
}

.btn-outline {
    background: white;
    color: #6366f1;
    border-color: #6366f1;
}

.btn-outline:hover {
    background: #6366f1;
    color: white;
}

.btn-primary {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
    border-color: transparent;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(99, 102, 241, 0.3);
}

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
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; background: white; padding: 30px; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
.header-title { display: flex; align-items: center; gap: 20px; }
.title-icon { width: 60px; height: 60px; background: linear-gradient(135deg, #6366f1, #8b5cf6); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; }
.page-header h1 { font-size: 32px; color: #1f2937; margin: 0 0 8px 0; font-weight: 700; }
.page-header p { color: #6b7280; margin: 0; font-size: 16px; }

.btn-add { background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; padding: 12px 24px; border-radius: 12px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px; transition: all 0.3s; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3); }
.btn-add:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4); }

.categories-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 24px; }
.category-card { background: white; border-radius: 16px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: 1px solid #e5e7eb; transition: all 0.3s; }
.category-card:hover { transform: translateY(-4px); box-shadow: 0 12px 40px rgba(0,0,0,0.15); }
.category-card.inactive { opacity: 0.7; border-color: #fbbf24; }

.category-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
.category-icon { width: 50px; height: 50px; background: linear-gradient(135deg, #6366f1, #8b5cf6); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; }
.status-badge { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
.status-badge.active { background: #d1fae5; color: #065f46; }
.status-badge.inactive { background: #fef3c7; color: #92400e; }

.category-name { font-size: 20px; font-weight: 700; color: #1f2937; margin: 0 0 8px 0; }
.category-description { color: #6b7280; margin: 0 0 16px 0; line-height: 1.5; }
.category-stats { display: flex; gap: 16px; margin-bottom: 20px; }
.stat-item { display: flex; align-items: center; gap: 6px; font-size: 14px; color: #6b7280; }

.category-actions { display: flex; gap: 8px; }
.btn-edit, .btn-delete { padding: 8px 16px; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 6px; transition: all 0.3s; }
.btn-edit { background: #3b82f6; color: white; }
.btn-delete { background: #ef4444; color: white; }
.btn-edit:hover { background: #2563eb; transform: translateY(-1px); }
.btn-delete:hover { background: #dc2626; transform: translateY(-1px); }

.empty-state { text-align: center; padding: 80px 40px; background: white; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
.empty-icon { width: 80px; height: 80px; background: #f3f4f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; }
.empty-icon i { font-size: 32px; color: #9ca3af; }
.empty-state h3 { color: #374151; margin-bottom: 10px; font-size: 24px; }
.empty-state p { color: #6b7280; font-size: 16px; margin-bottom: 24px; }

.alert { padding: 16px 20px; border-radius: 12px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px; font-weight: 500; }
.alert-success { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }
.alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #ef4444; }

.modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 9999; }
.modal.active { display: flex; align-items: center; justify-content: center; }
.modal-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(10px); }
.modal-content { background: white; border-radius: 16px; width: 90%; max-width: 500px; position: relative; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); border: 1px solid #e2e8f0; overflow: hidden; }
.modal-header { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; padding: 24px 32px; display: flex; justify-content: space-between; align-items: center; }
.modal-header h2 { margin: 0; font-size: 20px; font-weight: 600; color: white; }
.close-btn { background: none; border: none; color: white; font-size: 24px; cursor: pointer; padding: 8px; border-radius: 50%; transition: all 0.3s; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; }
.close-btn:hover { background: rgba(255, 255, 255, 0.2); }
.modal-body { padding: 32px; }

.modal-body .form-group { margin-bottom: 24px; }
.modal-body .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #1f2937; font-size: 14px; }
.modal-body .form-group input, .modal-body .form-group textarea, .modal-body .form-group select { width: 100%; padding: 12px 16px; border: 2px solid #d1d5db; border-radius: 8px; font-size: 14px; transition: all 0.3s; outline: none; background: white; box-sizing: border-box; font-family: inherit; }
.modal-body .form-group input:focus, .modal-body .form-group textarea:focus, .modal-body .form-group select:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); }
.modal-body .form-group textarea { resize: vertical; min-height: 80px; }

.modal-body .form-actions { display: flex; gap: 12px; justify-content: flex-end; margin-top: 32px; padding-top: 24px; border-top: 1px solid #e5e7eb; }
.btn-cancel, .btn-save { padding: 12px 24px; border-radius: 8px; cursor: pointer; font-weight: 500; display: flex; align-items: center; gap: 8px; transition: all 0.3s; font-size: 14px; text-decoration: none; border: 2px solid transparent; }
.btn-cancel { background: #6b7280; color: white; }
.btn-save { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; }
.btn-cancel:hover { background: #4b5563; }
.btn-save:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(99, 102, 241, 0.3); }

@media (max-width: 768px) {
    .admin-content { padding: 20px; }
    .page-header { flex-direction: column; gap: 20px; text-align: center; }
    .categories-grid { grid-template-columns: 1fr; }
    .category-actions { flex-direction: column; }
}
</style>

<script>
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Category';
    document.getElementById('formAction').value = 'add_category';
    document.getElementById('categoryForm').reset();
    document.getElementById('categoryId').value = '';
    document.getElementById('categoryModal').classList.add('active');
}

function editCategory(category) {
    document.getElementById('modalTitle').textContent = 'Edit Category';
    document.getElementById('formAction').value = 'edit_category';
    document.getElementById('categoryId').value = category.id;
    document.getElementById('categoryName').value = category.name;
    document.getElementById('categoryDescription').value = category.description || '';
    document.getElementById('categoryStatus').value = category.is_active;
    document.getElementById('categoryModal').classList.add('active');
}

function deleteCategory(id, name, productCount) {
    let message = `Are you sure you want to delete the "${name}" category?`;
    if (productCount > 0) {
        message += ` This will remove the category from ${productCount} products. This action cannot be undone.`;
    } else {
        message += ` This action cannot be undone.`;
    }
    
    if (confirm(message)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_category">
            <input type="hidden" name="category_id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function closeModal() {
    document.getElementById('categoryModal').classList.remove('active');
}

// Auto-hide alerts
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            alert.style.transition = 'all 0.3s ease';
            setTimeout(() => alert.remove(), 300);
        }, 4000);
    });
});
</script>

</body>
</html>

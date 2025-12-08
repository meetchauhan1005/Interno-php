<?php
session_start();
require_once '../includes/config.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../user/login.php');
    exit();
}

$page_title = 'Product Management';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $imageName = $_POST['image'] ?? '';
        
        // Handle file upload
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../assets/images/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileExtension = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($fileExtension, $allowedExtensions)) {
                $imageName = uniqid() . '.' . $fileExtension;
                $uploadPath = $uploadDir . $imageName;
                
                if (move_uploaded_file($_FILES['image_file']['tmp_name'], $uploadPath)) {
                    // File uploaded successfully
                } else {
                    $error = "Failed to upload image";
                }
            } else {
                $error = "Invalid image format. Please use JPG, PNG, GIF, or WebP";
            }
        }
        
        if (!isset($error)) {
            switch ($_POST['action']) {
                case 'add_product':
                    try {
                        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, category_id, stock_quantity, image, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([
                            $_POST['name'],
                            $_POST['description'],
                            $_POST['price'],
                            $_POST['category_id'] ?: null,
                            $_POST['stock_quantity'],
                            $imageName,
                            $_POST['status']
                        ]);
                        $success = "Product added successfully!";
                    } catch (PDOException $e) {
                        $error = "Error adding product: " . $e->getMessage();
                    }
                    break;
                    
                case 'update_product':
                    try {
                        // If no new image uploaded, keep existing image
                        if (!isset($_FILES['image_file']) || $_FILES['image_file']['error'] !== UPLOAD_ERR_OK) {
                            if (empty($_POST['image'])) {
                                // Get current image from database
                                $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
                                $stmt->execute([$_POST['product_id']]);
                                $currentProduct = $stmt->fetch();
                                $imageName = $currentProduct['image'];
                            } else {
                                $imageName = $_POST['image'];
                            }
                        }
                        
                        $stmt = $pdo->prepare("UPDATE products SET name=?, description=?, price=?, category_id=?, stock_quantity=?, image=?, status=? WHERE id=?");
                        $stmt->execute([
                            $_POST['name'],
                            $_POST['description'],
                            $_POST['price'],
                            $_POST['category_id'] ?: null,
                            $_POST['stock_quantity'],
                            $imageName,
                            $_POST['status'],
                            $_POST['product_id']
                        ]);
                        $success = "Product updated successfully!";
                    } catch (PDOException $e) {
                        $error = "Error updating product: " . $e->getMessage();
                    }
                    break;
                    
                case 'delete_product':
                    try {
                        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
                        $stmt->execute([$_POST['product_id']]);
                        $success = "Product deleted successfully!";
                    } catch (PDOException $e) {
                        $error = "Error deleting product: " . $e->getMessage();
                    }
                    break;
                    
                case 'toggle_featured':
                    try {
                        // Ensure column exists first
                        $pdo->exec("ALTER TABLE products ADD COLUMN is_featured TINYINT(1) DEFAULT 0");
                    } catch (PDOException $e) {
                        // Column exists, continue
                    }
                    
                    try {
                        $stmt = $pdo->prepare("UPDATE products SET is_featured = ? WHERE id = ?");
                        $stmt->execute([$_POST['featured'], $_POST['product_id']]);
                        $success = "Featured status updated successfully!";
                    } catch (PDOException $e) {
                        $error = "Error updating featured status: " . $e->getMessage();
                    }
                    break;
            }
        }
    }
}

// Get all products
try {
    // Ensure is_featured column exists
    try {
        $pdo->exec("ALTER TABLE products ADD COLUMN is_featured TINYINT(1) DEFAULT 0");
    } catch (PDOException $e) {
        // Column already exists, ignore error
    }
    
    // Insert default categories if they don't exist
    $default_categories = ['Bedroom', 'Office', 'Sofas & Chairs', 'Storage', 'Tables'];
    foreach ($default_categories as $cat_name) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE name = ?");
        $stmt->execute([$cat_name]);
        if ($stmt->fetchColumn() == 0) {
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->execute([$cat_name]);
        }
    }
    
    $stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC");
    $products = $stmt->fetchAll();
    
    // Get categories for dropdown
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    $products = [];
    $categories = [];
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
    <link rel="stylesheet" href="../assets/css/style.css">
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
                <a href="products.php" class="nav-tab active">
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
            <div>
                <h1>Product Management</h1>
                <p>Manage your product inventory</p>
            </div>
            <button onclick="openAddProductModal()" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Product
            </button>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="products-table-container">
            <table class="products-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Featured</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <div class="product-image-cell">
                                <?php if ($product['image'] && file_exists('../assets/images/' . $product['image'])): ?>
                                    <img src="../assets/images/<?php echo htmlspecialchars($product['image']); ?>" alt="Product">
                                <?php else: ?>
                                    <div class="no-image">No Image</div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div class="product-name-cell">
                                <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                <small><?php echo substr(htmlspecialchars($product['description']), 0, 50) . '...'; ?></small>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($product['category_name'] ?: 'Uncategorized'); ?></td>
                        <td>₹<?php echo number_format($product['price'], 2); ?></td>
                        <td>
                            <span class="stock-badge <?php echo $product['stock_quantity'] <= 10 ? 'low-stock' : 'in-stock'; ?>">
                                <?php echo $product['stock_quantity']; ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $product['status']; ?>">
                                <?php echo ucfirst($product['status']); ?>
                            </span>
                        </td>
                        <td>
                            <button onclick="toggleFeatured(<?php echo $product['id']; ?>, <?php echo isset($product['is_featured']) ? $product['is_featured'] : 0; ?>)" 
                                    class="btn-featured <?php echo (isset($product['is_featured']) && $product['is_featured']) ? 'active' : ''; ?>">
                                <i class="fas fa-star"></i>
                            </button>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)" class="btn-edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteProduct(<?php echo $product['id']; ?>)" class="btn-delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Product Modal -->
<div id="productModal" class="product-modal" style="display: none;">
    <div class="product-modal-content">
        <div class="product-modal-header">
            <h3 id="modalTitle">Add New Product</h3>
            <button onclick="closeProductModal()" class="modal-close">&times;</button>
        </div>
        <form id="productForm" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" id="formAction" value="add_product">
            <input type="hidden" name="product_id" id="productId">
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Product Name: <span class="required">*</span></label>
                    <input type="text" name="name" id="productName" required>
                </div>
                
                <div class="form-group">
                    <label>Stock Quantity:</label>
                    <input type="number" name="stock_quantity" id="stockQuantity" value="0" min="0">
                </div>
                
                <div class="form-group">
                    <label>Price (₹): <span class="required">*</span></label>
                    <input type="number" name="price" id="productPrice" step="0.01" placeholder="Enter price in rupees" required>
                </div>
                
                <div class="form-group">
                    <label>Status:</label>
                    <select name="status" id="productStatus">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                

                
                <div class="form-group">
                    <label>Category:</label>
                    <select name="category_id" id="productCategory">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Upload Image:</label>
                    <div class="file-input-container">
                        <input type="file" name="image_file" id="imageFile" accept="image/*" onchange="handleImageUpload(this)">
                        <div class="file-input-text">
                            <span>Or enter filename manually:</span>
                            <input type="text" name="image" id="productImage" placeholder="e.g., sofa1.jpg">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-group full-width">
                <label>Description: <span class="required">*</span></label>
                <textarea name="description" id="productDescription" rows="4" required></textarea>
            </div>
            
            <div class="modal-actions">
                <button type="button" onclick="closeProductModal()" class="btn btn-outline">Cancel</button>
                <button type="submit" class="btn btn-primary" id="submitBtn">Add Product</button>
            </div>
        </form>
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

.products-table-container {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.products-table {
    width: 100%;
    border-collapse: collapse;
}

.products-table th {
    background: #f8f9fa;
    padding: 16px;
    text-align: left;
    font-weight: 600;
    color: #495057;
    border-bottom: 1px solid #dee2e6;
}

.products-table td {
    padding: 16px;
    border-bottom: 1px solid #dee2e6;
    vertical-align: middle;
}

.product-image-cell img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 8px;
}

.no-image {
    width: 50px;
    height: 50px;
    background: #f8f9fa;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    color: #6c757d;
}

.product-name-cell strong {
    display: block;
    margin-bottom: 4px;
}

.product-name-cell small {
    color: #6c757d;
}

.stock-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.stock-badge.in-stock {
    background: #d4edda;
    color: #155724;
}

.stock-badge.low-stock {
    background: #f8d7da;
    color: #721c24;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    text-transform: capitalize;
}

.status-badge.active {
    background: #d4edda;
    color: #155724;
}

.status-badge.inactive {
    background: #f8d7da;
    color: #721c24;
}

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

/* Product Modal */
.product-modal {
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

.product-modal-content {
    background: white;
    border-radius: 16px;
    width: 90%;
    max-width: 700px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 25px 50px rgba(0,0,0,0.25);
}

.product-modal-header {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
    padding: 24px 32px;
    border-radius: 16px 16px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.product-modal-header h3 {
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

#productForm {
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

.form-group.full-width {
    grid-column: 1 / -1;
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
.form-group select,
.form-group textarea {
    padding: 12px 16px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.2s;
    background: #f9fafb;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #6366f1;
    background: white;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.file-input-container {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.file-input-text span {
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 4px;
    display: block;
}

.checkbox-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 8px;
}

.checkbox-wrapper input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: #6366f1;
}

.checkbox-text {
    font-weight: 500;
    cursor: pointer;
    margin: 0;
}

.help-text {
    font-size: 12px;
    color: #6b7280;
    margin-top: 4px;
}

.featured-badge {
    background: linear-gradient(135deg, #fbbf24, #f59e0b);
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 4px;
}

.not-featured {
    color: #6b7280;
    font-size: 12px;
    font-weight: 500;
    background: #f3f4f6;
    padding: 4px 8px;
    border-radius: 12px;
}

.btn-featured {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    background: #f3f4f6;
    color: #9ca3af;
}

.btn-featured.active {
    background: #fbbf24;
    color: white;
}

.btn-featured:hover {
    transform: scale(1.1);
}

.modal-actions {
    display: flex;
    gap: 16px;
    justify-content: flex-end;
    margin-top: 32px;
    padding-top: 24px;
    border-top: 1px solid #e5e7eb;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .admin-content {
        padding: 20px;
    }
    
    .products-table-container {
        overflow-x: auto;
    }
}
</style>

<script>
function openAddProductModal() {
    document.getElementById('modalTitle').textContent = 'Add New Product';
    document.getElementById('formAction').value = 'add_product';
    document.getElementById('submitBtn').textContent = 'Add Product';
    document.getElementById('productForm').reset();
    document.getElementById('productId').value = '';
    document.getElementById('productModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function editProduct(product) {
    document.getElementById('modalTitle').textContent = 'Edit Product';
    document.getElementById('formAction').value = 'update_product';
    document.getElementById('submitBtn').textContent = 'Update Product';
    document.getElementById('productId').value = product.id;
    document.getElementById('productName').value = product.name;
    document.getElementById('productDescription').value = product.description;
    document.getElementById('productPrice').value = product.price;
    document.getElementById('productCategory').value = product.category_id || '';
    document.getElementById('stockQuantity').value = product.stock_quantity;
    document.getElementById('productImage').value = product.image || '';
    document.getElementById('productStatus').value = product.status;
    document.getElementById('productModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeProductModal() {
    document.getElementById('productModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function deleteProduct(productId) {
    if (confirm('Are you sure you want to delete this product?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_product">
            <input type="hidden" name="product_id" value="${productId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function handleImageUpload(input) {
    if (input.files && input.files[0]) {
        const fileName = input.files[0].name;
        document.getElementById('productImage').value = fileName;
    }
}

function toggleFeatured(productId, currentStatus) {
    const newStatus = currentStatus ? 0 : 1;
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="action" value="toggle_featured">
        <input type="hidden" name="product_id" value="${productId}">
        <input type="hidden" name="featured" value="${newStatus}">
    `;
    document.body.appendChild(form);
    form.submit();
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('productModal');
    if (event.target === modal) {
        closeProductModal();
    }
});
</script>

</body>
</html>
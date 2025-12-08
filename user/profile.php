<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$page_title = 'My Profile';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $pincode = trim($_POST['pincode']);
    
    $errors = [];
    
    if (empty($full_name)) $errors[] = "Full name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    
    // Check if email exists for other users
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->fetch()) {
        $errors[] = "Email already exists";
    }
    
    if (empty($errors)) {
        try {
            $names = explode(' ', $full_name, 2);
            $first_name = $names[0] ?? '';
            $last_name = $names[1] ?? '';
            
            // Check which columns exist and build query accordingly
            $columns = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
            
            $updateFields = ['first_name = ?', 'last_name = ?', 'email = ?'];
            $updateValues = [$first_name, $last_name, $email];
            
            if (in_array('phone', $columns)) {
                $updateFields[] = 'phone = ?';
                $updateValues[] = $phone;
            }
            if (in_array('address', $columns)) {
                $updateFields[] = 'address = ?';
                $updateValues[] = $address;
            }
            if (in_array('city', $columns)) {
                $updateFields[] = 'city = ?';
                $updateValues[] = $city;
            }
            if (in_array('state', $columns)) {
                $updateFields[] = 'state = ?';
                $updateValues[] = $state;
            }
            if (in_array('zip_code', $columns)) {
                $updateFields[] = 'zip_code = ?';
                $updateValues[] = $pincode;
            }
            
            $updateValues[] = $user_id;
            
            $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($updateValues);
            
            $_SESSION['username'] = $full_name;
            $success = "Profile updated successfully!";
            
            // Redirect to refresh the page and show updated data
            header('Location: profile.php?updated=1');
            exit();
        } catch (PDOException $e) {
            error_log("Profile update error: " . $e->getMessage());
            $errors[] = "Profile update failed. Please try again.";
        }
    }
}

// Get user data
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    // Get user statistics
    $stmt = $pdo->prepare("SELECT COUNT(*) as order_count FROM orders WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $order_count = $stmt->fetch()['order_count'];
    
    $wishlist_count = 0;
    
    $stmt = $pdo->prepare("SELECT SUM(total_amount) as total_spent FROM orders WHERE user_id = ? AND status != 'cancelled'");
    $stmt->execute([$_SESSION['user_id']]);
    $total_spent = $stmt->fetch()['total_spent'] ?: 0;
    
} catch (PDOException $e) {
    $user = null;
}

include '../includes/header.php';
?>

<section class="section" style="padding-top: 2rem;">
    <div class="container">
        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-avatar">
                    <div class="avatar-circle">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: $user['username']); ?></h1>
                    <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
                    <?php if (!empty($user['phone'])): ?>
                        <p class="profile-phone"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($user['phone']); ?></p>
                    <?php endif; ?>
                    <p class="profile-member">Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
                </div>
            </div>
            
            <div class="profile-stats">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $order_count; ?></div>
                    <div class="stat-label">Orders</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo date('M Y', strtotime($user['created_at'])); ?></div>
                    <div class="stat-label">Member Since</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">₹<?php echo number_format($total_spent); ?></div>
                    <div class="stat-label">Total Spent</div>
                </div>
            </div>
            
            <div class="profile-content">
                <div class="profile-form-section">
                    <h2>Personal Information</h2>
                    
                    <?php if (isset($_GET['updated'])): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            Profile updated successfully!
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo implode('<br>', $errors); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="profile-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="full_name">Full Name *</label>
                                <input type="text" id="full_name" name="full_name" 
                                       value="<?php echo htmlspecialchars(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: $user['username']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address *</label>
                                <input type="email" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="pincode">PIN Code</label>
                                <input type="text" id="pincode" name="pincode" 
                                       value="<?php echo htmlspecialchars($user['zip_code'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">City</label>
                                <input type="text" id="city" name="city" 
                                       value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="state">State</label>
                                <input type="text" id="state" name="state" 
                                       value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                            <a href="orders.php" class="btn btn-outline btn-lg">
                                <i class="fas fa-box"></i> View Orders
                            </a>
                        </div>
                    </form>
                </div>
                
                <div class="profile-actions">
                    <div class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <h3>My Orders</h3>
                        <p>Track your orders and view order history</p>
                        <a href="orders.php" class="btn btn-secondary">View Orders</a>
                    </div>
                    

                    
                    <div class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <h3>Shopping Cart</h3>
                        <p>Review items in your cart</p>
                        <a href="cart.php" class="btn btn-secondary">View Cart</a>
                    </div>
                    
                    <div class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                        <h3>Change Password</h3>
                        <p>Update your account password</p>
                        <button onclick="showPasswordModal()" class="btn btn-accent">Change Password</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Password Change Modal -->
<div id="passwordModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Change Password</h3>
            <button onclick="closePasswordModal()" class="modal-close">&times;</button>
        </div>
        <form id="passwordForm" onsubmit="changePassword(event)">
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required minlength="6">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <div class="modal-actions">
                <button type="button" onclick="closePasswordModal()" class="btn btn-outline">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Password</button>
            </div>
        </form>
    </div>
</div>

<style>
.profile-container {
    max-width: 1000px;
    margin: 0 auto;
}

.profile-header {
    background: var(--gradient-primary);
    color: var(--light);
    padding: 3rem 2rem;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    gap: 2rem;
    margin-bottom: 2rem;
}

.profile-avatar {
    flex-shrink: 0;
}

.avatar-circle {
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: var(--light);
}

.profile-info h1 {
    font-size: 2rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.profile-email {
    font-size: 1.1rem;
    opacity: 0.9;
    margin-bottom: 0.25rem;
}

.profile-phone {
    font-size: 1rem;
    opacity: 0.9;
    margin-bottom: 0.25rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.profile-member {
    font-size: 0.9rem;
    opacity: 0.8;
}

.profile-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-bottom: 3rem;
}

.stat-item {
    background: var(--light);
    padding: 1.5rem;
    border-radius: var(--radius-lg);
    text-align: center;
    border: 1px solid var(--gray-200);
}

.stat-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 0.5rem;
}

.stat-label {
    color: var(--gray-600);
    font-size: 0.9rem;
}

.profile-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 3rem;
}

.profile-form-section {
    background: var(--light);
    padding: 2rem;
    border-radius: var(--radius-lg);
    border: 1px solid var(--gray-200);
}

.profile-form-section h2 {
    color: var(--primary);
    margin-bottom: 1.5rem;
    font-size: 1.5rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
}

.profile-actions {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.action-card {
    background: var(--light);
    padding: 1.5rem;
    border-radius: var(--radius-lg);
    border: 1px solid var(--gray-200);
    text-align: center;
    transition: var(--transition);
}

.action-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
    border-color: var(--primary);
}

.action-icon {
    width: 50px;
    height: 50px;
    background: var(--gradient-primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    color: var(--light);
    font-size: 1.25rem;
}

.action-card h3 {
    color: var(--primary);
    margin-bottom: 0.5rem;
    font-size: 1.1rem;
}

.action-card p {
    color: var(--gray-600);
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

/* Modal Styles */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.modal-content {
    background: var(--light);
    border-radius: var(--radius-lg);
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem 2rem;
    border-bottom: 1px solid var(--gray-200);
}

.modal-header h3 {
    color: var(--primary);
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--gray-500);
}

.modal form {
    padding: 2rem;
}

.modal-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 1.5rem;
}

@media (max-width: 768px) {
    .profile-header {
        flex-direction: column;
        text-align: center;
    }
    
    .profile-content {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .profile-stats {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function showPasswordModal() {
    document.getElementById('passwordModal').style.display = 'flex';
}

function closePasswordModal() {
    document.getElementById('passwordModal').style.display = 'none';
    document.getElementById('passwordForm').reset();
}

function changePassword(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const newPassword = formData.get('new_password');
    const confirmPassword = formData.get('confirm_password');
    
    if (newPassword !== confirmPassword) {
        showNotification('Passwords do not match', 'error');
        return;
    }
    
    fetch('change_password.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Password updated successfully', 'success');
            closePasswordModal();
        } else {
            showNotification(data.message || 'Failed to update password', 'error');
        }
    })
    .catch(error => {
        showNotification('Error updating password', 'error');
    });
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

// Close modal when clicking outside
document.getElementById('passwordModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePasswordModal();
    }
});
</script>

<?php include '../includes/footer.php'; ?>
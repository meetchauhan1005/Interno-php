<?php
// Include config if not already included
if (!defined('DB_HOST')) {
    $currentDir = dirname($_SERVER['PHP_SELF']);
    $configPath = '';
    if (strpos($currentDir, '/user') !== false || strpos($currentDir, '/admin') !== false) {
        $configPath = '../';
    }
    require_once $configPath . 'includes/config.php';
}
?>
<?php
// Determine the correct path to assets based on current directory
$currentDir = dirname($_SERVER['PHP_SELF']);
$assetsPath = '';
if (strpos($currentDir, '/user') !== false || strpos($currentDir, '/admin') !== false) {
    $assetsPath = '../';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title><?php echo isset($page_title) ? $page_title . ' - INTERNO' : 'INTERNO - Premium Furniture Store'; ?></title>
    <meta name="description" content="INTERNO - Your trusted destination for premium furniture with fast delivery across India.">
    <link rel="icon" href="data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20viewBox='0%200%2016%2016'%3E%3Ctext%20x='8'%20y='14'%20font-size='12'%20text-anchor='middle'%3E🛋️%3C/text%3E%3C/svg%3E">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="<?php echo $assetsPath; ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Mobile optimizations -->
    <meta name="format-detection" content="telephone=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="theme-color" content="#6366f1">
</head>
<body>
    <header>
        <nav>
            <a href="<?php echo $assetsPath; ?>index.php" class="logo">
                <span class="logo-emoji">🛋️</span>
                <span class="logo-text">INTERNO</span>
            </a>
            
            <div class="nav-center">
                <ul class="nav-links">
                    <li><a href="<?php echo $assetsPath; ?>index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                        Home
                    </a></li>
                    <li><a href="<?php echo $assetsPath; ?>products.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">
                        Products
                    </a></li>
                    <li><a href="<?php echo $assetsPath; ?>categories.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">
                        Categories
                    </a></li>
                    <li><a href="<?php echo $assetsPath; ?>about.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>">
                        About
                    </a></li>
                    <li><a href="<?php echo $assetsPath; ?>contact.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>">
                        Contact
                    </a></li>
                    <li><a href="#" onclick="openAdminModal()" class="admin-nav-link">
                        Admin
                    </a></li>
                </ul>
                

            </div>
            
            <div class="user-actions">
                <div class="search-container">
                    <input type="text" id="search" placeholder="Search furniture..." onkeypress="handleSearch(event)">
                    <button class="search-btn" onclick="performSearch()">
                        <i class="fas fa-search"></i>
                    </button>
                </div>

                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo $assetsPath; ?>user/cart.php" class="cart-icon" title="Shopping Cart">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count" id="cartCount">0</span>
                    </a>
                    
                    <div class="user-menu" style="position: relative;">
                        <button class="btn btn-secondary btn-sm" onclick="toggleUserMenu()" id="userMenuBtn">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                            <i class="fas fa-chevron-down" style="font-size: 0.75rem;"></i>
                        </button>
                        <div id="userDropdown" class="user-dropdown" style="display: none; position: absolute; top: 100%; right: 0; background: white; border: 1px solid var(--gray-200); border-radius: var(--radius); box-shadow: var(--shadow-lg); min-width: 200px; z-index: 1000;">
                            <a href="<?php echo $assetsPath; ?>user/profile.php" class="dropdown-item">
                                <i class="fas fa-user"></i> My Profile
                            </a>
                            <a href="<?php echo $assetsPath; ?>user/orders.php" class="dropdown-item">
                                <i class="fas fa-box"></i> My Orders
                            </a>
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                            <hr style="margin: 0.5rem 0; border: none; border-top: 1px solid var(--gray-200);">
                            <a href="<?php echo strpos($_SERVER['REQUEST_URI'], 'admin') !== false ? '' : $assetsPath . 'admin/'; ?>dashboard.php" class="dropdown-item">
                                <i class="fas fa-shield-alt"></i> Admin Panel
                            </a>
                            <?php endif; ?>
                            <hr style="margin: 0.5rem 0; border: none; border-top: 1px solid var(--gray-200);">
                            <a href="<?php echo $assetsPath; ?>user/logout.php" class="dropdown-item text-danger">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <button onclick="openLoginModal()" class="btn btn-login btn-sm">
                        Login
                    </button>
                    <button onclick="openRegisterModal()" class="btn btn-register btn-sm">
                        Register
                    </button>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    
    <!-- Admin Modal -->
    <div id="adminModal" class="admin-modal" style="display: none;">
        <div class="admin-modal-content">
            <div class="admin-modal-header">
                <h3>Admin Access</h3>
                <button onclick="closeAdminModal()" class="modal-close">&times;</button>
            </div>
            <form onsubmit="adminLogin(event)">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" id="adminUsername" placeholder="Enter admin username" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <div class="password-input-container">
                        <input type="password" id="adminPassword" placeholder="Enter admin password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('adminPassword')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" onclick="closeAdminModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary">Access Admin</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Login Modal -->
    <div id="loginModal" class="auth-modal" style="display: none;">
        <div class="auth-modal-content">
            <div class="auth-modal-header">
                <h3>Welcome Back</h3>
                <button onclick="closeLoginModal()" class="modal-close">&times;</button>
            </div>
            <form onsubmit="handleLogin(event)">
                <div class="form-group">
                    <label>Username or Email</label>
                    <input type="text" id="loginUsername" placeholder="Enter username or email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <div class="password-input-container">
                        <input type="password" id="loginPassword" placeholder="Enter password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('loginPassword')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="forgot-password-link">
                        <a href="#" onclick="openForgotPassword()">Forgot Password?</a>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" onclick="closeLoginModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary">Sign In</button>
                </div>
            </form>
            <div class="modal-footer">
                <p>Don't have an account? <a href="#" onclick="switchToRegister()">Create Account</a></p>
            </div>
        </div>
    </div>
    
    <!-- Register Modal -->
    <div id="registerModal" class="auth-modal" style="display: none;">
        <div class="auth-modal-content register-modal">
            <div class="auth-modal-header">
                <h3>Create Account</h3>
                <button onclick="closeRegisterModal()" class="modal-close">&times;</button>
            </div>
            <form onsubmit="handleRegister(event)">
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" id="regFullName" placeholder="Enter full name" required>
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" id="regUsername" placeholder="Choose username" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="regEmail" placeholder="Enter email" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Password</label>
                        <div class="password-input-container">
                            <input type="password" id="regPassword" placeholder="Create password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('regPassword')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <div class="password-input-container">
                            <input type="password" id="regConfirmPassword" placeholder="Confirm password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('regConfirmPassword')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" onclick="closeRegisterModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Account</button>
                </div>
            </form>
            <div class="modal-footer">
                <p>Already have an account? <a href="#" onclick="switchToLogin()">Sign In</a></p>
            </div>
        </div>
    </div>
    
    <!-- Forgot Password Modal -->
    <div id="forgotPasswordModal" class="auth-modal" style="display: none;">
        <div class="auth-modal-content">
            <div class="auth-modal-header">
                <h3>Reset Password</h3>
                <button onclick="closeForgotPasswordModal()" class="modal-close">&times;</button>
            </div>
            <form onsubmit="handleForgotPassword(event)">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" id="forgotEmail" placeholder="Enter your email address" required>
                </div>
                <div class="modal-actions">
                    <button type="button" onclick="closeForgotPasswordModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Reset Link</button>
                </div>
            </form>
            <div class="modal-footer">
                <p>Remember your password? <a href="#" onclick="switchToLoginFromForgot()">Sign In</a></p>
            </div>
        </div>
    </div>
    
    <main>

<style>
.dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    color: var(--gray-700);
    text-decoration: none;
    font-size: 0.875rem;
    font-family: var(--font-primary);
    font-weight: 500;
    transition: background-color 0.2s;
}

.dropdown-item:hover {
    background: var(--gray-50);
}

.dropdown-item.text-danger {
    color: var(--danger);
}

.user-dropdown {
    animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<script>
function toggleUserMenu() {
    const dropdown = document.getElementById('userDropdown');
    dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
}

function handleSearch(event) {
    if (event.key === 'Enter') {
        performSearch();
    }
}

function performSearch() {
    const searchTerm = document.getElementById('search').value.trim();
    if (searchTerm) {
        window.location.href = '<?php echo $assetsPath; ?>products.php?search=' + encodeURIComponent(searchTerm);
    }
}

function updateCartCount() {
    <?php if (isset($_SESSION['user_id'])): ?>
    fetch('<?php echo $assetsPath; ?>includes/cart_handler.php?action=count')
    .then(response => response.json())
    .then(data => {
        const cartCount = document.getElementById('cartCount');
        if (cartCount) {
            cartCount.textContent = data.count || 0;
        }
    })
    .catch(error => console.log('Cart count update failed'));
    <?php endif; ?>
}

// Mobile menu functions
function toggleMobileMenu() {
    const navLinks = document.getElementById('navLinks');
    const toggle = document.querySelector('.mobile-menu-toggle');
    const icon = toggle.querySelector('i');
    
    navLinks.classList.toggle('active');
    toggle.classList.toggle('active');
    
    if (navLinks.classList.contains('active')) {
        icon.classList.remove('fa-bars');
        icon.classList.add('fa-times');
        document.body.style.overflow = 'hidden';
    } else {
        icon.classList.remove('fa-times');
        icon.classList.add('fa-bars');
        document.body.style.overflow = 'auto';
    }
}



// Close mobile menu when clicking on links
document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('.nav-links a');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                toggleMobileMenu();
            }
        });
    });
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(event) {
        const nav = document.querySelector('nav');
        const navLinks = document.getElementById('navLinks');

        
        if (!nav.contains(event.target) && navLinks.classList.contains('active')) {
            toggleMobileMenu();
        }
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            const navLinks = document.getElementById('navLinks');
            const toggle = document.querySelector('.mobile-menu-toggle');
            const icon = toggle.querySelector('i');
            
            navLinks.classList.remove('active');
            toggle.classList.remove('active');
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
            document.body.style.overflow = 'auto';
        }
    });
});

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const userMenu = document.querySelector('.user-menu');
    const dropdown = document.getElementById('userDropdown');
    
    if (userMenu && dropdown && !userMenu.contains(event.target)) {
        dropdown.style.display = 'none';
    }
});

// Update cart count on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
});

// Admin Modal Functions
function openAdminModal() {
    document.getElementById('adminModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeAdminModal() {
    document.getElementById('adminModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    document.getElementById('adminUsername').value = '';
    document.getElementById('adminPassword').value = '';
}

function adminLogin(event) {
    event.preventDefault();
    const username = document.getElementById('adminUsername').value;
    const password = document.getElementById('adminPassword').value;
    
    const formData = new FormData();
    formData.append('username', username);
    formData.append('password', password);
    
    const currentPath = window.location.pathname;
    let loginPath = 'admin/admin_login.php';
    let dashboardPath = 'admin/dashboard.php';
    
    if (currentPath.includes('/user/') || currentPath.includes('/admin/')) {
        loginPath = '../admin/admin_login.php';
        dashboardPath = '../admin/dashboard.php';
    }
    
    fetch(loginPath, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = dashboardPath;
        } else {
            alert(data.message || 'Invalid admin credentials');
        }
    })
    .catch(error => {
        alert('Admin login error occurred');
    });
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const adminModal = document.getElementById('adminModal');
    const loginModal = document.getElementById('loginModal');
    const registerModal = document.getElementById('registerModal');
    const forgotModal = document.getElementById('forgotPasswordModal');
    
    if (event.target === adminModal) closeAdminModal();
    if (event.target === loginModal) closeLoginModal();
    if (event.target === registerModal) closeRegisterModal();
    if (event.target === forgotModal) closeForgotPasswordModal();
});

// Login Modal Functions
function openLoginModal() {
    document.getElementById('loginModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeLoginModal() {
    document.getElementById('loginModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    document.getElementById('loginUsername').value = '';
    document.getElementById('loginPassword').value = '';
}

function handleLogin(event) {
    event.preventDefault();
    const username = document.getElementById('loginUsername').value;
    const password = document.getElementById('loginPassword').value;
    
    const formData = new FormData();
    formData.append('username', username);
    formData.append('password', password);
    
    fetch('<?php echo $assetsPath; ?>user/login_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Login failed');
        }
    })
    .catch(error => {
        alert('Login error occurred');
    });
}

// Register Modal Functions
function openRegisterModal() {
    document.getElementById('registerModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeRegisterModal() {
    document.getElementById('registerModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    clearRegisterForm();
}

function clearRegisterForm() {
    document.getElementById('regFullName').value = '';
    document.getElementById('regUsername').value = '';
    document.getElementById('regEmail').value = '';
    document.getElementById('regPassword').value = '';
    document.getElementById('regConfirmPassword').value = '';
}

function handleRegister(event) {
    event.preventDefault();
    const password = document.getElementById('regPassword').value;
    const confirmPassword = document.getElementById('regConfirmPassword').value;
    
    if (password !== confirmPassword) {
        alert('Passwords do not match');
        return;
    }
    
    const formData = new FormData();
    formData.append('full_name', document.getElementById('regFullName').value);
    formData.append('username', document.getElementById('regUsername').value);
    formData.append('email', document.getElementById('regEmail').value);
    formData.append('password', password);
    formData.append('confirm_password', confirmPassword);
    formData.append('terms', '1');
    
    fetch('<?php echo $assetsPath; ?>user/register_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Registration successful! Please login.');
            closeRegisterModal();
            openLoginModal();
        } else {
            alert(data.message || 'Registration failed');
        }
    })
    .catch(error => {
        alert('Registration error occurred');
    });
}

// Switch between modals
function switchToRegister() {
    closeLoginModal();
    openRegisterModal();
}

function switchToLogin() {
    closeRegisterModal();
    openLoginModal();
}

// Forgot Password Modal Functions
function openForgotPassword() {
    closeLoginModal();
    document.getElementById('forgotPasswordModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeForgotPasswordModal() {
    document.getElementById('forgotPasswordModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    document.getElementById('forgotEmail').value = '';
}

function switchToLoginFromForgot() {
    closeForgotPasswordModal();
    openLoginModal();
}

function handleForgotPassword(event) {
    event.preventDefault();
    const email = document.getElementById('forgotEmail').value;
    
    const formData = new FormData();
    formData.append('email', email);
    
    const currentPath = window.location.pathname;
    let resetPath = 'user/forgot_password_handler.php';
    
    if (currentPath.includes('/user/') || currentPath.includes('/admin/')) {
        resetPath = '../user/forgot_password_handler.php';
    }
    
    fetch(resetPath, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Password reset instructions have been sent to your email.');
            closeForgotPasswordModal();
        } else {
            alert(data.message || 'Failed to send reset email');
        }
    })
    .catch(error => {
        alert('Error sending reset email');
    });
}

// Password toggle function
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = input.nextElementSibling.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
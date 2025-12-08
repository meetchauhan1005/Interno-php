<?php
session_start();
require_once '../includes/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $terms = isset($_POST['terms']);
    
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif (!$terms) {
        $error = 'Please accept the terms and conditions.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetch()) {
            $error = 'Username or email already exists.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, phone) VALUES (?, ?, ?, ?, ?)");
            
            if ($stmt->execute([$username, $email, $hashed_password, $full_name, $phone])) {
                $success = 'Registration successful! You can now login.';
                $username = $email = $full_name = $phone = '';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - INTERNO</title>
    <link rel="icon" type="image/svg+xml" href="../assets/images/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); min-height: 100vh;">

<div class="auth-container">
    <div class="auth-card register-card">
        <div class="auth-header">
            <a href="../index.php" class="auth-logo">
                <img src="../assets/images/logo.svg" alt="INTERNO" style="height: 40px;">
            </a>
            <h1>Create Account</h1>
            <p>Join INTERNO and start shopping today</p>
        </div>

        <div class="auth-form">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                    <div style="margin-top: 1rem;">
                        <a href="login.php" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Login Now
                        </a>
                    </div>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <input type="text" id="full_name" name="full_name" required 
                                   placeholder="Enter your full name"
                                   value="<?php echo htmlspecialchars($full_name ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <div class="input-group">
                            <i class="fas fa-at"></i>
                            <input type="text" id="username" name="username" required 
                                   placeholder="Choose a username"
                                   value="<?php echo htmlspecialchars($username ?? ''); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" required 
                               placeholder="Enter your email address"
                               value="<?php echo htmlspecialchars($email ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <div class="input-group">
                        <i class="fas fa-phone"></i>
                        <input type="tel" id="phone" name="phone" 
                               placeholder="Enter your phone number"
                               value="<?php echo htmlspecialchars($phone ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" required minlength="6"
                                   placeholder="Create a password">
                            <button type="button" onclick="togglePassword('password', 'toggleIcon1')" class="password-toggle">
                                <i class="fas fa-eye" id="toggleIcon1"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password *</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="confirm_password" name="confirm_password" required minlength="6"
                                   placeholder="Confirm your password">
                            <button type="button" onclick="togglePassword('confirm_password', 'toggleIcon2')" class="password-toggle">
                                <i class="fas fa-eye" id="toggleIcon2"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="password-strength">
                    <div class="strength-label">Password Strength:</div>
                    <div class="strength-bar">
                        <div id="strengthBar"></div>
                    </div>
                    <div id="strengthText" class="strength-text">Enter a password</div>
                </div>
                
                <div class="terms-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="terms" required>
                        <span class="checkmark"></span>
                        <span>I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></span>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-lg w-full">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>
            
            <div class="auth-divider">
                <span>Or sign up with</span>
            </div>
            
            <div class="social-buttons">
                <button class="btn btn-outline social-btn">
                    <i class="fab fa-google"></i>
                    Google
                </button>
                <button class="btn btn-outline social-btn">
                    <i class="fab fa-facebook"></i>
                    Facebook
                </button>
            </div>
        </div>
        
        <div class="auth-footer">
            <p>Already have an account? <a href="login.php">Sign In</a></p>
        </div>
    </div>
    
    <div class="hero-section">
        <div class="hero-content">
            <div class="hero-badge">
                <i class="fas fa-crown"></i>
                <span>Premium Experience</span>
            </div>
            <h3>Transform Your Space</h3>
            <p>Join thousands of satisfied customers who trust INTERNO for their furniture needs</p>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="feature-text">
                        <h5>Exclusive Deals</h5>
                        <span>Up to 40% off</span>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <div class="feature-text">
                        <h5>Free Delivery</h5>
                        <span>Orders ₹999+</span>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <div class="feature-text">
                        <h5>24/7 Support</h5>
                        <span>Always here</span>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="feature-text">
                        <h5>Secure Shopping</h5>
                        <span>100% Protected</span>
                    </div>
                </div>
            </div>
            
            <div class="stats-row">
                <div class="stat-item">
                    <div class="stat-number">50K+</div>
                    <div class="stat-label">Happy Customers</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">1000+</div>
                    <div class="stat-label">Products</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">4.9★</div>
                    <div class="stat-label">Rating</div>
                </div>
            </div>
        </div>
        
        <div class="floating-elements">
            <div class="float-element float-1">
                <i class="fas fa-couch"></i>
            </div>
            <div class="float-element float-2">
                <i class="fas fa-bed"></i>
            </div>
            <div class="float-element float-3">
                <i class="fas fa-chair"></i>
            </div>
        </div>
    </div>
</div>

<style>
.auth-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
    position: relative;
}

.auth-card {
    background: var(--light);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-xl);
    width: 100%;
    max-width: 420px;
    overflow: hidden;
    border: 1px solid var(--gray-200);
}

.register-card {
    max-width: 500px;
}

.auth-header {
    background: var(--gradient-primary);
    color: var(--light);
    padding: 2rem;
    text-align: center;
}

.auth-logo {
    display: inline-block;
    margin-bottom: 1rem;
}

.auth-logo img {
    filter: brightness(0) invert(1);
    height: 40px;
    width: auto;
}

.auth-header h1 {
    font-family: var(--font-display);
    font-size: 1.75rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.auth-header p {
    opacity: 0.9;
    font-size: 0.95rem;
}

.auth-form {
    padding: 2rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: var(--gray-700);
    font-size: 0.9rem;
}

.input-group {
    position: relative;
    display: flex;
    align-items: center;
}

.input-group i {
    position: absolute;
    left: 1rem;
    color: var(--gray-500);
    z-index: 2;
}

.input-group input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 3rem;
    border: 1px solid var(--gray-300);
    border-radius: var(--radius);
    font-size: 0.9rem;
    background: var(--light);
    transition: var(--transition);
    outline: none;
}

.input-group input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.password-toggle {
    position: absolute;
    right: 1rem;
    background: none;
    border: none;
    color: var(--gray-500);
    cursor: pointer;
    z-index: 2;
}

.password-strength {
    margin-bottom: 1.5rem;
}

.strength-label {
    font-size: 0.9rem;
    color: var(--gray-600);
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.strength-bar {
    height: 4px;
    background: var(--gray-200);
    border-radius: 2px;
    overflow: hidden;
}

#strengthBar {
    height: 100%;
    width: 0%;
    background: var(--danger);
    transition: all 0.3s ease;
}

.strength-text {
    font-size: 0.8rem;
    color: var(--gray-500);
    margin-top: 0.25rem;
}

.terms-group {
    margin-bottom: 1.5rem;
}

.checkbox-label {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    font-size: 0.9rem;
    cursor: pointer;
    line-height: 1.4;
}

.checkbox-label input {
    width: auto;
    margin: 0;
    margin-top: 0.125rem;
}

.terms-group a {
    color: var(--primary);
    text-decoration: none;
}

.terms-group a:hover {
    text-decoration: underline;
}

.auth-divider {
    text-align: center;
    margin: 1.5rem 0;
    position: relative;
}

.auth-divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: var(--gray-200);
}

.auth-divider span {
    background: var(--light);
    padding: 0 1rem;
    color: var(--gray-500);
    font-size: 0.9rem;
}

.social-buttons {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.social-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    font-size: 0.9rem;
}

.auth-footer {
    padding: 1.5rem 2rem;
    background: var(--gray-50);
    text-align: center;
    border-top: 1px solid var(--gray-200);
}

.auth-footer p {
    margin: 0;
    color: var(--gray-600);
    font-size: 0.9rem;
}

.auth-footer a {
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
}

.auth-footer a:hover {
    text-decoration: underline;
}

.hero-section {
    position: absolute;
    top: 2rem;
    right: 2rem;
    width: 380px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 24px;
    padding: 2rem;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.hero-content {
    position: relative;
    z-index: 2;
}

.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: linear-gradient(135deg, #fbbf24, #f59e0b);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 12px rgba(251, 191, 36, 0.3);
}

.hero-section h3 {
    font-family: var(--font-display);
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 0.75rem;
    line-height: 1.2;
}

.hero-section p {
    color: var(--gray-600);
    font-size: 0.95rem;
    line-height: 1.5;
    margin-bottom: 2rem;
}

.features-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 2rem;
}

.feature-card {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 16px;
    padding: 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    transition: all 0.3s ease;
}

.feature-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.feature-icon {
    width: 40px;
    height: 40px;
    background: var(--gradient-primary);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
    flex-shrink: 0;
}

.feature-text h5 {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--dark);
    margin: 0 0 0.25rem 0;
}

.feature-text span {
    font-size: 0.75rem;
    color: var(--gray-600);
}

.stats-row {
    display: flex;
    justify-content: space-between;
    padding: 1.5rem 0;
    border-top: 1px solid rgba(255, 255, 255, 0.3);
}

.stat-item {
    text-align: center;
}

.stat-number {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.75rem;
    color: var(--gray-600);
    font-weight: 500;
}

.floating-elements {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
    overflow: hidden;
}

.float-element {
    position: absolute;
    width: 60px;
    height: 60px;
    background: rgba(99, 102, 241, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary);
    font-size: 1.5rem;
    animation: float 6s ease-in-out infinite;
}

.float-1 {
    top: 10%;
    right: 10%;
    animation-delay: 0s;
}

.float-2 {
    bottom: 20%;
    left: 5%;
    animation-delay: 2s;
}

.float-3 {
    top: 60%;
    right: 5%;
    animation-delay: 4s;
}

@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(10deg); }
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .hero-section {
        position: static;
        width: 100%;
        max-width: 500px;
        margin-top: 2rem;
    }
    
    .features-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-row {
        flex-direction: column;
        gap: 1rem;
    }
    
    .social-buttons {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function togglePassword(inputId, iconId) {
    const passwordInput = document.getElementById(inputId);
    const toggleIcon = document.getElementById(iconId);
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.className = 'fas fa-eye-slash';
    } else {
        passwordInput.type = 'password';
        toggleIcon.className = 'fas fa-eye';
    }
}

document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    
    let strength = 0;
    let text = 'Weak';
    let color = 'var(--danger)';
    
    if (password.length >= 6) strength += 20;
    if (password.length >= 8) strength += 20;
    if (/[a-z]/.test(password)) strength += 20;
    if (/[A-Z]/.test(password)) strength += 20;
    if (/[0-9]/.test(password)) strength += 10;
    if (/[^A-Za-z0-9]/.test(password)) strength += 10;
    
    if (strength >= 80) {
        text = 'Very Strong';
        color = 'var(--success)';
    } else if (strength >= 60) {
        text = 'Strong';
        color = 'var(--info)';
    } else if (strength >= 40) {
        text = 'Medium';
        color = 'var(--warning)';
    } else if (strength >= 20) {
        text = 'Weak';
        color = 'var(--danger)';
    } else {
        text = 'Very Weak';
        color = 'var(--danger)';
    }
    
    strengthBar.style.width = strength + '%';
    strengthBar.style.background = color;
    strengthText.textContent = text;
    strengthText.style.color = color;
});

document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    
    if (confirmPassword && password !== confirmPassword) {
        this.style.borderColor = 'var(--danger)';
    } else {
        this.style.borderColor = 'var(--gray-300)';
    }
});
</script>

</body>
</html>
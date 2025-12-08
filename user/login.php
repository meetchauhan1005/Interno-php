<?php
session_start();
require_once '../includes/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $pdo->prepare("SELECT id, username, password, role, full_name FROM users WHERE (username = ? OR email = ?) AND is_active = 1");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            
            $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            $redirect = $_GET['redirect'] ?? ($user['role'] == 'admin' ? '../admin/dashboard.php' : '../index.php');
            header("Location: $redirect");
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - INTERNO</title>
    <link rel="icon" type="image/svg+xml" href="../assets/images/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); min-height: 100vh;">

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <a href="../index.php" class="auth-logo">
                <span style="font-size: 2rem; margin-right: 0.5rem;">🛋️</span>
                <span style="font-size: 1.5rem; font-weight: 700;">INTERNO</span>
            </a>
            <h1>Welcome Back</h1>
            <p>Sign in to your INTERNO account</p>
        </div>

        <div class="auth-form">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" required 
                               placeholder="Enter your username or email"
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" required 
                               placeholder="Enter your password">
                        <button type="button" onclick="togglePassword()" class="password-toggle">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember">
                        <span class="checkmark"></span>
                        Remember me
                    </label>
                    <a href="#" class="forgot-link">Forgot Password?</a>
                </div>
                
                <button type="submit" class="btn btn-primary btn-lg w-full">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>
            
            <div class="auth-divider">
                <span>Or continue with</span>
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
            <p>Don't have an account? <a href="register.php">Create Account</a></p>
        </div>
    </div>
    
    <div class="demo-credentials">
        <div class="demo-card">
            <h4><i class="fas fa-info-circle"></i> Demo Credentials</h4>
            <div class="demo-info">
                <div>
                    <strong>Admin Account:</strong><br>
                    Username: <code>admin</code><br>
                    Password: <code>password</code>
                </div>
                <div>
                    <strong>Features:</strong><br>
                    • Full admin access<br>
                    • Dashboard & management
                </div>
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
    padding-left: 3rem;
    padding-right: 3rem;
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

.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    cursor: pointer;
}

.checkbox-label input {
    width: auto;
    margin: 0;
}

.forgot-link {
    color: var(--primary);
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
}

.forgot-link:hover {
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

.demo-credentials {
    position: absolute;
    bottom: 2rem;
    right: 2rem;
    max-width: 300px;
}

.demo-card {
    background: var(--light);
    border-radius: var(--radius);
    padding: 1rem;
    box-shadow: var(--shadow);
    border: 1px solid var(--gray-200);
}

.demo-card h4 {
    font-size: 0.9rem;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--primary);
}

.demo-info {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    font-size: 0.8rem;
}

.demo-info code {
    background: var(--gray-100);
    padding: 0.125rem 0.25rem;
    border-radius: 3px;
    font-family: monospace;
    color: var(--primary);
}

@media (max-width: 768px) {
    .demo-credentials {
        position: static;
        margin-top: 2rem;
        max-width: 420px;
    }
    
    .demo-info {
        grid-template-columns: 1fr;
    }
    
    .social-buttons {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.className = 'fas fa-eye-slash';
    } else {
        passwordInput.type = 'password';
        toggleIcon.className = 'fas fa-eye';
    }
}
</script>

</body>
</html>
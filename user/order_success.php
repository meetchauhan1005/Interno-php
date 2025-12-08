<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['order_success'])) {
    header('Location: orders.php');
    exit();
}

$order_data = $_SESSION['order_success'];
unset($_SESSION['order_success']);

$page_title = 'Order Placed Successfully';
include '../includes/header.php';
?>

<div class="success-celebration">
    <div class="container">
        <div class="success-content">
            <!-- Animated Success Icon -->
            <div class="success-icon">
                <div class="checkmark-circle">
                    <div class="checkmark"></div>
                </div>
                <div class="confetti"></div>
            </div>

            <!-- Success Message -->
            <div class="success-message">
                <h1>🎉 Order Placed Successfully!</h1>
                <p class="order-number">Order #<?php echo $order_data['order_number']; ?></p>
                <p class="amount">Total: ₹<?php echo number_format($order_data['total_amount'], 2); ?></p>
            </div>

            <!-- Happy Content -->
            <div class="celebration-content">
                <div class="emoji-rain">
                    <span>🎊</span>
                    <span>🎉</span>
                    <span>✨</span>
                    <span>🛋️</span>
                    <span>💫</span>
                    <span>🎈</span>
                </div>
                
                <div class="success-features">
                    <div class="feature">
                        <div class="feature-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <span>Fast Delivery</span>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <span>Secure Payment</span>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <span>Quality Products</span>
                    </div>
                </div>

                <div class="thank-you-message">
                    <h2>Thank You for Choosing INTERNO!</h2>
                    <p>Your furniture journey begins now. We're preparing your order with love and care.</p>
                </div>
            </div>

            <!-- Redirect Message -->
            <div class="redirect-info">
                <div class="loading-spinner"></div>
                <p>Redirecting to your orders in <span id="countdown">3</span> seconds...</p>
            </div>
        </div>
    </div>
</div>

<style>
.success-celebration {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    position: relative;
    overflow: hidden;
}

.success-celebration::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at 50% 50%, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: pulse 3s ease-in-out infinite;
}

.container {
    max-width: 600px;
    width: 100%;
    position: relative;
    z-index: 1;
}

.success-content {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 30px;
    padding: 3rem 2rem;
    text-align: center;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
    animation: slideUp 0.8s ease-out;
}

.success-icon {
    position: relative;
    margin-bottom: 2rem;
}

.checkmark-circle {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: linear-gradient(135deg, #10b981, #059669);
    margin: 0 auto;
    position: relative;
    animation: scaleIn 0.6s ease-out 0.3s both;
    box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4);
}

.checkmark {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 60px;
    height: 60px;
}

.checkmark::before {
    content: '';
    position: absolute;
    width: 20px;
    height: 40px;
    border: 4px solid white;
    border-top: none;
    border-left: none;
    transform: rotate(45deg);
    top: 10px;
    left: 18px;
    animation: checkmarkDraw 0.5s ease-out 0.8s both;
}

.confetti {
    position: absolute;
    top: -50px;
    left: 50%;
    transform: translateX(-50%);
    width: 200px;
    height: 200px;
    pointer-events: none;
}

.confetti::before,
.confetti::after {
    content: '';
    position: absolute;
    width: 10px;
    height: 10px;
    background: #f59e0b;
    animation: confettiFall 2s ease-out infinite;
}

.confetti::before {
    left: 20%;
    animation-delay: 0.2s;
    background: #ef4444;
}

.confetti::after {
    right: 20%;
    animation-delay: 0.8s;
    background: #3b82f6;
}

.success-message h1 {
    font-size: 2.5rem;
    font-weight: 700;
    background: linear-gradient(135deg, #667eea, #764ba2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin: 0 0 1rem 0;
    animation: fadeIn 0.6s ease-out 1s both;
}

.order-number {
    font-size: 1.25rem;
    font-weight: 600;
    color: #374151;
    margin: 0 0 0.5rem 0;
    animation: fadeIn 0.6s ease-out 1.2s both;
}

.amount {
    font-size: 1.5rem;
    font-weight: 700;
    color: #059669;
    margin: 0 0 2rem 0;
    animation: fadeIn 0.6s ease-out 1.4s both;
}

.celebration-content {
    margin-bottom: 2rem;
}

.emoji-rain {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 2rem;
    font-size: 2rem;
}

.emoji-rain span {
    animation: bounce 2s ease-in-out infinite;
}

.emoji-rain span:nth-child(1) { animation-delay: 0.1s; }
.emoji-rain span:nth-child(2) { animation-delay: 0.2s; }
.emoji-rain span:nth-child(3) { animation-delay: 0.3s; }
.emoji-rain span:nth-child(4) { animation-delay: 0.4s; }
.emoji-rain span:nth-child(5) { animation-delay: 0.5s; }
.emoji-rain span:nth-child(6) { animation-delay: 0.6s; }

.success-features {
    display: flex;
    justify-content: center;
    gap: 2rem;
    margin-bottom: 2rem;
    animation: fadeIn 0.6s ease-out 1.6s both;
}

.feature {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.feature-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
    animation: rotate 3s linear infinite;
}

.feature span {
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
}

.thank-you-message {
    animation: fadeIn 0.6s ease-out 1.8s both;
}

.thank-you-message h2 {
    font-size: 1.5rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 0.5rem 0;
}

.thank-you-message p {
    color: #6b7280;
    font-size: 1rem;
    margin: 0;
}

.redirect-info {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    padding: 1rem;
    background: rgba(102, 126, 234, 0.1);
    border-radius: 12px;
    animation: fadeIn 0.6s ease-out 2s both;
}

.loading-spinner {
    width: 20px;
    height: 20px;
    border: 2px solid #e5e7eb;
    border-top: 2px solid #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

.redirect-info p {
    margin: 0;
    color: #374151;
    font-weight: 500;
}

#countdown {
    font-weight: 700;
    color: #667eea;
}

/* Animations */
@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes scaleIn {
    from {
        opacity: 0;
        transform: scale(0);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

@keyframes checkmarkDraw {
    from {
        opacity: 0;
        transform: rotate(45deg) scale(0);
    }
    to {
        opacity: 1;
        transform: rotate(45deg) scale(1);
    }
}

@keyframes confettiFall {
    0% {
        opacity: 1;
        transform: translateY(-20px) rotate(0deg);
    }
    100% {
        opacity: 0;
        transform: translateY(100px) rotate(360deg);
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-10px);
    }
    60% {
        transform: translateY(-5px);
    }
}

@keyframes rotate {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

@keyframes pulse {
    0%, 100% {
        opacity: 0.5;
    }
    50% {
        opacity: 0.8;
    }
}

/* Page transition */
.page-exit {
    animation: pageExit 0.8s ease-in-out forwards;
}

@keyframes pageExit {
    0% {
        opacity: 1;
        transform: scale(1);
    }
    50% {
        opacity: 0.8;
        transform: scale(1.05);
    }
    100% {
        opacity: 0;
        transform: scale(0.9);
    }
}

@media (max-width: 768px) {
    .success-features {
        flex-direction: column;
        gap: 1rem;
    }
    
    .success-message h1 {
        font-size: 2rem;
    }
    
    .emoji-rain {
        font-size: 1.5rem;
        gap: 0.5rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let countdown = 3;
    const countdownElement = document.getElementById('countdown');
    
    const timer = setInterval(() => {
        countdown--;
        countdownElement.textContent = countdown;
        
        if (countdown <= 0) {
            clearInterval(timer);
            
            // Add exit animation
            document.body.classList.add('page-exit');
            
            // Redirect after animation
            setTimeout(() => {
                window.location.href = 'orders.php';
            }, 800);
        }
    }, 1000);
    
    // Add click to skip
    document.addEventListener('click', function() {
        clearInterval(timer);
        document.body.classList.add('page-exit');
        setTimeout(() => {
            window.location.href = 'orders.php';
        }, 800);
    });
});
</script>

<?php include '../includes/footer.php'; ?>
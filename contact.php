<?php
require_once 'includes/config.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Insert contact message into database
        try {
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, subject, message, status, created_at) VALUES (?, ?, ?, ?, ?, 'new', NOW())");
            $stmt->execute([$name, $email, $phone, $subject, $message]);
            $success = 'Thank you for reaching out! We\'ll get back to you within 24 hours.';
            // Clear form data
            $name = $email = $phone = $subject = $message = '';
        } catch (Exception $e) {
            $error = 'Failed to send message. Please try again. Error: ' . $e->getMessage();
        }
    }
}

$page_title = "Contact Us";
include 'includes/header.php';
?>

<!-- Contact Hero -->
<section class="contact-hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <div class="hero-badge">
                    <i class="fas fa-headset"></i>
                    <span>Get in Touch</span>
                </div>
                <h1>We're Here to Help</h1>
                <p>Have questions about our furniture? Need assistance with your order? Our friendly team is ready to provide the support you need.</p>
                <div class="contact-stats">
                    <div class="stat">
                        <i class="fas fa-clock"></i>
                        <div>
                            <span class="number">< 24hrs</span>
                            <span class="label">Response Time</span>
                        </div>
                    </div>
                    <div class="stat">
                        <i class="fas fa-smile"></i>
                        <div>
                            <span class="number">99%</span>
                            <span class="label">Satisfaction Rate</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Contact Form & Info -->
<section class="section contact-section">
    <div class="container">
        <div class="contact-grid">
            <!-- Contact Form -->
            <div class="form-container">
                <div class="form-header">
                    <h2>Send us a Message</h2>
                    <p>Fill out the form below and we'll get back to you as soon as possible</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="contact-form" id="contactForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Full Name *</label>
                            <div class="input-group">
                                <i class="fas fa-user"></i>
                                <input type="text" id="name" name="name" required 
                                       placeholder="Enter your full name"
                                       value="<?php echo htmlspecialchars($name ?? ''); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <div class="input-group">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" required 
                                       placeholder="your@email.com"
                                       value="<?php echo htmlspecialchars($email ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <div class="input-group">
                                <i class="fas fa-phone"></i>
                                <input type="tel" id="phone" name="phone" 
                                       placeholder="+91 98765 43210"
                                       value="<?php echo htmlspecialchars($phone ?? ''); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="subject">Subject *</label>
                            <div class="input-group">
                                <i class="fas fa-tag"></i>
                                <select id="subject" name="subject" required>
                                    <option value="">Choose a topic</option>
                                    <option value="General Inquiry" <?php echo ($subject ?? '') == 'General Inquiry' ? 'selected' : ''; ?>>General Inquiry</option>
                                    <option value="Order Support" <?php echo ($subject ?? '') == 'Order Support' ? 'selected' : ''; ?>>Order Support</option>
                                    <option value="Product Question" <?php echo ($subject ?? '') == 'Product Question' ? 'selected' : ''; ?>>Product Question</option>
                                    <option value="Shipping & Delivery" <?php echo ($subject ?? '') == 'Shipping & Delivery' ? 'selected' : ''; ?>>Shipping & Delivery</option>
                                    <option value="Returns & Refunds" <?php echo ($subject ?? '') == 'Returns & Refunds' ? 'selected' : ''; ?>>Returns & Refunds</option>
                                    <option value="Technical Issue" <?php echo ($subject ?? '') == 'Technical Issue' ? 'selected' : ''; ?>>Technical Issue</option>
                                    <option value="Feedback" <?php echo ($subject ?? '') == 'Feedback' ? 'selected' : ''; ?>>Feedback</option>
                                    <option value="Partnership" <?php echo ($subject ?? '') == 'Partnership' ? 'selected' : ''; ?>>Partnership</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Message *</label>
                        <div class="input-group">
                            <i class="fas fa-comment"></i>
                            <textarea id="message" name="message" rows="6" required 
                                      placeholder="Please describe your inquiry in detail..."><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg submit-btn">
                        <i class="fas fa-paper-plane"></i>
                        <span>Send Message</span>
                    </button>
                </form>
            </div>
            
            <!-- Contact Information -->
            <div class="info-container">
                <div class="contact-info">
                    <h3>Contact Information</h3>
                    <p>Get in touch with us through any of these channels</p>
                    
                    <div class="info-items">
                        <div class="info-item">
                            <div class="info-icon address">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="info-content">
                                <h4>Visit Our Store</h4>
                                <p>123 Furniture Street<br>Mumbai, Maharashtra 400001<br>India</p>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-icon phone">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="info-content">
                                <h4>Call Us</h4>
                                <p>+91 98765 43210<br>Mon-Fri: 9AM-6PM IST<br>Sat: 10AM-4PM IST</p>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-icon email">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="info-content">
                                <h4>Email Us</h4>
                                <p>support@interno.com<br>sales@interno.com<br>info@interno.com</p>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-icon social">
                                <i class="fas fa-share-alt"></i>
                            </div>
                            <div class="info-content">
                                <h4>Follow Us</h4>
                                <div class="social-links">
                                    <a href="#"><i class="fab fa-facebook"></i></a>
                                    <a href="#"><i class="fab fa-twitter"></i></a>
                                    <a href="#"><i class="fab fa-instagram"></i></a>
                                    <a href="#"><i class="fab fa-linkedin"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                

            </div>
        </div>
    </div>
</section>



<style>
.contact-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 6rem 0;
    position: relative;
    overflow: hidden;
}

.contact-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="%23fff" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>') repeat;
    pointer-events: none;
}

.hero-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: center;
    position: relative;
    z-index: 1;
}

.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    padding: 0.5rem 1rem;
    border-radius: 50px;
    color: white;
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
}

.hero-text h1 {
    font-size: 3.5rem;
    font-weight: 700;
    color: white;
    line-height: 1.1;
    margin-bottom: 1.5rem;
    font-family: var(--font-display);
}

.hero-text p {
    font-size: 1.125rem;
    color: rgba(255, 255, 255, 0.9);
    line-height: 1.6;
    margin-bottom: 2rem;
}

.contact-stats {
    display: flex;
    gap: 2rem;
}

.contact-stats .stat {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.contact-stats .stat i {
    font-size: 2rem;
    color: rgba(255, 255, 255, 0.8);
}

.contact-stats .number {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: white;
    font-family: var(--font-secondary);
}

.contact-stats .label {
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.8);
    font-weight: 500;
}

.hero-image {
    position: relative;
}

.hero-image img {
    width: 100%;
    border-radius: var(--radius-lg);
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
}

.floating-badge {
    position: absolute;
    bottom: -20px;
    right: -20px;
    background: white;
    padding: 1.5rem;
    border-radius: var(--radius-lg);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    display: flex;
    align-items: center;
    gap: 1rem;
}

.floating-badge i {
    font-size: 2rem;
    color: var(--primary);
}

.floating-badge h4 {
    margin: 0 0 0.25rem 0;
    font-size: 1rem;
    font-weight: 600;
}

.floating-badge p {
    margin: 0;
    font-size: 0.875rem;
    color: var(--gray-600);
}

.contact-section {
    background: var(--gray-50);
}

.contact-grid {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 3rem;
    align-items: start;
}

.form-container {
    background: white;
    padding: 3rem;
    border-radius: var(--radius-lg);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    border: 1px solid var(--gray-200);
}

.form-header {
    margin-bottom: 2rem;
}

.form-header h2 {
    font-size: 2rem;
    font-weight: 700;
    color: var(--gray-900);
    margin-bottom: 0.5rem;
    font-family: var(--font-display);
}

.form-header p {
    color: var(--gray-600);
    margin: 0;
}

.contact-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    color: var(--gray-700);
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
    font-family: var(--font-secondary);
}

.input-group {
    position: relative;
    display: flex;
    align-items: center;
}

.input-group i {
    position: absolute;
    left: 1rem;
    color: var(--gray-400);
    z-index: 1;
}

.input-group input,
.input-group select,
.input-group textarea {
    width: 100%;
    padding: 1rem 1rem 1rem 3rem;
    border: 2px solid var(--gray-300);
    border-radius: var(--radius);
    font-size: 0.875rem;
    transition: all 0.3s ease;
    background: var(--gray-50);
    font-family: var(--font-primary);
}

.input-group input:focus,
.input-group select:focus,
.input-group textarea:focus {
    outline: none;
    border-color: var(--primary);
    background: white;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.submit-btn {
    margin-top: 1rem;
    padding: 1rem 2rem;
    font-size: 1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}

.submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(99, 102, 241, 0.3);
}

.info-container {
    display: flex;
    flex-direction: column;
}

.contact-info {
    background: white;
    padding: 2rem;
    border-radius: var(--radius-lg);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid var(--gray-200);
}

.contact-info h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--gray-900);
    margin-bottom: 0.5rem;
    font-family: var(--font-secondary);
}

.contact-info p {
    color: var(--gray-600);
    margin-bottom: 2rem;
}

.info-items {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.info-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.info-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.info-icon.address { background: linear-gradient(135deg, #667eea, #764ba2); }
.info-icon.phone { background: linear-gradient(135deg, #4facfe, #00f2fe); }
.info-icon.email { background: linear-gradient(135deg, #43e97b, #38f9d7); }
.info-icon.social { background: linear-gradient(135deg, #f093fb, #f5576c); }

.info-content h4 {
    font-size: 1rem;
    font-weight: 600;
    color: var(--gray-900);
    margin-bottom: 0.5rem;
    font-family: var(--font-secondary);
}

.info-content p {
    color: var(--gray-600);
    line-height: 1.6;
    margin: 0;
}

.social-links {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.social-links a {
    width: 35px;
    height: 35px;
    background: var(--gray-100);
    color: var(--gray-600);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.3s ease;
}

.social-links a:hover {
    background: var(--primary);
    color: white;
    transform: translateY(-2px);
}



@media (max-width: 1024px) {
    .hero-content,
    .contact-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .hero-content {
        text-align: center;
    }
}

@media (max-width: 768px) {
    .hero-text h1 {
        font-size: 2.5rem;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .form-container {
        padding: 2rem;
    }
    
    .floating-badge {
        position: static;
        margin-top: 1rem;
    }
    

}
</style>

<script>
// FAQ Toggle
document.querySelectorAll('.faq-question').forEach(question => {
    question.addEventListener('click', () => {
        const faqItem = question.parentElement;
        const isActive = faqItem.classList.contains('active');
        
        // Close all FAQ items
        document.querySelectorAll('.faq-item').forEach(item => {
            item.classList.remove('active');
        });
        
        // Open clicked item if it wasn't active
        if (!isActive) {
            faqItem.classList.add('active');
        }
    });
});

// Form submission animation
document.getElementById('contactForm').addEventListener('submit', function(e) {
    const submitBtn = document.querySelector('.submit-btn');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Sending...</span>';
    submitBtn.disabled = true;
});
</script>

<?php include 'includes/footer.php'; ?>
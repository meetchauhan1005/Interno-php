<?php
$page_title = "About Us";
include 'includes/header.php';
?>

<!-- Modern About Hero -->
<section class="about-hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <div class="hero-badge">
                    <i class="fas fa-couch"></i>
                    <span>About INTERNO</span>
                </div>
                <h1>Crafting Beautiful Spaces Since 2020</h1>
                <p>We're passionate about transforming houses into homes with premium furniture that combines style, comfort, and affordability. Every piece tells a story of craftsmanship and care.</p>
                <div class="hero-stats">
                    <div class="stat">
                        <span class="number">50K+</span>
                        <span class="label">Happy Customers</span>
                    </div>
                    <div class="stat">
                        <span class="number">100K+</span>
                        <span class="label">Furniture Delivered</span>
                    </div>
                    <div class="stat">
                        <span class="number">99%</span>
                        <span class="label">Satisfaction Rate</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Our Story Section -->
<section class="section story-section">
    <div class="container">
        <div class="story-grid">
            <div class="story-content">
                <div class="section-badge">
                    <i class="fas fa-book-open"></i>
                    <span>Our Journey</span>
                </div>
                <h2>From Vision to Reality</h2>
                <p>INTERNO began with a simple belief: everyone deserves beautiful, quality furniture without breaking the bank. What started as a small family business has grown into India's trusted furniture destination.</p>
                <div class="story-points">
                    <div class="point">
                        <i class="fas fa-lightbulb"></i>
                        <div>
                            <h4>Innovation First</h4>
                            <p>Constantly evolving our designs and technology</p>
                        </div>
                    </div>
                    <div class="point">
                        <i class="fas fa-heart"></i>
                        <div>
                            <h4>Customer Obsessed</h4>
                            <p>Every decision is made with our customers in mind</p>
                        </div>
                    </div>
                    <div class="point">
                        <i class="fas fa-leaf"></i>
                        <div>
                            <h4>Sustainable Future</h4>
                            <p>Committed to eco-friendly materials and practices</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Values Section -->
<section class="section values-section">
    <div class="container">
        <div class="section-header">
            <div class="section-badge">
                <i class="fas fa-gem"></i>
                <span>Our Values</span>
            </div>
            <h2 class="section-title">What Drives Us Forward</h2>
            <p class="section-subtitle">Our core values shape every interaction, every product, and every decision we make</p>
        </div>
        <div class="values-grid">
            <div class="value-card">
                <div class="value-icon quality">
                    <i class="fas fa-medal"></i>
                </div>
                <h3>Uncompromising Quality</h3>
                <p>We source only the finest materials and work with skilled craftsmen to ensure every piece meets our high standards.</p>
            </div>
            <div class="value-card">
                <div class="value-icon innovation">
                    <i class="fas fa-rocket"></i>
                </div>
                <h3>Continuous Innovation</h3>
                <p>From smart furniture solutions to sustainable materials, we're always pushing the boundaries of what's possible.</p>
            </div>
            <div class="value-card">
                <div class="value-icon service">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Exceptional Service</h3>
                <p>Our dedicated team is here to help you every step of the way, from selection to delivery and beyond.</p>
            </div>
            <div class="value-card">
                <div class="value-icon sustainability">
                    <i class="fas fa-globe"></i>
                </div>
                <h3>Environmental Care</h3>
                <p>We're committed to sustainable practices, using eco-friendly materials and responsible manufacturing processes.</p>
            </div>
        </div>
    </div>
</section>





<style>
.about-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 6rem 0;
    position: relative;
    overflow: hidden;
}

.about-hero::before {
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
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
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

.hero-stats {
    display: flex;
    gap: 2rem;
}

.stat {
    text-align: center;
}

.stat .number {
    display: block;
    font-size: 2rem;
    font-weight: 700;
    color: white;
    font-family: var(--font-secondary);
}

.stat .label {
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.8);
    font-weight: 500;
}



.story-section {
    background: var(--gray-50);
}

.story-grid {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.section-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: var(--primary);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
}

.story-content h2 {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--gray-900);
    margin-bottom: 1.5rem;
    font-family: var(--font-display);
}

.story-content > p {
    font-size: 1.125rem;
    color: var(--gray-600);
    line-height: 1.6;
    margin-bottom: 2rem;
}

.story-points {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.point {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.point i {
    width: 40px;
    height: 40px;
    background: var(--gradient-primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.point h4 {
    margin: 0 0 0.5rem 0;
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--gray-900);
}

.point p {
    margin: 0;
    color: var(--gray-600);
}

.image-stack {
    position: relative;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.image-stack img {
    width: 100%;
    height: 250px;
    object-fit: cover;
    border-radius: var(--radius-lg);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.image-stack img:first-child {
    transform: translateY(-20px);
}

.image-stack img:last-child {
    transform: translateY(20px);
}

.values-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 2rem;
    margin-top: 3rem;
}

.value-card {
    background: white;
    padding: 2.5rem 2rem;
    border-radius: var(--radius-lg);
    text-align: center;
    border: 1px solid var(--gray-200);
    transition: all 0.3s ease;
}

.value-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    border-color: var(--primary);
}

.value-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    font-size: 2rem;
    color: white;
}

.value-icon.quality { background: linear-gradient(135deg, #667eea, #764ba2); }
.value-icon.innovation { background: linear-gradient(135deg, #f093fb, #f5576c); }
.value-icon.service { background: linear-gradient(135deg, #4facfe, #00f2fe); }
.value-icon.sustainability { background: linear-gradient(135deg, #43e97b, #38f9d7); }

.value-card h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--gray-900);
    margin-bottom: 1rem;
    font-family: var(--font-secondary);
}

.value-card p {
    color: var(--gray-600);
    line-height: 1.6;
}





@media (max-width: 1024px) {
    .hero-content,
    .story-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
        text-align: center;
    }
    

}

@media (max-width: 768px) {
    .hero-text h1 {
        font-size: 2.5rem;
    }
    
    .hero-stats {
        justify-content: center;
    }
    
    .floating-card {
        position: static;
        margin-top: 1rem;
    }
    
    .image-stack {
        grid-template-columns: 1fr;
    }
    
    .image-stack img {
        transform: none !important;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
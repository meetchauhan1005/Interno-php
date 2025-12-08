// INTERNO E-commerce JavaScript Functions

// Cart functionality
function addToCart(productId, quantity = 1) {
    const userMenu = document.querySelector('.user-menu');
    if (!userMenu) {
        showAlert('Please login to add items to cart', 'warning');
        setTimeout(() => {
            window.location.href = getBasePath() + 'user/login.php';
        }, 1500);
        return;
    }
    
    fetch(getBasePath() + 'includes/cart_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=add&product_id=${productId}&quantity=${quantity}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(text => {
        try {
            const data = JSON.parse(text);
            if (data.success) {
                updateCartCount();
                showAlert('Product added to cart!', 'success');
            } else {
                showAlert(data.message || 'Error adding to cart', 'error');
            }
        } catch (e) {
            console.error('Response not JSON:', text);
            showAlert('Error adding to cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error adding to cart', 'error');
    });
}

function addToCartWithQuantity(productId) {
    const quantityInput = document.getElementById('quantity');
    const quantity = quantityInput ? parseInt(quantityInput.value) : 1;
    addToCart(productId, quantity);
}

function updateQuantity(cartId, quantity) {
    if (quantity < 1) {
        removeFromCart(cartId);
        return;
    }
    
    fetch('includes/cart_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=update&cart_id=${cartId}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showAlert(data.message || 'Error updating quantity', 'error');
        }
    });
}

function removeFromCart(cartId) {
    if (confirm('Remove this item from cart?')) {
        fetch('includes/cart_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=remove&cart_id=${cartId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                showAlert(data.message || 'Error removing item', 'error');
            }
        });
    }
}

function updateCartCount() {
    const basePath = getBasePath();
    fetch(basePath + 'includes/cart_handler.php?action=count')
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(text => {
        try {
            const data = JSON.parse(text);
            const cartCount = document.getElementById('cartCount');
            if (cartCount) {
                cartCount.textContent = data.count || 0;
            }
        } catch (e) {
            console.log('Cart count response not JSON:', text);
        }
    })
    .catch(error => console.log('Cart count update failed:', error));
}

// Wishlist functionality
function addToWishlist(productId) {
    const userMenu = document.querySelector('.user-menu');
    if (!userMenu) {
        showAlert('Please login to add items to wishlist', 'warning');
        setTimeout(() => {
            window.location.href = getBasePath() + 'user/login.php';
        }, 1500);
        return;
    }
    
    fetch(getBasePath() + 'includes/wishlist_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=add&product_id=${productId}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(text => {
        try {
            const data = JSON.parse(text);
            if (data.success) {
                showAlert('Added to wishlist!', 'success');
            } else {
                showAlert(data.message || 'Error adding to wishlist', 'error');
            }
        } catch (e) {
            console.error('Response not JSON:', text);
            showAlert('Error adding to wishlist', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error adding to wishlist', 'error');
    });
}

function removeFromWishlist(productId) {
    fetch('includes/wishlist_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=remove&product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Removed from wishlist', 'info');
            updateWishlistIcon(productId, false);
        } else {
            showAlert(data.message || 'Error removing from wishlist', 'error');
        }
    });
}

function updateWishlistIcon(productId, inWishlist) {
    const icon = document.querySelector(`[data-product-id="${productId}"] .wishlist-icon`);
    if (icon) {
        icon.className = inWishlist ? 'fas fa-heart wishlist-icon' : 'far fa-heart wishlist-icon';
        icon.style.color = inWishlist ? '#ef4444' : '#6b7280';
    }
}

// Search functionality
function searchProducts() {
    const searchInput = document.getElementById('search');
    const searchTerm = searchInput.value.trim();
    if (searchTerm) {
        window.location.href = getBasePath() + `products.php?search=${encodeURIComponent(searchTerm)}`;
    }
}

// Get base path based on current location
function getBasePath() {
    const currentPath = window.location.pathname;
    if (currentPath.includes('/user/') || currentPath.includes('/admin/')) {
        return '../';
    }
    return '';
}

// View product details
function viewProduct(productId) {
    window.location.href = getBasePath() + 'product_detail.php?id=' + productId;
}

// Navigate to category
function viewCategory(categoryId) {
    window.location.href = getBasePath() + 'products.php?category=' + categoryId;
}

// Quick shop functionality
function quickShop(productId) {
    addToCart(productId, 1);
}

// Newsletter subscription
function subscribeNewsletter() {
    const emailInput = document.getElementById('newsletter-email');
    const email = emailInput.value.trim();
    
    if (!email) {
        showAlert('Please enter your email address', 'error');
        return;
    }
    
    if (!isValidEmail(email)) {
        showAlert('Please enter a valid email address', 'error');
        return;
    }
    
    const basePath = getBasePath();
    fetch(basePath + 'includes/newsletter_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `email=${encodeURIComponent(email)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Successfully subscribed to newsletter!', 'success');
            emailInput.value = '';
        } else {
            showAlert(data.message || 'Error subscribing to newsletter', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error subscribing to newsletter', 'error');
    });
}

// Contact form submission
function submitContactForm() {
    const form = document.getElementById('contact-form');
    const formData = new FormData(form);
    
    const basePath = getBasePath();
    fetch(basePath + 'includes/contact_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Message sent successfully! We will get back to you soon.', 'success');
            form.reset();
        } else {
            showAlert(data.message || 'Error sending message', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error sending message', 'error');
    });
}

// Utility functions
function isLoggedIn() {
    return document.body.dataset.loggedIn === 'true';
}

function showLoginPrompt() {
    if (confirm('Please login to continue. Would you like to go to the login page?')) {
        window.location.href = getBasePath() + 'user/login.php?redirect=' + encodeURIComponent(window.location.pathname);
    }
}

function showAlert(message, type = 'info') {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.alert');
    existingAlerts.forEach(alert => alert.remove());
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    
    const icon = getAlertIcon(type);
    alertDiv.innerHTML = `
        <i class="${icon}"></i>
        <span>${message}</span>
    `;
    
    // Insert at top of main content
    const main = document.querySelector('main');
    if (main) {
        main.insertBefore(alertDiv, main.firstChild);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
        
        // Scroll to alert
        alertDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

function getAlertIcon(type) {
    const icons = {
        success: 'fas fa-check-circle',
        error: 'fas fa-exclamation-circle',
        warning: 'fas fa-exclamation-triangle',
        info: 'fas fa-info-circle'
    };
    return icons[type] || icons.info;
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Product filtering
function filterProducts(category = '', minPrice = '', maxPrice = '', sort = '') {
    const params = new URLSearchParams();
    if (category) params.append('category', category);
    if (minPrice) params.append('min_price', minPrice);
    if (maxPrice) params.append('max_price', maxPrice);
    if (sort) params.append('sort', sort);
    
    window.location.href = getBasePath() + 'products.php?' + params.toString();
}

// Quantity controls
function increaseQuantity(inputId) {
    const input = document.getElementById(inputId);
    const max = parseInt(input.getAttribute('max')) || 999;
    const current = parseInt(input.value) || 1;
    if (current < max) {
        input.value = current + 1;
    }
}

function decreaseQuantity(inputId) {
    const input = document.getElementById(inputId);
    const min = parseInt(input.getAttribute('min')) || 1;
    const current = parseInt(input.value) || 1;
    if (current > min) {
        input.value = current - 1;
    }
}

// Image gallery
function changeProductImage(imageSrc) {
    const mainImage = document.getElementById('main-product-image');
    if (mainImage) {
        mainImage.src = imageSrc;
    }
}

// Back to top functionality
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Set logged in status
    document.body.dataset.loggedIn = document.querySelector('.user-menu') ? 'true' : 'false';
    
    // Update cart count
    updateCartCount();
    
    // Back to top button
    const backToTopBtn = document.getElementById('backToTop');
    if (backToTopBtn) {
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopBtn.style.display = 'block';
            } else {
                backToTopBtn.style.display = 'none';
            }
        });
    }
    
    // Search functionality
    const searchInput = document.getElementById('search');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchProducts();
            }
        });
    }
    
    // Newsletter form
    const newsletterForm = document.getElementById('newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            subscribeNewsletter();
        });
    }
    
    // Contact form
    const contactForm = document.getElementById('contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitContactForm();
        });
    }
});
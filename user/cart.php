<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$page_title = 'Shopping Cart';

// Get cart items
$stmt = $pdo->prepare("
    SELECT c.*, p.name, p.price, p.image, p.stock_quantity 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ? 
    ORDER BY c.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll();

$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$shipping = $subtotal >= 999 ? 0 : 99;
$tax = $subtotal * 0.18;
$total = $subtotal + $shipping + $tax;

include '../includes/header.php';
?>

<div class="checkout-container">
    <div class="container">
        <div class="checkout-header">
            <h1><i class="fas fa-shopping-cart"></i> Shopping Cart</h1>
            <p><?php echo count($cart_items); ?> items in your cart</p>
        </div>

        <?php if (empty($cart_items)): ?>
            <div class="empty-state">
                <i class="fas fa-shopping-cart"></i>
                <h2>Your cart is empty</h2>
                <p>Add some products to get started</p>
                <a href="../products.php" class="btn-continue">
                    <i class="fas fa-shopping-bag"></i> Continue Shopping
                </a>
            </div>
        <?php else: ?>
            <div class="checkout-layout">
                <div class="items-section">
                    <?php foreach ($cart_items as $item): ?>
                    <div class="checkout-item" data-product-id="<?php echo $item['product_id']; ?>">
                        <div class="item-image">
                            <?php if (!empty($item['image'])): ?>
                                <img src="../assets/images/<?php echo htmlspecialchars($item['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <?php else: ?>
                                <div class="no-image">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="item-info">
                            <h3 class="item-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p class="item-price">₹<?php echo number_format($item['price'], 2); ?> each</p>
                            <p class="stock-status">
                                <?php if ($item['stock_quantity'] > 10): ?>
                                    <i class="fas fa-check"></i> In Stock
                                <?php elseif ($item['stock_quantity'] > 0): ?>
                                    <i class="fas fa-exclamation-triangle"></i> Only <?php echo $item['stock_quantity']; ?> left
                                <?php else: ?>
                                    <i class="fas fa-times"></i> Out of Stock
                                <?php endif; ?>
                            </p>
                        </div>
                        
                        <div class="quantity-section">
                            <button class="qty-btn minus" onclick="updateQuantity(<?php echo $item['product_id']; ?>, -1)">
                                <i class="fas fa-minus"></i>
                            </button>
                            <span class="qty-display" id="qty-<?php echo $item['product_id']; ?>"><?php echo $item['quantity']; ?></span>
                            <button class="qty-btn plus" onclick="updateQuantity(<?php echo $item['product_id']; ?>, 1)">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        
                        <div class="item-total">
                            <span class="total-amount" id="total-<?php echo $item['product_id']; ?>">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                            <button class="remove-btn" onclick="removeItem(<?php echo $item['product_id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="summary-section">
                    <div class="order-summary">
                        <h3>Order Summary</h3>
                        
                        <div class="summary-line">
                            <span>Subtotal:</span>
                            <span id="subtotal-amount">₹<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        
                        <div class="summary-line">
                            <span>Shipping:</span>
                            <span class="<?php echo $shipping == 0 ? 'free-shipping' : ''; ?>">
                                <?php echo $shipping > 0 ? '₹' . number_format($shipping, 2) : 'FREE'; ?>
                            </span>
                        </div>
                        
                        <div class="summary-line">
                            <span>Tax (18%):</span>
                            <span id="tax-amount">₹<?php echo number_format($tax, 2); ?></span>
                        </div>
                        
                        <div class="summary-divider"></div>
                        
                        <div class="summary-line total-line">
                            <span>Total:</span>
                            <span id="final-total">₹<?php echo number_format($total, 2); ?></span>
                        </div>
                        
                        <button class="checkout-button" onclick="proceedToCheckout()">
                            <i class="fas fa-lock"></i> Proceed to Checkout
                        </button>
                        
                        <button class="continue-shopping" onclick="continueShopping()">
                            <i class="fas fa-arrow-left"></i> Continue Shopping
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="bottom-actions">
                <button class="clear-cart" onclick="clearCart()">
                    <i class="fas fa-trash"></i> Clear All Items
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.checkout-container {
    background: #f8fafc;
    min-height: 100vh;
    padding: 2rem 0;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

.checkout-header {
    text-align: center;
    margin-bottom: 3rem;
}

.checkout-header h1 {
    font-size: 2.5rem;
    color: #1f2937;
    margin-bottom: 0.5rem;
    font-weight: 700;
}

.checkout-header p {
    color: #6b7280;
    font-size: 1.1rem;
}

.empty-state {
    background: white;
    border-radius: 20px;
    padding: 4rem 2rem;
    text-align: center;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.empty-state i {
    font-size: 4rem;
    color: #d1d5db;
    margin-bottom: 1.5rem;
}

.empty-state h2 {
    font-size: 1.8rem;
    color: #374151;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: #6b7280;
    margin-bottom: 2rem;
}

.btn-continue {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    padding: 1rem 2rem;
    border-radius: 15px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-continue:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(59, 130, 246, 0.4);
}

.checkout-layout {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 3rem;
    align-items: start;
}

.items-section {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.checkout-item {
    display: grid;
    grid-template-columns: 80px 1fr auto auto;
    gap: 1.5rem;
    align-items: center;
    padding: 1.5rem 0;
    border-bottom: 1px solid #f1f5f9;
}

.checkout-item:last-child {
    border-bottom: none;
}

.item-info h3 {
    font-size: 1.25rem;
    color: #1f2937;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.item-price {
    font-size: 1.1rem;
    color: #3b82f6;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.stock-status {
    font-size: 0.9rem;
    color: #10b981;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.quantity-section {
    display: flex;
    align-items: center;
    background: #f8fafc;
    border-radius: 15px;
    padding: 0.5rem;
    gap: 1rem;
}

.qty-btn {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    color: #6b7280;
}

.qty-btn:hover {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
    transform: scale(1.05);
}

.qty-display {
    font-weight: 700;
    color: #1f2937;
    font-size: 1.1rem;
    min-width: 30px;
    text-align: center;
}

.item-total {
    text-align: right;
}

.total-amount {
    font-size: 1.4rem;
    font-weight: 700;
    color: #1f2937;
    display: block;
    margin-bottom: 0.75rem;
}

.remove-btn {
    background: #fee2e2;
    border: 2px solid #fecaca;
    color: #dc2626;
    padding: 0.5rem;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s;
}

.remove-btn:hover {
    background: #dc2626;
    color: white;
    transform: scale(1.05);
}

.summary-section {
    position: sticky;
    top: 2rem;
}

.order-summary {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.order-summary h3 {
    font-size: 1.4rem;
    color: #1f2937;
    margin-bottom: 1.5rem;
    text-align: center;
    font-weight: 700;
}

.summary-line {
    display: flex;
    justify-content: space-between;
    margin-bottom: 1rem;
    color: #6b7280;
    font-size: 1rem;
}

.summary-line.total-line {
    font-size: 1.3rem;
    font-weight: 700;
    color: #1f2937;
    padding-top: 1rem;
}

.free-shipping {
    color: #10b981 !important;
    font-weight: 600;
}

.summary-divider {
    height: 2px;
    background: #f1f5f9;
    margin: 1.5rem 0;
}

.checkout-button {
    width: 100%;
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    border: none;
    padding: 1.2rem;
    border-radius: 15px;
    font-size: 1.1rem;
    font-weight: 700;
    cursor: pointer;
    margin: 1.5rem 0 1rem 0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.3s;
}

.checkout-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4);
}

.continue-shopping {
    width: 100%;
    background: #f8fafc;
    color: #6b7280;
    border: 2px solid #e5e7eb;
    padding: 1rem;
    border-radius: 15px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.2s;
}

.continue-shopping:hover {
    background: #f1f5f9;
    color: #374151;
    border-color: #d1d5db;
}

.bottom-actions {
    text-align: center;
    margin-top: 3rem;
}

.clear-cart {
    background: #fee2e2;
    color: #dc2626;
    border: 2px solid #fecaca;
    padding: 1rem 2rem;
    border-radius: 15px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.2s;
}

.clear-cart:hover {
    background: #dc2626;
    color: white;
    transform: translateY(-2px);
}

.item-image {
    width: 80px;
    height: 80px;
    border-radius: 12px;
    overflow: hidden;
    background: #f8fafc;
    display: flex;
    align-items: center;
    justify-content: center;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.no-image {
    color: #9ca3af;
    font-size: 1.5rem;
}

@media (max-width: 768px) {
    .checkout-layout {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .summary-section {
        position: static;
    }
    
    .checkout-item {
        grid-template-columns: 60px 1fr;
        text-align: left;
        gap: 1rem;
    }
    
    .item-image {
        width: 60px;
        height: 60px;
    }
    
    .quantity-section {
        grid-column: 1 / -1;
        justify-self: center;
        margin-top: 1rem;
    }
    
    .item-total {
        grid-column: 1 / -1;
        text-align: center;
        margin-top: 1rem;
    }
    
    .checkout-header h1 {
        font-size: 2rem;
    }
}
</style>

<script>
function updateQuantity(productId, change) {
    const qtyElement = document.getElementById(`qty-${productId}`);
    const totalElement = document.getElementById(`total-${productId}`);
    
    if (!qtyElement || !totalElement) {
        console.error('Elements not found for product:', productId);
        return;
    }
    
    let currentQty = parseInt(qtyElement.textContent);
    let newQty = Math.max(1, currentQty + change);
    
    console.log('Updating quantity for product', productId, 'from', currentQty, 'to', newQty);
    
    fetch('../includes/cart_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=update&product_id=${productId}&quantity=${newQty}`
    })
    .then(response => response.json())
    .then(data => {
        console.log('Server response:', data);
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error updating cart');
        }
    })
    .catch(error => {
        console.error('Error updating cart:', error);
        alert('Error updating cart');
    });
}

function removeItem(productId) {
    if (confirm('Remove this item from your cart?')) {
        fetch('../includes/cart_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=remove&product_id=${productId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error removing item');
            }
        })
        .catch(error => {
            console.error('Error removing item:', error);
            alert('Error removing item');
        });
    }
}

function clearCart() {
    if (confirm('Clear all items from your cart?')) {
        fetch('../includes/cart_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=clear'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error clearing cart');
            }
        })
        .catch(error => {
            console.error('Error clearing cart:', error);
            alert('Error clearing cart');
        });
    }
}

function proceedToCheckout() {
    window.location.href = 'checkout.php';
}

function continueShopping() {
    window.location.href = '../products.php';
}
</script>

<?php include '../includes/footer.php'; ?>
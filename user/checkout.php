<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$page_title = 'Checkout';
ob_start();

// Get cart items
$stmt = $pdo->prepare("SELECT c.*, p.name, p.price, p.image, p.stock_quantity FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll();

if (empty($cart_items)) {
    header('Location: cart.php');
    exit();
}

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = $subtotal >= 999 ? 0 : 99;
$tax = $subtotal * 0.18;
$total = $subtotal + $shipping + $tax;

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_number = 'ORD-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    // Calculate final total with delivery option
    $delivery_charge = $_POST['delivery_option'] === 'express' ? 150 : 0;
    $final_total = $total + $delivery_charge;
    
    // Insert order
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, order_number, total_amount, subtotal, shipping_amount, tax_amount, status, payment_status, payment_method, shipping_address, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', 'paid', ?, ?, NOW())");
    
    $shipping_address = json_encode([
        'name' => $_POST['full_name'],
        'phone' => $_POST['phone'],
        'address' => $_POST['address'],
        'city' => $_POST['city'],
        'state' => $_POST['state'],
        'pincode' => $_POST['pincode'],
        'delivery_option' => $_POST['delivery_option'],
        'delivery_charge' => $delivery_charge
    ]);
    
    $stmt->execute([$_SESSION['user_id'], $order_number, $final_total, $subtotal, $shipping, $tax, $_POST['payment_method'], $shipping_address]);
    $order_id = $pdo->lastInsertId();
    
    // Insert order items
    foreach ($cart_items as $item) {
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price, total) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$order_id, $item['product_id'], $item['name'], $item['quantity'], $item['price'], $item['price'] * $item['quantity']]);
    }
    
    // Clear cart
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    
    $_SESSION['order_success'] = [
        'order_number' => $order_number,
        'total_amount' => $final_total
    ];
    
    header('Location: order_success.php');
    exit();
}

include '../includes/header.php';
ob_end_flush();
?>

<div class="premium-checkout">
    <div class="container">
        <!-- Header -->
        <div class="checkout-header">
            <div class="header-content">
                <div class="brand-badge">
                    <i class="fas fa-crown"></i>
                    <span>Premium Checkout</span>
                </div>
                <h1>Complete Your Order</h1>
                <p>Secure payment • Fast delivery • Premium experience</p>
            </div>
        </div>

        <form method="POST" class="checkout-form" id="checkoutForm">
            <div class="checkout-grid">
                <!-- Left Column -->
                <div class="checkout-main">
                    <!-- Delivery Information -->
                    <div class="section-card">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="section-title">
                                <h2>Delivery Address</h2>
                                <p>Where should we deliver your order?</p>
                            </div>
                        </div>
                        <div class="section-content">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Full Name</label>
                                    <input type="text" name="full_name" value="<?php echo htmlspecialchars(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: $user['username']); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Phone Number</label>
                                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Complete Address</label>
                                <textarea name="address" rows="3" placeholder="House/Flat No., Street, Landmark"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>City</label>
                                    <input type="text" name="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label>State</label>
                                    <select name="state">
                                        <option value="">Select State</option>
                                        <option value="Andhra Pradesh" <?php echo ($user['state'] ?? '') == 'Andhra Pradesh' ? 'selected' : ''; ?>>Andhra Pradesh</option>
                                        <option value="Arunachal Pradesh" <?php echo $user['state'] == 'Arunachal Pradesh' ? 'selected' : ''; ?>>Arunachal Pradesh</option>
                                        <option value="Assam" <?php echo $user['state'] == 'Assam' ? 'selected' : ''; ?>>Assam</option>
                                        <option value="Bihar" <?php echo $user['state'] == 'Bihar' ? 'selected' : ''; ?>>Bihar</option>
                                        <option value="Chhattisgarh" <?php echo $user['state'] == 'Chhattisgarh' ? 'selected' : ''; ?>>Chhattisgarh</option>
                                        <option value="Goa" <?php echo $user['state'] == 'Goa' ? 'selected' : ''; ?>>Goa</option>
                                        <option value="Gujarat" <?php echo $user['state'] == 'Gujarat' ? 'selected' : ''; ?>>Gujarat</option>
                                        <option value="Haryana" <?php echo $user['state'] == 'Haryana' ? 'selected' : ''; ?>>Haryana</option>
                                        <option value="Himachal Pradesh" <?php echo $user['state'] == 'Himachal Pradesh' ? 'selected' : ''; ?>>Himachal Pradesh</option>
                                        <option value="Jharkhand" <?php echo $user['state'] == 'Jharkhand' ? 'selected' : ''; ?>>Jharkhand</option>
                                        <option value="Karnataka" <?php echo $user['state'] == 'Karnataka' ? 'selected' : ''; ?>>Karnataka</option>
                                        <option value="Kerala" <?php echo $user['state'] == 'Kerala' ? 'selected' : ''; ?>>Kerala</option>
                                        <option value="Madhya Pradesh" <?php echo $user['state'] == 'Madhya Pradesh' ? 'selected' : ''; ?>>Madhya Pradesh</option>
                                        <option value="Maharashtra" <?php echo $user['state'] == 'Maharashtra' ? 'selected' : ''; ?>>Maharashtra</option>
                                        <option value="Manipur" <?php echo $user['state'] == 'Manipur' ? 'selected' : ''; ?>>Manipur</option>
                                        <option value="Meghalaya" <?php echo $user['state'] == 'Meghalaya' ? 'selected' : ''; ?>>Meghalaya</option>
                                        <option value="Mizoram" <?php echo $user['state'] == 'Mizoram' ? 'selected' : ''; ?>>Mizoram</option>
                                        <option value="Nagaland" <?php echo $user['state'] == 'Nagaland' ? 'selected' : ''; ?>>Nagaland</option>
                                        <option value="Odisha" <?php echo $user['state'] == 'Odisha' ? 'selected' : ''; ?>>Odisha</option>
                                        <option value="Punjab" <?php echo $user['state'] == 'Punjab' ? 'selected' : ''; ?>>Punjab</option>
                                        <option value="Rajasthan" <?php echo $user['state'] == 'Rajasthan' ? 'selected' : ''; ?>>Rajasthan</option>
                                        <option value="Sikkim" <?php echo $user['state'] == 'Sikkim' ? 'selected' : ''; ?>>Sikkim</option>
                                        <option value="Tamil Nadu" <?php echo $user['state'] == 'Tamil Nadu' ? 'selected' : ''; ?>>Tamil Nadu</option>
                                        <option value="Telangana" <?php echo $user['state'] == 'Telangana' ? 'selected' : ''; ?>>Telangana</option>
                                        <option value="Tripura" <?php echo $user['state'] == 'Tripura' ? 'selected' : ''; ?>>Tripura</option>
                                        <option value="Uttar Pradesh" <?php echo $user['state'] == 'Uttar Pradesh' ? 'selected' : ''; ?>>Uttar Pradesh</option>
                                        <option value="Uttarakhand" <?php echo $user['state'] == 'Uttarakhand' ? 'selected' : ''; ?>>Uttarakhand</option>
                                        <option value="West Bengal" <?php echo $user['state'] == 'West Bengal' ? 'selected' : ''; ?>>West Bengal</option>
                                        <option value="Andaman and Nicobar Islands" <?php echo $user['state'] == 'Andaman and Nicobar Islands' ? 'selected' : ''; ?>>Andaman and Nicobar Islands</option>
                                        <option value="Chandigarh" <?php echo $user['state'] == 'Chandigarh' ? 'selected' : ''; ?>>Chandigarh</option>
                                        <option value="Dadra and Nagar Haveli and Daman and Diu" <?php echo $user['state'] == 'Dadra and Nagar Haveli and Daman and Diu' ? 'selected' : ''; ?>>Dadra and Nagar Haveli and Daman and Diu</option>
                                        <option value="Delhi" <?php echo $user['state'] == 'Delhi' ? 'selected' : ''; ?>>Delhi</option>
                                        <option value="Jammu and Kashmir" <?php echo $user['state'] == 'Jammu and Kashmir' ? 'selected' : ''; ?>>Jammu and Kashmir</option>
                                        <option value="Ladakh" <?php echo $user['state'] == 'Ladakh' ? 'selected' : ''; ?>>Ladakh</option>
                                        <option value="Lakshadweep" <?php echo $user['state'] == 'Lakshadweep' ? 'selected' : ''; ?>>Lakshadweep</option>
                                        <option value="Puducherry" <?php echo $user['state'] == 'Puducherry' ? 'selected' : ''; ?>>Puducherry</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>PIN Code</label>
                                    <input type="text" name="pincode" value="<?php echo htmlspecialchars($user['zip_code'] ?? ''); ?>" pattern="[0-9]{6}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Options -->
                    <div class="section-card">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="fas fa-shipping-fast"></i>
                            </div>
                            <div class="section-title">
                                <h2>Delivery Speed</h2>
                                <p>Choose your preferred delivery option</p>
                            </div>
                        </div>
                        <div class="section-content">
                            <div class="delivery-options">
                                <label class="delivery-option">
                                    <input type="radio" name="delivery_option" value="standard" checked>
                                    <div class="option-card">
                                        <div class="option-header">
                                            <div class="option-icon standard">
                                                <i class="fas fa-truck"></i>
                                            </div>
                                            <div class="option-info">
                                                <h3>Standard Delivery</h3>
                                                <p>5-6 Business Days</p>
                                            </div>
                                            <div class="option-price">
                                                <span class="price">FREE</span>
                                            </div>
                                        </div>
                                        <div class="option-features">
                                            <div class="feature">
                                                <i class="fas fa-check"></i>
                                                <span>Free delivery on all orders</span>
                                            </div>
                                            <div class="feature">
                                                <i class="fas fa-shield-alt"></i>
                                                <span>Secure packaging</span>
                                            </div>
                                        </div>
                                    </div>
                                </label>

                                <label class="delivery-option">
                                    <input type="radio" name="delivery_option" value="express">
                                    <div class="option-card premium">
                                        <div class="premium-badge">
                                            <i class="fas fa-bolt"></i>
                                            <span>Express</span>
                                        </div>
                                        <div class="option-header">
                                            <div class="option-icon express">
                                                <i class="fas fa-rocket"></i>
                                            </div>
                                            <div class="option-info">
                                                <h3>Express Delivery</h3>
                                                <p>2-3 Business Days</p>
                                            </div>
                                            <div class="option-price">
                                                <span class="price">₹150</span>
                                            </div>
                                        </div>
                                        <div class="option-features">
                                            <div class="feature">
                                                <i class="fas fa-bolt"></i>
                                                <span>Priority processing</span>
                                            </div>
                                            <div class="feature">
                                                <i class="fas fa-clock"></i>
                                                <span>Real-time tracking</span>
                                            </div>
                                            <div class="feature">
                                                <i class="fas fa-headset"></i>
                                                <span>Dedicated support</span>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="section-card">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <div class="section-title">
                                <h2>Payment Method</h2>
                                <p>Choose your preferred payment option</p>
                            </div>
                        </div>
                        <div class="section-content">
                            <div class="payment-methods">
                                <!-- UPI Payment -->
                                <div class="payment-method">
                                    <label class="payment-option">
                                        <input type="radio" name="payment_method" value="upi" checked>
                                        <div class="method-card">
                                            <div class="method-header">
                                                <div class="method-icon upi">
                                                    <i class="fas fa-mobile-alt"></i>
                                                </div>
                                                <div class="method-info">
                                                    <h3>UPI Payment</h3>
                                                    <p>Instant & Secure</p>
                                                </div>
                                                <div class="method-badge instant">Instant</div>
                                            </div>
                                        </div>
                                    </label>
                                    <div class="payment-details upi-details">
                                        <div class="upi-apps">
                                            <div class="upi-app">
                                                <div class="app-icon gpay">G</div>
                                                <span>Google Pay</span>
                                            </div>
                                            <div class="upi-app">
                                                <div class="app-icon phonepe">Pe</div>
                                                <span>PhonePe</span>
                                            </div>
                                            <div class="upi-app">
                                                <div class="app-icon paytm">P</div>
                                                <span>Paytm UPI</span>
                                            </div>
                                            <div class="upi-app">
                                                <div class="app-icon bhim">B</div>
                                                <span>BHIM UPI</span>
                                            </div>
                                        </div>
                                        <div class="upi-form">
                                            <label>UPI ID (Optional)</label>
                                            <input type="text" name="upi_id" placeholder="yourname@paytm">
                                        </div>
                                    </div>
                                </div>

                                <!-- Credit/Debit Cards -->
                                <div class="payment-method">
                                    <label class="payment-option">
                                        <input type="radio" name="payment_method" value="card">
                                        <div class="method-card">
                                            <div class="method-header">
                                                <div class="method-icon card">
                                                    <i class="fas fa-credit-card"></i>
                                                </div>
                                                <div class="method-info">
                                                    <h3>Credit/Debit Card</h3>
                                                    <p>Visa, Mastercard, RuPay</p>
                                                </div>
                                                <div class="card-icons">
                                                    <i class="fab fa-cc-visa"></i>
                                                    <i class="fab fa-cc-mastercard"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                    <div class="payment-details card-details" style="display: none;">
                                        <div class="card-form">
                                            <div class="form-group">
                                                <label>Card Number</label>
                                                <input type="text" name="card_number" placeholder="1234 5678 9012 3456" maxlength="19">
                                            </div>
                                            <div class="form-row">
                                                <div class="form-group">
                                                    <label>Expiry Date</label>
                                                    <input type="text" name="expiry" placeholder="MM/YY" maxlength="5">
                                                </div>
                                                <div class="form-group">
                                                    <label>CVV</label>
                                                    <input type="text" name="cvv" placeholder="123" maxlength="4">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label>Cardholder Name</label>
                                                <input type="text" name="card_name" placeholder="Name on card">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Net Banking -->
                                <div class="payment-method">
                                    <label class="payment-option">
                                        <input type="radio" name="payment_method" value="netbanking">
                                        <div class="method-card">
                                            <div class="method-header">
                                                <div class="method-icon netbanking">
                                                    <i class="fas fa-university"></i>
                                                </div>
                                                <div class="method-info">
                                                    <h3>Net Banking</h3>
                                                    <p>All major banks</p>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                    <div class="payment-details netbanking-details" style="display: none;">
                                        <div class="bank-selection">
                                            <label>Select your bank:</label>
                                            <select name="bank">
                                                <option value="">Choose Bank</option>
                                                <option value="sbi">State Bank of India</option>
                                                <option value="hdfc">HDFC Bank</option>
                                                <option value="icici">ICICI Bank</option>
                                                <option value="axis">Axis Bank</option>
                                                <option value="kotak">Kotak Mahindra</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Cash on Delivery -->
                                <div class="payment-method">
                                    <label class="payment-option">
                                        <input type="radio" name="payment_method" value="cod">
                                        <div class="method-card">
                                            <div class="method-header">
                                                <div class="method-icon cod">
                                                    <i class="fas fa-money-bill-wave"></i>
                                                </div>
                                                <div class="method-info">
                                                    <h3>Cash on Delivery</h3>
                                                    <p>Pay when you receive</p>
                                                </div>
                                                <div class="method-badge cod-badge">+₹40</div>
                                            </div>
                                        </div>
                                    </label>
                                    <div class="payment-details cod-details">
                                        <div class="cod-info">
                                            <div class="info-item">
                                                <i class="fas fa-truck"></i>
                                                <span>Pay at your doorstep</span>
                                            </div>
                                            <div class="info-item">
                                                <i class="fas fa-money-bill"></i>
                                                <span>Cash or card payment</span>
                                            </div>
                                            <div class="info-item warning">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                <span>Additional ₹40 handling charges</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Order Summary -->
                <div class="checkout-sidebar">
                    <div class="summary-card">
                        <div class="summary-header">
                            <h3>Order Summary</h3>
                            <span class="item-count"><?php echo count($cart_items); ?> items</span>
                        </div>

                        <div class="order-items">
                            <?php foreach ($cart_items as $item): ?>
                            <div class="summary-item">
                                <div class="item-image">
                                    <img src="../assets/images/<?php echo $item['image'] ?: 'placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    <span class="item-qty"><?php echo $item['quantity']; ?></span>
                                </div>
                                <div class="item-details">
                                    <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                    <p>₹<?php echo number_format($item['price'], 2); ?> × <?php echo $item['quantity']; ?></p>
                                </div>
                                <div class="item-total">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="summary-calculations">
                            <div class="calc-row">
                                <span>Subtotal</span>
                                <span>₹<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <div class="calc-row">
                                <span>Shipping</span>
                                <span class="<?php echo $shipping == 0 ? 'free' : ''; ?>">
                                    <?php echo $shipping > 0 ? '₹' . number_format($shipping, 2) : 'FREE'; ?>
                                </span>
                            </div>
                            <div class="calc-row">
                                <span>Tax (GST 18%)</span>
                                <span>₹<?php echo number_format($tax, 2); ?></span>
                            </div>
                            <div class="calc-row delivery-charge" style="display: none;">
                                <span>Express Delivery</span>
                                <span>₹150.00</span>
                            </div>
                            <div class="calc-row cod-charges" style="display: none;">
                                <span>COD Charges</span>
                                <span>₹40.00</span>
                            </div>
                            <div class="calc-divider"></div>
                            <div class="calc-row total">
                                <span>Total Amount</span>
                                <span id="finalTotal">₹<?php echo number_format($total, 2); ?></span>
                            </div>
                        </div>

                        <button type="submit" class="place-order-btn">
                            <i class="fas fa-lock"></i>
                            <span>Place Order Securely</span>
                        </button>

                        <div class="security-badges">
                            <div class="badge">
                                <i class="fas fa-shield-alt"></i>
                                <span>SSL Secured</span>
                            </div>
                            <div class="badge">
                                <i class="fas fa-undo"></i>
                                <span>Easy Returns</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
.premium-checkout {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

.brand-badge {
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
    margin-bottom: 1rem;
}

.header-content h1 {
    color: white;
    font-size: 3rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.header-content p {
    color: rgba(255, 255, 255, 0.9);
    font-size: 1.125rem;
    margin: 0;
}

.checkout-grid {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 2rem;
    align-items: start;
}

.checkout-main {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.section-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.section-header {
    padding: 2rem;
    background: linear-gradient(135deg, #f8fafc, #e2e8f0);
    display: flex;
    align-items: center;
    gap: 1rem;
}

.section-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.section-title h2 {
    margin: 0 0 0.25rem 0;
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
}

.section-title p {
    margin: 0;
    color: #6b7280;
    font-size: 0.875rem;
}

.section-content {
    padding: 2rem;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

.form-grid:has(.form-group:nth-child(3)) {
    grid-template-columns: 1fr 1fr 1fr;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    font-size: 0.875rem;
    transition: all 0.3s;
    background: white;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.delivery-options {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.delivery-option input[type="radio"] {
    display: none;
}

.option-card {
    border: 2px solid #e5e7eb;
    border-radius: 16px;
    padding: 1.5rem;
    transition: all 0.3s;
    cursor: pointer;
    background: white;
    position: relative;
}

.option-card.premium {
    background: linear-gradient(135deg, #f8fafc, #eff6ff);
}

.premium-badge {
    position: absolute;
    top: -8px;
    right: 1rem;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.delivery-option input:checked + .option-card {
    border-color: #667eea;
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.15);
    transform: translateY(-2px);
}

.option-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.option-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
}

.option-icon.standard {
    background: linear-gradient(135deg, #10b981, #059669);
}

.option-icon.express {
    background: linear-gradient(135deg, #f59e0b, #d97706);
}

.option-info {
    flex: 1;
}

.option-info h3 {
    margin: 0 0 0.25rem 0;
    font-size: 1.125rem;
    font-weight: 600;
    color: #1f2937;
}

.option-info p {
    margin: 0;
    color: #6b7280;
    font-size: 0.875rem;
}

.option-price .price {
    font-size: 1.25rem;
    font-weight: 700;
    color: #059669;
}

.option-features {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.feature {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #6b7280;
}

.feature i {
    color: #10b981;
    width: 16px;
}

.payment-methods {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.payment-method input[type="radio"] {
    display: none;
}

.method-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s;
    background: white;
}

.payment-method input:checked + .method-card {
    border-color: #667eea;
    background: linear-gradient(135deg, #f8fafc, #eff6ff);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.1);
}

.method-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
}

.method-icon.upi {
    background: linear-gradient(135deg, #8b5cf6, #7c3aed);
}

.method-icon.card {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
}

.method-icon.cod {
    background: linear-gradient(135deg, #f59e0b, #d97706);
}

.method-icon.netbanking {
    background: linear-gradient(135deg, #6366f1, #4f46e5);
}

.method-info {
    flex: 1;
}

.method-info h3 {
    margin: 0 0 0.25rem 0;
    font-size: 1rem;
    font-weight: 600;
    color: #1f2937;
}

.method-info p {
    margin: 0;
    color: #6b7280;
    font-size: 0.875rem;
}

.method-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.method-badge.instant {
    background: #d1fae5;
    color: #065f46;
}

.method-badge.cod-badge {
    background: #fef3c7;
    color: #92400e;
}

.payment-details {
    padding: 1.5rem;
    background: #f8fafc;
    border-top: 1px solid #e5e7eb;
    display: none;
}

.payment-method input[type="radio"]:checked ~ .payment-details {
    display: block;
}

.upi-apps {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.upi-app {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    transition: all 0.2s;
}

.upi-app:hover {
    border-color: #667eea;
    background: white;
}

.app-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: white;
    font-size: 0.875rem;
}

.app-icon.gpay {
    background: linear-gradient(135deg, #4285f4, #34a853);
}

.app-icon.phonepe {
    background: linear-gradient(135deg, #5f259f, #3c1361);
}

.app-icon.paytm {
    background: linear-gradient(135deg, #00baf2, #0082c6);
}

.app-icon.bhim {
    background: linear-gradient(135deg, #ff6b35, #f7931e);
}

.upi-app span {
    font-size: 0.75rem;
    color: #374151;
    text-align: center;
}

.upi-form {
    margin-top: 1rem;
}

.upi-form label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
}

.upi-form input {
    width: 100%;
    padding: 0.875rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.875rem;
}

.card-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.card-form .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.card-form .form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.card-form label {
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
}

.card-form input {
    padding: 0.875rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.875rem;
}

.bank-selection label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
}

.bank-selection select {
    width: 100%;
    padding: 0.875rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.875rem;
    background: white;
}

.cod-info {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.875rem;
    color: #374151;
}

.info-item i {
    color: #10b981;
    width: 16px;
}

.info-item.warning i {
    color: #f59e0b;
}

.method-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
}

.card-icons {
    display: flex;
    gap: 0.5rem;
    font-size: 1.5rem;
    color: #6b7280;
}

.checkout-sidebar {
    position: sticky;
    top: 2rem;
}

.summary-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.summary-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.summary-header h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 700;
    color: #1f2937;
}

.item-count {
    background: #667eea;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.order-items {
    margin-bottom: 1.5rem;
}

.summary-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid #f3f4f6;
}

.summary-item:last-child {
    border-bottom: none;
}

.item-image {
    position: relative;
    flex-shrink: 0;
}

.item-image img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
}

.item-qty {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #667eea;
    color: white;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 600;
}

.item-details {
    flex: 1;
}

.item-details h4 {
    margin: 0 0 0.25rem 0;
    font-size: 0.875rem;
    font-weight: 600;
    color: #1f2937;
}

.item-details p {
    margin: 0;
    font-size: 0.75rem;
    color: #6b7280;
}

.item-total {
    font-weight: 600;
    color: #1f2937;
    font-size: 0.875rem;
}

.summary-calculations {
    border-top: 1px solid #e5e7eb;
    padding-top: 1rem;
    margin-bottom: 1.5rem;
}

.calc-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.75rem;
    font-size: 0.875rem;
    color: #4b5563;
}

.calc-row.total {
    font-size: 1.125rem;
    font-weight: 700;
    color: #1f2937;
    padding-top: 0.75rem;
    border-top: 1px solid #e5e7eb;
}

.calc-divider {
    height: 1px;
    background: #e5e7eb;
    margin: 1rem 0;
}

.free {
    color: #059669 !important;
    font-weight: 600;
}

.place-order-btn {
    width: 100%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    padding: 1rem 2rem;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.place-order-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
}

.security-badges {
    display: flex;
    justify-content: center;
    gap: 1rem;
}

.security-badges .badge {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    color: #6b7280;
}

.security-badges .badge i {
    color: #10b981;
}

@media (max-width: 1024px) {
    .checkout-grid {
        grid-template-columns: 1fr;
    }
    
    .checkout-sidebar {
        position: static;
    }
}

@media (max-width: 768px) {
    .premium-checkout {
        padding: 1rem 0;
    }
    
    .container {
        padding: 0 0.75rem;
    }
    
    .checkout-header {
        margin-bottom: 2rem;
    }
    
    .header-content h1 {
        font-size: 2rem;
    }
    
    .header-content p {
        font-size: 0.95rem;
    }
    
    .checkout-grid {
        gap: 1.5rem;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .form-grid:has(.form-group:nth-child(3)) {
        grid-template-columns: 1fr;
    }
    
    .section-card {
        border-radius: 16px;
    }
    
    .section-header {
        padding: 1.5rem;
        flex-direction: column;
        text-align: center;
        gap: 0.75rem;
    }
    
    .section-icon {
        width: 50px;
        height: 50px;
        font-size: 1.25rem;
    }
    
    .section-title h2 {
        font-size: 1.25rem;
    }
    
    .section-content {
        padding: 1.5rem;
    }
    
    .form-group input,
    .form-group textarea,
    .form-group select {
        padding: 0.875rem;
        font-size: 16px;
    }
    
    .option-card {
        padding: 1.25rem;
    }
    
    .option-header {
        flex-wrap: wrap;
    }
    
    .option-icon {
        width: 45px;
        height: 45px;
        font-size: 1.1rem;
    }
    
    .option-info h3 {
        font-size: 1rem;
    }
    
    .option-price .price {
        font-size: 1.1rem;
    }
    
    .upi-apps {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .method-card {
        padding: 1.25rem;
    }
    
    .method-icon {
        width: 45px;
        height: 45px;
        font-size: 1.1rem;
    }
    
    .method-info h3 {
        font-size: 0.95rem;
    }
    
    .card-form .form-row {
        grid-template-columns: 1fr;
    }
    
    .summary-card {
        padding: 1.5rem;
    }
    
    .summary-header h3 {
        font-size: 1.1rem;
    }
    
    .place-order-btn {
        padding: 0.875rem 1.5rem;
        font-size: 0.95rem;
    }
}

@media (max-width: 480px) {
    .header-content h1 {
        font-size: 1.75rem;
    }
    
    .header-content p {
        font-size: 0.875rem;
    }
    
    .brand-badge {
        font-size: 0.8rem;
        padding: 0.4rem 0.875rem;
    }
    
    .section-header {
        padding: 1.25rem;
    }
    
    .section-icon {
        width: 45px;
        height: 45px;
        font-size: 1.15rem;
    }
    
    .section-title h2 {
        font-size: 1.15rem;
    }
    
    .section-title p {
        font-size: 0.8rem;
    }
    
    .section-content {
        padding: 1.25rem;
    }
    
    .option-card {
        padding: 1rem;
    }
    
    .option-features {
        gap: 0.4rem;
    }
    
    .feature {
        font-size: 0.8rem;
    }
    
    .upi-apps {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }
    
    .upi-app {
        padding: 0.75rem;
    }
    
    .app-icon {
        width: 35px;
        height: 35px;
        font-size: 0.8rem;
    }
    
    .upi-app span {
        font-size: 0.7rem;
    }
    
    .method-card {
        padding: 1rem;
    }
    
    .payment-details {
        padding: 1.25rem;
    }
    
    .summary-card {
        padding: 1.25rem;
    }
    
    .item-image img {
        width: 50px;
        height: 50px;
    }
    
    .item-details h4 {
        font-size: 0.8rem;
    }
    
    .item-details p {
        font-size: 0.7rem;
    }
    
    .item-total {
        font-size: 0.8rem;
    }
    
    .calc-row {
        font-size: 0.8rem;
    }
    
    .calc-row.total {
        font-size: 1rem;
    }
    
    .place-order-btn {
        padding: 0.8rem 1.25rem;
        font-size: 0.9rem;
    }
    
    .security-badges {
        flex-direction: column;
        gap: 0.5rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deliveryOptions = document.querySelectorAll('input[name="delivery_option"]');
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    const deliveryChargeRow = document.querySelector('.delivery-charge');
    const codChargesRow = document.querySelector('.cod-charges');
    const finalTotal = document.getElementById('finalTotal');
    const baseTotal = <?php echo $total; ?>;
    
    function updateTotal() {
        let total = baseTotal;
        
        // Add delivery charge
        const selectedDelivery = document.querySelector('input[name="delivery_option"]:checked');
        if (selectedDelivery && selectedDelivery.value === 'express') {
            total += 150;
            deliveryChargeRow.style.display = 'flex';
        } else {
            deliveryChargeRow.style.display = 'none';
        }
        
        // Add COD charge
        const selectedPayment = document.querySelector('input[name="payment_method"]:checked');
        if (selectedPayment && selectedPayment.value === 'cod') {
            total += 40;
            codChargesRow.style.display = 'flex';
        } else {
            codChargesRow.style.display = 'none';
        }
        
        finalTotal.textContent = '₹' + total.toLocaleString('en-IN', {minimumFractionDigits: 2});
    }
    
    deliveryOptions.forEach(option => {
        option.addEventListener('change', updateTotal);
    });
    
    paymentMethods.forEach(method => {
        method.addEventListener('change', function() {
            updateTotal();
            
            // Hide all payment details
            document.querySelectorAll('.payment-details').forEach(detail => {
                detail.style.display = 'none';
            });
            
            // Show selected payment details
            const selectedMethod = this.closest('.payment-method');
            const paymentDetails = selectedMethod.querySelector('.payment-details');
            if (paymentDetails) {
                paymentDetails.style.display = 'block';
            }
        });
    });
    
    // Card number formatting
    const cardNumberInput = document.querySelector('input[name="card_number"]');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function() {
            let value = this.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            this.value = formattedValue;
        });
    }
    
    // Expiry date formatting
    const expiryInput = document.querySelector('input[name="expiry"]');
    if (expiryInput) {
        expiryInput.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            this.value = value;
        });
    }
    
    // Show UPI details by default
    const defaultUpiDetails = document.querySelector('.upi-details');
    if (defaultUpiDetails) {
        defaultUpiDetails.style.display = 'block';
    }
    
    // Debug button click
    const placeOrderBtn = document.querySelector('.place-order-btn');
    if (placeOrderBtn) {
        placeOrderBtn.addEventListener('click', function(e) {
            console.log('Place order button clicked');
        });
    }
    
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        console.log('Form submission started');
        const btn = document.querySelector('.place-order-btn');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Processing...</span>';
        btn.disabled = true;
        
        // Validate payment method specific fields
        const selectedPayment = document.querySelector('input[name="payment_method"]:checked');
        if (!selectedPayment) {
            e.preventDefault();
            alert('Please select a payment method');
            btn.innerHTML = '<i class="fas fa-lock"></i> <span>Place Order Securely</span>';
            btn.disabled = false;
            return;
        }
        
        const paymentValue = selectedPayment.value;
        
        if (paymentValue === 'card') {
            const cardNumber = document.querySelector('input[name="card_number"]');
            const expiry = document.querySelector('input[name="expiry"]');
            const cvv = document.querySelector('input[name="cvv"]');
            
            if (!cardNumber || !cardNumber.value.trim() || !expiry || !expiry.value.trim() || !cvv || !cvv.value.trim()) {
                e.preventDefault();
                alert('Please fill in all card details');
                btn.innerHTML = '<i class="fas fa-lock"></i> <span>Place Order Securely</span>';
                btn.disabled = false;
                return;
            }
        }
        
        if (paymentValue === 'netbanking') {
            const bankSelect = document.querySelector('select[name="bank"]');
            if (!bankSelect || !bankSelect.value) {
                e.preventDefault();
                alert('Please select a bank');
                btn.innerHTML = '<i class="fas fa-lock"></i> <span>Place Order Securely</span>';
                btn.disabled = false;
                return;
            }
        }
        
        // Check required address fields
        const requiredFields = ['full_name', 'phone', 'address', 'city', 'state', 'pincode'];
        for (let field of requiredFields) {
            const input = document.querySelector(`[name="${field}"]`);
            if (!input || !input.value.trim()) {
                e.preventDefault();
                alert(`Please fill in ${field.replace('_', ' ')}`);
                btn.innerHTML = '<i class="fas fa-lock"></i> <span>Place Order Securely</span>';
                btn.disabled = false;
                return;
            }
        }
        
        setTimeout(() => {
            document.body.style.opacity = '0';
            document.body.style.transform = 'scale(0.95)';
            document.body.style.transition = 'all 0.5s ease';
        }, 1000);
    });
});
</script>

<?php include '../includes/footer.php'; ?>
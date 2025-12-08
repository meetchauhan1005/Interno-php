<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$page_title = 'My Orders';

try {
    $stmt = $pdo->prepare("
        SELECT o.*, 
               COUNT(oi.id) as item_count,
               GROUP_CONCAT(oi.product_name SEPARATOR '|') as product_names,
               GROUP_CONCAT(oi.quantity SEPARATOR '|') as quantities,
               GROUP_CONCAT(oi.price SEPARATOR '|') as prices,
               GROUP_CONCAT(p.image SEPARATOR '|') as product_images
        FROM orders o 
        LEFT JOIN order_items oi ON o.id = oi.order_id 
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE o.user_id = ? 
        GROUP BY o.id 
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $orders = [];
}

include '../includes/header.php';
?>

<div class="orders-dashboard">
    <div class="container">

        
        <div class="page-header">
            <div class="header-content">
                <h1><i class="fas fa-shopping-bag"></i> My Orders</h1>
                <p>Track and manage your orders</p>
            </div>
            <div class="filter-section">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search orders..." id="searchOrders">
                </div>
                <div class="filter-chips">
                    <button class="chip active" data-filter="all">All Orders</button>
                    <button class="chip" data-filter="pending">Pending</button>
                    <button class="chip" data-filter="shipped">Shipped</button>
                    <button class="chip" data-filter="delivered">Delivered</button>
                </div>
            </div>
        </div>

        <?php if (empty($orders)): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h2>No Orders Yet</h2>
                <p>Start shopping to see your orders here</p>
                <a href="../products.php" class="btn-shop-now">
                    <i class="fas fa-shopping-bag"></i>
                    Start Shopping
                </a>
            </div>
        <?php else: ?>
            <div class="orders-list">
                <?php foreach ($orders as $order): 
                    $productNames = explode('|', $order['product_names']);
                    $quantities = explode('|', $order['quantities']);
                    $prices = explode('|', $order['prices']);
                    $images = explode('|', $order['product_images']);
                ?>
                    <div class="order-item" data-status="<?php echo $order['status']; ?>">
                        <div class="order-header">
                            <div class="order-meta">
                                <h3>Order #<?php echo $order['order_number'] ?? $order['id']; ?></h3>
                                <span class="order-date"><?php echo date('M j, Y • g:i A', strtotime($order['created_at'])); ?></span>
                            </div>
                            <div class="order-status">
                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                    <?php 
                                    $icons = ['pending' => 'clock', 'confirmed' => 'check', 'shipped' => 'truck', 'delivered' => 'home'];
                                    $icon = $icons[$order['status']] ?? 'clock';
                                    ?>
                                    <i class="fas fa-<?php echo $icon; ?>"></i>
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="order-content">
                            <div class="products-section">
                                <?php for ($i = 0; $i < min(2, count($productNames)); $i++): ?>
                                    <div class="product-row">
                                        <div class="product-img">
                                            <?php if (!empty($images[$i])): ?>
                                                <img src="../assets/images/<?php echo $images[$i]; ?>" alt="Product">
                                            <?php else: ?>
                                                <div class="img-placeholder">
                                                    <i class="fas fa-image"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="product-info">
                                            <h4><?php echo htmlspecialchars($productNames[$i]); ?></h4>
                                            <p>Qty: <?php echo $quantities[$i]; ?> × ₹<?php echo number_format((float)($prices[$i] ?? 0), 2); ?></p>
                                        </div>
                                    </div>
                                <?php endfor; ?>
                                <?php if (count($productNames) > 2): ?>
                                    <div class="more-products">
                                        <i class="fas fa-plus"></i>
                                        <?php echo count($productNames) - 2; ?> more items
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="order-details">
                                <div class="detail-row">
                                    <span class="label">Total</span>
                                    <span class="value total">₹<?php echo number_format($order['total_amount'], 2); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="label">Payment</span>
                                    <span class="value payment"><?php echo strtoupper($order['payment_method']); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="label">Items</span>
                                    <span class="value"><?php echo $order['item_count']; ?> item<?php echo $order['item_count'] > 1 ? 's' : ''; ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="order-actions">
                            <button class="btn-primary-action" onclick="showTracking('<?php echo $order['order_number'] ?? $order['id']; ?>', '<?php echo $order['status']; ?>')">
                                <div class="btn-content">
                                    <i class="fas fa-route"></i>
                                    <span>Track Order</span>
                                </div>
                                <div class="btn-glow"></div>
                            </button>
                            <button class="btn-secondary-action" onclick="reorderItems(<?php echo $order['id']; ?>)">
                                <div class="btn-content">
                                    <i class="fas fa-repeat"></i>
                                    <span>Reorder</span>
                                </div>
                                <div class="btn-glow"></div>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Tracking Modal -->
<div id="trackingModal" class="tracking-modal">
    <div class="modal-backdrop" onclick="closeTracking()"></div>
    <div class="tracking-container">
        <div class="tracking-header">
            <h2><i class="fas fa-route"></i> Order Tracking</h2>
            <button class="close-btn" onclick="closeTracking()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="tracking-content" id="trackingContent">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

<!-- Order Details Modal -->
<div id="orderModal" class="modal">
    <div class="modal-overlay" onclick="closeModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h2>Order Details</h2>
            <button class="close-btn" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="orderDetails">
            <!-- Order details will be loaded here -->
        </div>
    </div>
</div>



<style>
.orders-dashboard {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    min-height: 100vh;
    padding: 2rem 0;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

.page-header {
    background: rgba(255,255,255,0.9);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    padding: 2rem;
    margin-bottom: 2rem;
    border: 1px solid rgba(255,255,255,0.2);
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.header-content h1 {
    margin: 0 0 0.5rem 0;
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.header-content p {
    margin: 0;
    color: #6b7280;
    font-size: 1rem;
}

.filter-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1.5rem;
    gap: 1rem;
}

.search-box {
    position: relative;
    flex: 1;
    max-width: 300px;
}

.search-box i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
}

.search-box input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 3rem;
    border: 2px solid #e5e7eb;
    border-radius: 50px;
    background: white;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.search-box input:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.filter-chips {
    display: flex;
    gap: 0.5rem;
}

.chip {
    background: #f3f4f6;
    border: 2px solid #e5e7eb;
    color: #6b7280;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.875rem;
}

.chip.active {
    background: #6366f1;
    color: white;
    border-color: #6366f1;
    box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: rgba(255,255,255,0.9);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    border: 1px solid rgba(255,255,255,0.2);
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.empty-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    font-size: 2rem;
    color: #9ca3af;
}

.empty-state h2 {
    margin: 0 0 0.75rem 0;
    font-size: 1.5rem;
    color: #1f2937;
    font-weight: 600;
}

.empty-state p {
    margin: 0 0 2rem 0;
    color: #6b7280;
    font-size: 1rem;
}

.btn-shop-now {
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: white;
    padding: 0.875rem 2rem;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: 0 8px 25px rgba(99, 102, 241, 0.3);
    transition: all 0.3s ease;
}

.btn-shop-now:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 35px rgba(99, 102, 241, 0.4);
}

.orders-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.order-item {
    background: rgba(255,255,255,0.9);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 20px;
    padding: 1.5rem;
    transition: all 0.3s ease;
    box-shadow: 0 8px 32px rgba(0,0,0,0.1);
}

.order-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 48px rgba(0,0,0,0.15);
    background: rgba(255,255,255,0.95);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
}

.order-meta h3 {
    margin: 0 0 0.25rem 0;
    font-size: 1.125rem;
    font-weight: 700;
    color: #1f2937;
}

.order-date {
    font-size: 0.875rem;
    color: #6b7280;
}

.order-status {
    display: flex;
    align-items: center;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: capitalize;
}

.status-badge.status-pending {
    background: rgba(245, 158, 11, 0.1);
    color: #d97706;
    border: 1px solid rgba(245, 158, 11, 0.2);
}

.status-badge.status-confirmed {
    background: rgba(59, 130, 246, 0.1);
    color: #2563eb;
    border: 1px solid rgba(59, 130, 246, 0.2);
}

.status-badge.status-shipped {
    background: rgba(139, 92, 246, 0.1);
    color: #7c3aed;
    border: 1px solid rgba(139, 92, 246, 0.2);
}

.status-badge.status-delivered {
    background: rgba(16, 185, 129, 0.1);
    color: #059669;
    border: 1px solid rgba(16, 185, 129, 0.2);
}

.order-content {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 2rem;
    margin-bottom: 1.5rem;
}

.products-section {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.product-row {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.product-img {
    width: 48px;
    height: 48px;
    flex-shrink: 0;
}

.product-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 12px;
}

.img-placeholder {
    width: 100%;
    height: 100%;
    background: #f3f4f6;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
    font-size: 1.25rem;
}

.product-info h4 {
    margin: 0 0 0.25rem 0;
    font-size: 0.875rem;
    font-weight: 600;
    color: #1f2937;
}

.product-info p {
    margin: 0;
    font-size: 0.75rem;
    color: #6b7280;
}

.more-products {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem;
    background: rgba(99, 102, 241, 0.1);
    border-radius: 12px;
    color: #6366f1;
    font-size: 0.875rem;
    font-weight: 600;
}

.order-details {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    min-width: 150px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.detail-row .label {
    font-size: 0.875rem;
    color: #6b7280;
}

.detail-row .value {
    font-weight: 600;
    color: #1f2937;
    font-size: 0.875rem;
}

.detail-row .value.total {
    font-size: 1.125rem;
    color: #059669;
}

.detail-row .value.payment {
    background: #f3f4f6;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    color: #6b7280;
}

.order-summary {
    background: rgba(0,0,0,0.02);
    border-radius: 16px;
    padding: 1rem;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.summary-row:last-child {
    margin-bottom: 0;
}

.summary-row .label {
    font-size: 0.875rem;
    color: #6b7280;
}

.amount {
    font-size: 1.25rem;
    font-weight: 700;
    color: #059669;
}

.payment-badge {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.order-actions {
    display: flex;
    gap: 0.75rem;
    padding-top: 1.5rem;
    border-top: 1px solid rgba(0,0,0,0.08);
}

.btn-primary-action, .btn-secondary-action {
    position: relative;
    flex: 1;
    padding: 0;
    border: none;
    border-radius: 16px;
    font-weight: 600;
    cursor: pointer;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    font-size: 0.875rem;
    min-height: 48px;
}

.btn-content {
    position: relative;
    z-index: 2;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.875rem 1rem;
    transition: all 0.3s ease;
}

.btn-glow {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    opacity: 0;
    transition: opacity 0.3s ease;
    border-radius: 16px;
}

.btn-primary-action {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 8px 32px rgba(102, 126, 234, 0.25);
}

.btn-primary-action .btn-glow {
    background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0.1) 100%);
}

.btn-primary-action:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 16px 48px rgba(102, 126, 234, 0.4);
}

.btn-primary-action:hover .btn-glow {
    opacity: 1;
}

.btn-primary-action:active {
    transform: translateY(-1px) scale(0.98);
}

.btn-secondary-action {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    box-shadow: 0 8px 32px rgba(16, 185, 129, 0.25);
}

.btn-secondary-action .btn-glow {
    background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0.1) 100%);
}

.btn-secondary-action:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 16px 48px rgba(16, 185, 129, 0.4);
}

.btn-secondary-action:hover .btn-glow {
    opacity: 1;
}

.btn-secondary-action:active {
    transform: translateY(-1px) scale(0.98);
}

.btn-primary-action i, .btn-secondary-action i {
    font-size: 1rem;
    transition: transform 0.3s ease;
}

.btn-primary-action:hover i, .btn-secondary-action:hover i {
    transform: scale(1.1);
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 9999;
}

.modal.active {
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.6);
    backdrop-filter: blur(10px);
}

.modal-content {
    background: white;
    border-radius: 24px;
    width: 90%;
    max-width: 600px;
    max-height: 80vh;
    overflow: hidden;
    box-shadow: 0 25px 100px rgba(0,0,0,0.3);
    position: relative;
    z-index: 1;
}

.modal-header {
    padding: 2rem;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
}

.close-btn {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #9ca3af;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 50%;
    transition: all 0.2s ease;
}

.close-btn:hover {
    background: #f3f4f6;
    color: #6b7280;
}

.modal-body {
    padding: 2rem;
    max-height: 60vh;
    overflow-y: auto;
}

.loading {
    text-align: center;
    padding: 3rem;
}

.loading-spinner {
    width: 50px;
    height: 50px;
    border: 4px solid #f3f4f6;
    border-top: 4px solid #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 1rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.order-detail-section {
    margin-bottom: 2rem;
}

.section-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.detail-label {
    font-size: 0.875rem;
    color: #6b7280;
    font-weight: 500;
}

.detail-value {
    font-weight: 600;
    color: #1f2937;
}

/* Modern Tracking Modal Styles */
.modern-tracking {
    padding: 0;
}

.tracking-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    background: linear-gradient(135deg, #f8fafc, #e2e8f0);
    border-bottom: 1px solid #e5e7eb;
}

.order-badge {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 50px;
    font-weight: 600;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.progress-ring {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.progress-circle {
    transform: rotate(-90deg);
}

.progress-bar-circle {
    transition: stroke-dashoffset 1s ease;
    animation: progress-animation 2s ease-in-out;
}

@keyframes progress-animation {
    0% { stroke-dashoffset: 157; }
    100% { stroke-dashoffset: 47; }
}

.progress-text {
    position: absolute;
    font-size: 0.875rem;
    font-weight: 700;
    color: #3b82f6;
}

.tracking-steps {
    padding: 2rem 1.5rem;
    position: relative;
}

.tracking-steps::before {
    content: '';
    position: absolute;
    left: 3rem;
    top: 2rem;
    bottom: 2rem;
    width: 2px;
    background: linear-gradient(to bottom, #10b981, #3b82f6, #e5e7eb);
    border-radius: 2px;
}

.step {
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: 1.5rem;
    margin-bottom: 2rem;
    padding-left: 1rem;
}

.step:last-child {
    margin-bottom: 0;
}

.step-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.125rem;
    position: relative;
    z-index: 2;
    border: 3px solid white;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transition: all 0.3s ease;
}

.step.completed .step-icon {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    animation: bounce-in 0.6s ease;
}

.step.active .step-icon {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    animation: pulse-glow 2s infinite;
}

.step.pending .step-icon {
    background: #f3f4f6;
    color: #9ca3af;
    border-color: #e5e7eb;
}

@keyframes bounce-in {
    0% { transform: scale(0.3); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

@keyframes pulse-glow {
    0%, 100% {
        transform: scale(1);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15), 0 0 0 0 rgba(59, 130, 246, 0.7);
    }
    50% {
        transform: scale(1.05);
        box-shadow: 0 6px 20px rgba(0,0,0,0.2), 0 0 0 8px rgba(59, 130, 246, 0);
    }
}

.step-content {
    flex: 1;
    padding-top: 0.25rem;
}

.step-content h4 {
    margin: 0 0 0.5rem 0;
    font-size: 1.125rem;
    font-weight: 700;
    color: #1f2937;
}

.step.completed .step-content h4 {
    color: #059669;
}

.step.active .step-content h4 {
    color: #3b82f6;
}

.step-content p {
    margin: 0 0 0.5rem 0;
    color: #6b7280;
    font-size: 0.875rem;
    line-height: 1.5;
}

.step-time {
    font-size: 0.75rem;
    color: #9ca3af;
    background: rgba(0,0,0,0.05);
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    display: inline-block;
    font-weight: 500;
}

.step.active .step-time {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
    animation: blink 2s infinite;
}

@keyframes blink {
    0%, 50% { opacity: 1; }
    51%, 100% { opacity: 0.6; }
}

.tracking-footer {
    padding: 1.5rem;
    background: #f8fafc;
    border-top: 1px solid #e5e7eb;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.tracking-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #6b7280;
    justify-content: center;
}

.btn-full-track {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    padding: 0.875rem 1.5rem;
    border-radius: 50px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.btn-full-track:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
}

.tracking-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 10000;
    align-items: center;
    justify-content: center;
}

.modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    backdrop-filter: blur(10px);
}

.tracking-container {
    background: white;
    border-radius: 24px;
    width: 95%;
    max-width: 400px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 25px 100px rgba(0,0,0,0.4);
    position: relative;
    z-index: 1;
    animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
    from { transform: scale(0.8) translateY(-50px); opacity: 0; }
    to { transform: scale(1) translateY(0); opacity: 1; }
}

.tracking-header {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #d946ef 100%);
    color: white;
    padding: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    overflow: hidden;
}

.tracking-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
    opacity: 0.3;
}

.tracking-header h2 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    position: relative;
    z-index: 1;
}

.tracking-header .close-btn {
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.2);
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 12px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    position: relative;
    z-index: 1;
}

.tracking-header .close-btn:hover {
    background: rgba(255,255,255,0.25);
    transform: scale(1.05);
}

.tracking-content {
    padding: 0;
}

.clean-tracking {
    background: white;
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
}

.order-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.package-icon {
    width: 40px;
    height: 40px;
    background: #3b82f6;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
}

.order-text h3 {
    margin: 0 0 0.25rem 0;
    font-size: 1rem;
    font-weight: 600;
    color: #1f2937;
}

.order-text p {
    margin: 0;
    color: #6b7280;
    font-size: 0.75rem;
}

.progress-display {
    text-align: right;
}

.progress-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: #3b82f6;
}

.progress-bar {
    height: 6px;
    background: #f1f5f9;
    margin: 0 1.5rem;
    border-radius: 3px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #3b82f6, #1d4ed8);
    border-radius: 3px;
    transition: width 1s ease;
}

.tracking-steps {
    padding: 1.5rem;
}

.step {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid #f1f5f9;
}

.step:last-child {
    border-bottom: none;
}

.step-icon-new {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f1f5f9;
    color: #9ca3af;
    font-size: 1rem;
    flex-shrink: 0;
    transition: all 0.3s ease;
    border: 2px solid #e5e7eb;
}

.step.completed .step-icon-new {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    border-color: #10b981;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.step.active .step-icon-new {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    border-color: #3b82f6;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    animation: pulse-icon 2s infinite;
}

@keyframes pulse-icon {
    0%, 100% {
        transform: scale(1);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }
    50% {
        transform: scale(1.05);
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.5);
    }
}

.step-content h4 {
    margin: 0 0 0.25rem 0;
    font-size: 0.875rem;
    font-weight: 600;
    color: #1f2937;
}

.step-content p {
    margin: 0;
    font-size: 0.75rem;
    color: #6b7280;
    line-height: 1.4;
}

.step.completed .step-content h4 {
    color: #10b981;
}

.step.active .step-content h4 {
    color: #3b82f6;
}







.cancelled-order {
    background: linear-gradient(135deg, #fee2e2, #fecaca) !important;
    border: 1px solid #f87171 !important;
}

.cancelled-icon {
    background: #ef4444 !important;
    animation: shake 0.5s ease-in-out;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-3px); }
    75% { transform: translateX(3px); }
}

.cancelled-progress {
    color: #dc2626 !important;
    font-weight: 800;
    font-size: 1rem;
}

.cancelled-bar {
    background: #fee2e2 !important;
}

.cancelled-fill {
    background: linear-gradient(90deg, #ef4444, #dc2626) !important;
}

@media (max-width: 768px) {
    .tracking-container {
        width: 98%;
        max-width: 350px;
        max-height: 85vh;
        border-radius: 20px;
    }
    
    .order-header {
        padding: 1rem;
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .tracking-steps {
        padding: 1rem;
    }
    
    .progress-bar {
        margin: 0 1rem;
    }
    
    .filter-section {
        flex-direction: column;
        gap: 1rem;
    }
    
    .search-box {
        max-width: 100%;
    }
    
    .filter-chips {
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .order-content {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .order-details {
        min-width: auto;
    }
    
    .order-actions {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .btn-primary-action, .btn-secondary-action {
        min-height: 52px;
    }
}
</style>

<script>
function openOrderDetails(orderId, orderData) {
    let order;
    try {
        order = typeof orderData === 'string' ? JSON.parse(orderData) : orderData;
    } catch (e) {
        console.error('Error parsing order data:', e);
        alert('Error loading order details');
        return;
    }
    const modal = document.getElementById('orderModal');
    const detailsContainer = document.getElementById('orderDetails');
    
    const shippingAddress = order.shipping_address ? JSON.parse(order.shipping_address) : {};
    
    detailsContainer.innerHTML = `
        <div class="order-detail-section">
            <h3 class="section-title">
                <i class="fas fa-info-circle"></i>
                Order Information
            </h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Order ID</span>
                    <span class="detail-value">#${order.order_number || order.id}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Order Date</span>
                    <span class="detail-value">${new Date(order.created_at).toLocaleDateString('en-US', {year: 'numeric', month: 'long', day: 'numeric'})}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Status</span>
                    <span class="detail-value">${order.status.charAt(0).toUpperCase() + order.status.slice(1)}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Payment Status</span>
                    <span class="detail-value">${order.payment_status.charAt(0).toUpperCase() + order.payment_status.slice(1)}</span>
                </div>
            </div>
        </div>
        
        <div class="order-detail-section">
            <h3 class="section-title">
                <i class="fas fa-credit-card"></i>
                Payment Details
            </h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Payment Method</span>
                    <span class="detail-value">${order.payment_method.toUpperCase()}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Total Amount</span>
                    <span class="detail-value">₹${parseFloat(order.total_amount).toLocaleString('en-IN', {minimumFractionDigits: 2})}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Subtotal</span>
                    <span class="detail-value">₹${parseFloat(order.subtotal).toLocaleString('en-IN', {minimumFractionDigits: 2})}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Tax (GST)</span>
                    <span class="detail-value">₹${parseFloat(order.tax_amount).toLocaleString('en-IN', {minimumFractionDigits: 2})}</span>
                </div>
            </div>
        </div>
        
        ${shippingAddress.name ? `
        <div class="order-detail-section">
            <h3 class="section-title">
                <i class="fas fa-shipping-fast"></i>
                Shipping Address
            </h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Name</span>
                    <span class="detail-value">${shippingAddress.name}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Phone</span>
                    <span class="detail-value">${shippingAddress.phone}</span>
                </div>
                <div class="detail-item" style="grid-column: 1 / -1;">
                    <span class="detail-label">Address</span>
                    <span class="detail-value">${shippingAddress.address}, ${shippingAddress.city}, ${shippingAddress.state} - ${shippingAddress.pincode}</span>
                </div>
            </div>
        </div>
        ` : ''}
    `;
    
    modal.classList.add('active');
}

function viewOrderDetails(orderId) {
    // Simple order details view
    alert('Order details for Order ID: ' + orderId);
}



function closeModal() {
    document.getElementById('orderModal').classList.remove('active');
}



// Filter and search functionality
document.querySelectorAll('.chip').forEach(chip => {
    chip.addEventListener('click', function() {
        document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        
        const filter = this.dataset.filter;
        const items = document.querySelectorAll('.order-item');
        
        items.forEach(item => {
            if (filter === 'all' || item.dataset.status === filter) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });
});

// Search functionality
document.getElementById('searchOrders')?.addEventListener('input', function() {
    const search = this.value.toLowerCase();
    const items = document.querySelectorAll('.order-item');
    
    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        if (text.includes(search)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

function showTracking(orderNumber, status) {
    const modal = document.getElementById('trackingModal');
    const content = document.getElementById('trackingContent');
    
    // Show modal with blur effect
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    // Generate 3D tracking view
    const steps = {
        'pending': { progress: 20, step: 1 },
        'confirmed': { progress: 40, step: 2 },
        'processing': { progress: 60, step: 3 },
        'shipped': { progress: 80, step: 4 },
        'delivered': { progress: 100, step: 5 },
        'cancelled': { progress: 0, step: 0 }
    };
    
    const currentStep = steps[status] || { progress: 20, step: 1 };
    
    content.innerHTML = `
        <div class="clean-tracking">
            <div class="order-header ${status === 'cancelled' ? 'cancelled-order' : ''}">
                <div class="order-info">
                    <div class="package-icon ${status === 'cancelled' ? 'cancelled-icon' : ''}">
                        <i class="fas ${status === 'cancelled' ? 'fa-times-circle' : 'fa-box'}"></i>
                    </div>
                    <div class="order-text">
                        <h3>Order #${orderNumber}</h3>
                        <p>${status === 'cancelled' ? 'Order has been cancelled' : 'Estimated delivery: 2-3 days'}</p>
                    </div>
                </div>
                <div class="progress-display">
                    <span class="progress-number ${status === 'cancelled' ? 'cancelled-progress' : ''}">${status === 'cancelled' ? 'CANCELLED' : currentStep.progress + '%'}</span>
                </div>
            </div>
            
            <div class="progress-bar ${status === 'cancelled' ? 'cancelled-bar' : ''}">
                <div class="progress-fill ${status === 'cancelled' ? 'cancelled-fill' : ''}" style="width: ${status === 'cancelled' ? '100' : currentStep.progress}%"></div>
            </div>
            
            <div class="tracking-steps">
                ${generateCleanSteps(currentStep.step)}
            </div>
        </div>
    `;
}

function generateCleanSteps(currentStep) {
    const steps = [
        { title: 'Order Placed', desc: 'Your order has been confirmed', icon: 'fas fa-shopping-cart' },
        { title: 'Processing', desc: 'We are preparing your items', icon: 'fas fa-cogs' },
        { title: 'Shipped', desc: 'Your order is on the way', icon: 'fas fa-truck' },
        { title: 'Delivered', desc: 'Package delivered successfully', icon: 'fas fa-home' }
    ];
    
    return steps.map((step, index) => {
        const stepNumber = index + 1;
        const isActive = stepNumber === currentStep;
        const isCompleted = stepNumber < currentStep;
        
        return `
            <div class="step ${isCompleted ? 'completed' : ''} ${isActive ? 'active' : ''}">
                <div class="step-icon-new">
                    <i class="${step.icon}"></i>
                </div>
                <div class="step-content">
                    <h4>${step.title}</h4>
                    <p>${step.desc}</p>
                </div>
            </div>
        `;
    }).join('');
}

function closeTracking() {
    const modal = document.getElementById('trackingModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

function reorderItems(orderId) {
    if (confirm('Add all items from this order to your cart?')) {
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
        button.disabled = true;
        
        fetch('../includes/cart_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=reorder&order_id=${orderId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                button.innerHTML = '<i class="fas fa-check"></i> Added!';
                setTimeout(() => {
                    window.location.href = 'cart.php';
                }, 1000);
            } else {
                alert('Error adding items to cart');
                button.innerHTML = originalText;
                button.disabled = false;
            }
        })
        .catch(error => {
            alert('Error adding items to cart');
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }
}
</script>

<?php include '../includes/footer.php'; ?>
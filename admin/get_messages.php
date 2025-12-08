<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Access denied');
}

try {
    // Ensure table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        subject VARCHAR(200) NOT NULL,
        message TEXT NOT NULL,
        status ENUM('unread','read') DEFAULT 'unread',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Insert sample data if table is empty
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM contact_messages");
    if ($stmt->fetch()['count'] == 0) {
        $pdo->exec("INSERT INTO contact_messages (name, email, subject, message, status) VALUES 
            ('John Doe', 'john@example.com', 'Product Inquiry', 'I am interested in your bedroom furniture collection.', 'unread'),
            ('Sarah Smith', 'sarah@example.com', 'Order Status', 'Can you please update me on my recent order?', 'read'),
            ('Mike Johnson', 'mike@example.com', 'Delivery Question', 'What are your delivery options for Mumbai?', 'unread')");
    }
    
    $stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
    $messages = $stmt->fetchAll();
    
    if (empty($messages)) {
        echo '<div class="no-messages">
                <i class="fas fa-inbox"></i>
                <h3>No Messages Yet</h3>
                <p>Contact messages will appear here when customers reach out.</p>
              </div>';
        exit;
    }
    
    foreach ($messages as $message) {
        $statusClass = $message['status'] === 'read' ? 'read' : 'unread';
        $statusIcon = $message['status'] === 'read' ? 'fa-envelope-open' : 'fa-envelope';
        
        echo '<div class="message-card ' . $statusClass . '">
                <div class="message-header">
                    <div class="sender-info">
                        <div class="sender-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="sender-details">
                            <h4>' . htmlspecialchars($message['name']) . '</h4>
                            <p>' . htmlspecialchars($message['email']) . '</p>
                        </div>
                    </div>
                    <div class="message-meta">
                        <span class="message-status">
                            <i class="fas ' . $statusIcon . '"></i>
                            ' . ucfirst($message['status']) . '
                        </span>
                        <span class="message-date">' . date('M j, Y g:i A', strtotime($message['created_at'])) . '</span>
                    </div>
                </div>
                <div class="message-content">
                    <h5>' . htmlspecialchars($message['subject']) . '</h5>
                    <p>' . htmlspecialchars(substr($message['message'], 0, 150)) . (strlen($message['message']) > 150 ? '...' : '') . '</p>
                </div>
                <div class="message-actions">
                    <button class="action-btn btn-view" onclick="viewMessage(' . $message['id'] . ')">
                        <i class="fas fa-eye"></i> View
                    </button>
                    <button class="action-btn btn-reply" onclick="replyMessage(\'' . htmlspecialchars($message['email']) . '\', \'' . htmlspecialchars($message['name']) . '\')">
                        <i class="fas fa-reply"></i> Reply
                    </button>
                    <button class="action-btn btn-delete" onclick="deleteMessage(' . $message['id'] . ')">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
              </div>';
    }
    
} catch (PDOException $e) {
    echo '<div class="error-message">
            <i class="fas fa-exclamation-triangle"></i>
            <h3>Error Loading Messages</h3>
            <p>Unable to load contact messages. Please try again.</p>
          </div>';
}
?>

<style>
.no-messages, .error-message {
    text-align: center;
    padding: 60px 20px;
    color: #6b7280;
}

.no-messages i, .error-message i {
    font-size: 4rem;
    margin-bottom: 1rem;
    color: #d1d5db;
}

.no-messages h3, .error-message h3 {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    color: #374151;
}

.message-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-left: 4px solid #e5e7eb;
    transition: all 0.3s;
}

.message-card.unread {
    border-left-color: #3b82f6;
    background: #f8faff;
}

.message-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}

.message-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.sender-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.sender-avatar {
    width: 40px;
    height: 40px;
    background: #6366f1;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 16px;
}

.sender-details h4 {
    margin: 0 0 4px 0;
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
}

.sender-details p {
    margin: 0;
    font-size: 14px;
    color: #6b7280;
}

.message-meta {
    text-align: right;
}

.message-status {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #6366f1;
    margin-bottom: 4px;
}

.message-date {
    font-size: 12px;
    color: #9ca3af;
}

.message-content h5 {
    margin: 0 0 8px 0;
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
}

.message-content p {
    margin: 0 0 16px 0;
    color: #6b7280;
    line-height: 1.5;
}

.message-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.action-btn {
    padding: 6px 12px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 4px;
    transition: all 0.2s;
}

.btn-view {
    background: #3b82f6;
    color: white;
}

.btn-reply {
    background: #10b981;
    color: white;
}

.btn-delete {
    background: #ef4444;
    color: white;
}

.action-btn:hover {
    transform: translateY(-1px);
    opacity: 0.9;
}
</style>

<script>
function viewMessage(id) {
    fetch('get_single_message.php?id=' + id)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessageModal(data.message);
        } else {
            alert('Error loading message details');
        }
    })
    .catch(error => {
        alert('Error loading message');
    });
}

function showMessageModal(message) {
    const modal = document.createElement('div');
    modal.className = 'message-modal';
    modal.innerHTML = `
        <div class="modal-backdrop" onclick="closeModal(this)"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-envelope"></i> Message Details</h3>
                <button onclick="closeModal(this)" class="close-btn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="message-detail">
                    <strong>From:</strong> ${message.name} (${message.email})
                </div>
                <div class="message-detail">
                    <strong>Phone:</strong> ${message.phone || 'Not provided'}
                </div>
                <div class="message-detail">
                    <strong>Subject:</strong> ${message.subject}
                </div>
                <div class="message-detail">
                    <strong>Date:</strong> ${new Date(message.created_at).toLocaleString()}
                </div>
                <div class="message-detail">
                    <strong>Message:</strong><br>
                    <div class="message-text">${message.message}</div>
                </div>
            </div>
            <div class="modal-footer">
                <button onclick="replyMessage('${message.email}', '${message.name}')" class="btn btn-primary">
                    <i class="fas fa-reply"></i> Reply
                </button>
                <button onclick="closeModal(this)" class="btn btn-outline">
                    Close
                </button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

function closeModal(element) {
    const modal = element.closest('.message-modal');
    if (modal) {
        modal.remove();
    }
}

function replyMessage(email, name) {
    const subject = 'Re: Your inquiry to INTERNO';
    const body = 'Dear ' + name + ',\n\nThank you for contacting INTERNO.\n\n';
    window.location.href = 'mailto:' + email + '?subject=' + encodeURIComponent(subject) + '&body=' + encodeURIComponent(body);
}

function deleteMessage(id) {
    if (confirm('Are you sure you want to delete this message?')) {
        fetch('delete_message.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + id
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadContactMessages();
                showNotification('Message deleted successfully', 'success');
            } else {
                alert('Error deleting message');
            }
        })
        .catch(error => {
            alert('Error deleting message');
        });
    }
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#10b981' : '#ef4444'};
        color: white;
        padding: 12px 16px;
        border-radius: 8px;
        z-index: 10000;
        animation: slideIn 0.3s ease;
    `;
    notification.innerHTML = `<i class="fas fa-${type === 'success' ? 'check' : 'times'}"></i> ${message}`;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}
</script>

<style>
.message-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
}

.modal-content {
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
    position: relative;
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    color: #1f2937;
}

.close-btn {
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
    color: #6b7280;
}

.modal-body {
    padding: 20px;
}

.message-detail {
    margin-bottom: 15px;
    color: #374151;
}

.message-text {
    background: #f9fafb;
    padding: 15px;
    border-radius: 8px;
    margin-top: 8px;
    line-height: 1.5;
}

.modal-footer {
    padding: 20px;
    border-top: 1px solid #e5e7eb;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}
</style>
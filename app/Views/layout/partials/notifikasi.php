<?php if (!empty($notifikasi)) : ?>
    <?php foreach ($notifikasi as $notif) : ?>
        <li class="p-3 border-bottom notification-item" 
            data-notification-id="<?= esc($notif['id']) ?>"
            data-navigation-url="<?= esc($notif['navigation_url'] ?? '#') ?>"
            style="cursor: pointer; position: relative;">
            
            <!-- Titik merah untuk notifikasi belum dibaca -->
            <span class="unread-indicator position-absolute"></span>
            
            <div class="notification-content">
                <div class="mb-2">
                    <div class="text-dark" style="font-size: 0.9rem; line-height: 1.4;">
                        <?= esc($notif['message']) ?>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <?= esc($notif['creator_name'] ?? 'Unknown User') ?>
                    </small>
                    <small class="text-muted">
                        <time datetime="<?= esc($notif['createddate']) ?>">
                            <?= date('M d, Y H:i', strtotime($notif['createddate'])) ?>
                        </time>
                    </small>
                </div>
            </div>
        </li>
    <?php endforeach; ?>
    
    <li class="p-3 text-center">
        <button class="btn btn-outline-secondary btn-sm" onclick="markAllAsRead()">
            Mark All as Read
        </button>
    </li>
<?php else : ?>
    <li class="text-center py-4">
        <div class="text-muted">
            <p class="mb-0">No new notifications</p>
        </div>
    </li>
<?php endif; ?>

<!-- CSS for notification styling -->
<style>
    .notif-badge {
        top: -2px;
        right: -8px;
        min-width: 18px;
        height: 18px;
        font-size: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }
    
    .notification-item {
        transition: background-color 0.2s ease;
        border-left: 3px solid transparent;
    }
    
    .notification-item:hover {
        background-color: #f8f9fa !important;
        border-left-color: #007bff;
    }
    
    .unread-indicator {
        top: 50%;
        left: 8px;
        transform: translateY(-50%);
        width: 6px;
        height: 6px;
        background-color: #dc3545;
        border-radius: 50%;
        animation: pulse-dot 2s infinite;
    }
    
    @keyframes pulse-dot {
        0% { opacity: 1; transform: translateY(-50%) scale(1); }
        50% { opacity: 0.7; transform: translateY(-50%) scale(1.1); }
        100% { opacity: 1; transform: translateY(-50%) scale(1); }
    }
    
    .notification-content {
        margin-left: 20px;
    }
    
    .notification-item.read .unread-indicator {
        display: none !important;
    }
    
    .notification-item.loading .unread-indicator {
        opacity: 0.5;
        animation: none;
    }
</style>

<script>
// Simple notification system - hanya pakai JavaScript untuk mark as read
document.addEventListener('DOMContentLoaded', function() {
    initNotificationSystem();
});

let isProcessing = false;

function initNotificationSystem() {
    // Attach click handlers
    document.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('click', handleNotificationClick);
    });
    
    // Auto refresh setiap 30 detik
    setInterval(refreshNotifications, 30000);
}

function handleNotificationClick(e) {
    e.preventDefault();
    
    if (this.classList.contains('read')) {
        // Sudah dibaca, langsung navigate
        if (this.dataset.navigationUrl && this.dataset.navigationUrl !== '#') {
            window.location.href = this.dataset.navigationUrl;
        }
        return;
    }
    
    const notificationId = this.dataset.notificationId;
    const navigationUrl = this.dataset.navigationUrl;
    
    if (!notificationId) return;
    
    // Mark as read di frontend dulu (immediate feedback)
    markAsReadVisually(this);
    
    // Kirim ke server
    markNotificationAsRead(notificationId, () => {
        // Success - navigate
        if (navigationUrl && navigationUrl !== '#') {
            setTimeout(() => {
                window.location.href = navigationUrl;
            }, 300);
        }
    });
}

function markAsReadVisually(element) {
    element.classList.add('read');
    element.classList.remove('loading');
    
    const indicator = element.querySelector('.unread-indicator');
    if (indicator) {
        indicator.style.display = 'none';
    }
    
    updateBadgeCount();
}

function markNotificationAsRead(notificationId, callback) {
    if (isProcessing) return;
    isProcessing = true;
    
    fetch('<?= base_url('notification/markAsRead') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            notification_id: notificationId
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Mark as read response:', data);
        if (callback) callback();
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    })
    .finally(() => {
        isProcessing = false;
    });
}

function markAllAsRead() {
    if (isProcessing) return;
    isProcessing = true;
    
    // Mark all visually first
    document.querySelectorAll('.notification-item:not(.read)').forEach(item => {
        markAsReadVisually(item);
    });
    
    // Send to server
    fetch('<?= base_url('notification/markAsRead') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            mark_all: true
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Mark all as read response:', data);
        
        // Update display to show no notifications
        setTimeout(() => {
            const notifList = document.getElementById('notif-list');
            if (notifList) {
                notifList.innerHTML = `
                    <li class="dropdown-header py-2">Notifications</li>
                    <li class="text-center py-4">
                        <div class="text-muted">
                            <p class="mb-0">No new notifications</p>
                        </div>
                    </li>
                `;
            }
            
            const badge = document.querySelector('.notif-badge');
            if (badge) badge.remove();
        }, 1000);
    })
    .catch(error => {
        console.error('Error marking all notifications as read:', error);
    })
    .finally(() => {
        isProcessing = false;
    });
}

function updateBadgeCount() {
    const unreadCount = document.querySelectorAll('.notification-item:not(.read)').length;
    const badge = document.querySelector('.notif-badge');
    
    if (unreadCount > 0) {
        if (badge) {
            badge.textContent = unreadCount;
        }
    } else {
        if (badge) badge.remove();
    }
}

function refreshNotifications() {
    if (isProcessing) return;
    
    fetch('<?= base_url('notification/fetch') ?>', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success' && data.notifikasi) {
            updateNotificationDisplay(data.notifikasi);
        }
    })
    .catch(error => {
        console.error('Error refreshing notifications:', error);
    });
}

function updateNotificationDisplay(notifications) {
    const notifList = document.getElementById('notif-list');
    if (!notifList) return;
    
    if (notifications.length > 0) {
        let html = '<li class="dropdown-header py-2">Notifications</li>';
        
        notifications.forEach(notif => {
            const date = new Date(notif.createddate);
            const formattedDate = date.toLocaleDateString('en-US', {
                day: '2-digit', 
                month: 'short', 
                year: 'numeric',
                hour: '2-digit', 
                minute: '2-digit'
            });
            
            html += `
                <li class="p-3 border-bottom notification-item" 
                    data-notification-id="${notif.id}"
                    data-navigation-url="${notif.navigation_url || '#'}"
                    style="cursor: pointer; position: relative;">
                    
                    <span class="unread-indicator position-absolute"></span>
                    
                    <div class="notification-content">
                        <div class="mb-2">
                            <div class="text-dark" style="font-size: 0.9rem; line-height: 1.4;">
                                ${escapeHtml(notif.message)}
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                ${escapeHtml(notif.creator_name || 'Unknown User')}
                            </small>
                            <small class="text-muted">
                                <time datetime="${notif.createddate}">${formattedDate}</time>
                            </small>
                        </div>
                    </div>
                </li>
            `;
        });
        
        html += `
            <li class="p-3 text-center">
                <button class="btn btn-outline-secondary btn-sm" onclick="markAllAsRead()">
                    Mark All as Read
                </button>
            </li>
        `;
        
        notifList.innerHTML = html;
        updateBadgeCount();
        initNotificationSystem(); // Re-init handlers
    } else {
        notifList.innerHTML = `
            <li class="dropdown-header py-2">Notifications</li>
            <li class="text-center py-4">
                <div class="text-muted">
                    <p class="mb-0">No new notifications</p>
                </div>
            </li>
        `;
        
        const badge = document.querySelector('.notif-badge');
        if (badge) badge.remove();
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
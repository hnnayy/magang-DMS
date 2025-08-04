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
        display: none !important; /* Force hide unread indicator for read notifications */
    }
    
    .notification-item.loading .unread-indicator {
        opacity: 0.5;
        transition: opacity 0.3s;
    }
</style>

<script>
// Flag to prevent auto-refresh during markAsRead
let isMarkingNotification = false;

document.addEventListener('DOMContentLoaded', function() {
    // Attach click event to all notification items
    document.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            if (this.classList.contains('read')) {
                console.log('Notification already read, navigating to:', this.dataset.navigationUrl);
                if (this.dataset.navigationUrl && this.dataset.navigationUrl !== '#') {
                    window.location.href = this.dataset.navigationUrl;
                }
                return;
            }

            const notificationId = this.dataset.notificationId;
            const navigationUrl = this.dataset.navigationUrl;

            // Show loading state
            this.classList.add('loading');
            console.log('Marking notification ID:', notificationId);

            // Mark notification as read
            isMarkingNotification = true;
            markNotificationAsRead(notificationId, () => {
                // Success: Mark item as read visually
                this.classList.remove('loading');
                this.classList.add('read');
                this.querySelector('.unread-indicator').style.display = 'none'; // Force hide
                updateNotificationBadge();
                console.log('Notification ID', notificationId, 'marked as read successfully');

                // Navigate to the appropriate page
                if (navigationUrl && navigationUrl !== '#') {
                    window.location.href = navigationUrl;
                }
                isMarkingNotification = false;
            }, () => {
                // Error: Restore loading state and show error
                this.classList.remove('loading');
                isMarkingNotification = false;
                console.error('Failed to mark notification ID', notificationId);
            });
        });
    });
});

// Function to mark individual notification as read
function markNotificationAsRead(notificationId, successCallback, errorCallback) {
    fetch('<?= base_url('notification/markAsRead') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        },
        body: JSON.stringify({
            notification_id: notificationId,
            <?= config('Security')->tokenName ?>: document.querySelector('meta[name="csrf-hash"]')?.getAttribute('content')
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            if (successCallback) successCallback();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Failed to mark notification as read',
                confirmButtonColor: '#dc3545'
            });
            if (errorCallback) errorCallback();
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while marking the notification as read',
            confirmButtonColor: '#dc3545'
        });
        if (errorCallback) errorCallback();
    });
}

// Function to mark all notifications as read
function markAllAsRead() {
    isMarkingNotification = true;
    fetch('<?= base_url('notification/markAsRead') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        },
        body: JSON.stringify({
            <?= config('Security')->tokenName ?>: document.querySelector('meta[name="csrf-hash"]')?.getAttribute('content')
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById('notif-list').innerHTML = `
                <li class="dropdown-header py-2">Notifications</li>
                <li class="text-center py-4">
                    <div class="text-muted">
                        <p class="mb-0">No new notifications</p>
                    </div>
                </li>
            `;
            const badge = document.querySelector('.notif-badge');
            if (badge) badge.remove();
            console.log('All notifications marked as read');
        } else {
            console.error('Failed to mark all notifications as read:', data.message);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Failed to mark all notifications as read',
                confirmButtonColor: '#dc3545'
            });
        }
        isMarkingNotification = false;
    })
    .catch(error => {
        console.error('Error marking all notifications as read:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while marking notifications as read',
            confirmButtonColor: '#dc3545'
        });
        isMarkingNotification = false;
    });
}

// Auto-refresh notifications every 5 seconds
setInterval(function() {
    if (isMarkingNotification) {
        console.log('Auto-refresh skipped due to ongoing markAsRead');
        return;
    }

    fetch('<?= base_url('notification/fetch') ?>', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            updateNotificationDisplay(data.notifikasi);
        } else {
            console.error('Error fetching notifications:', data.message);
        }
    })
    .catch(error => {
        console.error('Error fetching notifications:', error);
    });
}, 5000);

// Function to update notification display
function updateNotificationDisplay(notifications) {
    const notifList = document.getElementById('notif-list');
    
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
                                ${notif.message}
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                ${notif.creator_name || 'Unknown User'}
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
        updateNotificationBadge(notifications.length);
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

    // Re-attach event listeners to new notification items
    document.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            if (this.classList.contains('read')) {
                console.log('Notification already read, navigating to:', this.dataset.navigationUrl);
                if (this.dataset.navigationUrl && this.dataset.navigationUrl !== '#') {
                    window.location.href = this.dataset.navigationUrl;
                }
                return;
            }

            const notificationId = this.dataset.notificationId;
            const navigationUrl = this.dataset.navigationUrl;

            this.classList.add('loading');
            console.log('Marking notification ID:', notificationId);

            isMarkingNotification = true;
            markNotificationAsRead(notificationId, () => {
                this.classList.remove('loading');
                this.classList.add('read');
                this.querySelector('.unread-indicator').style.display = 'none';
                updateNotificationBadge();
                console.log('Notification ID', notificationId, 'marked as read successfully');

                if (navigationUrl && navigationUrl !== '#') {
                    window.location.href = navigationUrl;
                }
                isMarkingNotification = false;
            }, () => {
                this.classList.remove('loading');
                isMarkingNotification = false;
                console.error('Failed to mark notification ID', notificationId);
            });
        });
    });
}

// Function to update badge count
function updateNotificationBadge(count = null) {
    const badge = document.querySelector('.notif-badge');
    const iconWrapper = document.querySelector('.notif-icon-wrapper');
    
    if (count === null) {
        count = document.querySelectorAll('.notification-item:not(.read)').length;
    }
    
    console.log('Updating badge count to:', count);
    
    if (count > 0) {
        if (badge) {
            badge.textContent = count;
            badge.innerHTML = count + '<span class="visually-hidden">notifikasi belum dibaca</span>';
        } else if (iconWrapper) {
            const newBadge = document.createElement('span');
            newBadge.className = 'notif-badge position-absolute translate-middle badge rounded-pill bg-danger';
            newBadge.innerHTML = count + '<span class="visually-hidden">notifikasi belum dibaca</span>';
            iconWrapper.appendChild(newBadge);
        }
    } else {
        if (badge) badge.remove();
    }
}
</script>
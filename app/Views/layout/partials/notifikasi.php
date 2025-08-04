<?php if (!empty($notifikasi)) : ?>
    <?php foreach ($notifikasi as $notif) : ?>
        <li class="p-3 border-bottom">
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
                    <time datetime="<?= $notif['createddate'] ?>">
                        <?= date('M d, Y H:i', strtotime($notif['createddate'])) ?>
                    </time>
                </small>
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

<script>
function markAllAsRead() {
    fetch('<?= base_url('notification/markAsRead') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            csrf_token_name: document.querySelector('meta[name="csrf-hash"]').getAttribute('content')
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById('notif-list').innerHTML = `
                <li class="dropdown-header">Notifications</li>
                <li class="text-center py-4">
                    <div class="text-muted">
                        <p class="mb-0">No new notifications</p>
                    </div>
                </li>
            `;
            
            const badge = document.querySelector('.notif-badge');
            if (badge) {
                badge.remove();
            }
            
            console.log('All notifications marked as read');
        } else {
            console.error('Failed to mark notifications as read:', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Auto refresh notifications every 30 seconds
setInterval(function() {
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
        console.error('Error fetching notifications:', error);
    });
}, 30000);

function updateNotificationDisplay(notifications) {
    const notifList = document.getElementById('notif-list');
    const badge = document.querySelector('.notif-badge');
    
    if (notifications.length > 0) {
        let html = '<li class="dropdown-header">Notifications</li>';
        
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
                <li class="p-3 border-bottom">
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
        
        // Update badge
        if (badge) {
            badge.textContent = notifications.length;
        } else {
            const iconWrapper = document.querySelector('.notif-icon-wrapper');
            if (iconWrapper) {
                const newBadge = document.createElement('span');
                newBadge.className = 'notif-badge position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
                newBadge.textContent = notifications.length;
                iconWrapper.appendChild(newBadge);
            }
        }
    } else {
        notifList.innerHTML = `
            <li class="dropdown-header">Notifications</li>
            <li class="text-center py-4">
                <div class="text-muted">
                    <p class="mb-0">No new notifications</p>
                </div>
            </li>
        `;
        
        if (badge) {
            badge.remove();
        }
    }
}
</script>
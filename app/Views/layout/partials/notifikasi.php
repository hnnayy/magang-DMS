<?php if (!empty($notifikasi)) : ?>
    <?php foreach ($notifikasi as $notif) : ?>
        <div class="notification-item <?= $notif['status'] == 1 ? 'unread' : 'read' ?>" 
             data-notification-id="<?= esc($notif['id']) ?>"
             data-navigation-url="<?= esc($notif['navigation_url'] ?? '#') ?>"
             data-status="<?= esc($notif['status'] ?? 1) ?>">
            
            <?php if ($notif['status'] == 1) : ?>
                <span class="unread-indicator"></span>
            <?php elseif ($notif['status'] == 2) : ?>
                <span class="read-indicator"><i class="bi bi-check-circle"></i></span>
            <?php endif; ?>
            
            <div class="notification-content">
                <div class="notification-message"><?= esc($notif['message']) ?></div>
                <div class="notification-meta">
                    <span class="creator"><?= esc($notif['creator_name'] ?? 'Unknown User') ?></span>
                    <span class="date">
                        <time datetime="<?= esc($notif['createddate']) ?>">
                            <?= date('M d, Y H:i', strtotime($notif['createddate'])) ?>
                        </time>
                    </span>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    
    <div class="notification-footer">
        <button class="mark-all-btn" onclick="markAllAsRead()">Mark All as Read</button>
    </div>
<?php else : ?>
    <div class="notification-empty">No new notifications</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const notifIcon = document.querySelector('#notificationDropdown .notif-icon-wrapper');
    const notifList = document.querySelector('#notif-list');
    
    if (!notifIcon || !notifList) {
        console.error('Notification elements not found:', { notifIcon, notifList });
        return;
    }

    notifIcon.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        notifList.classList.toggle('show');
    });

    document.addEventListener('click', function(e) {
        if (!notifList.contains(e.target) && !notifIcon.contains(e.target)) {
            notifList.classList.remove('show');
        }
    });

    document.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.dataset.notificationId;
            const url = this.dataset.navigationUrl;
            const status = parseInt(this.dataset.status);
            
            if (status === 1) {
                this.classList.remove('unread');
                this.classList.add('read');
                const indicator = this.querySelector('.unread-indicator');
                if (indicator) indicator.remove();
                const readIndicator = document.createElement('span');
                readIndicator.className = 'read-indicator';
                readIndicator.innerHTML = '<i class="bi bi-check-circle"></i>';
                this.prepend(readIndicator);
                
                fetch('<?= base_url('notification/markAsRead') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-hash"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        notification_id: id,
                        [document.querySelector('meta[name="csrf-token"]').getAttribute('content')]: document.querySelector('meta[name="csrf-hash"]').getAttribute('content')
                    })
                }).then(response => response.json()).then(data => {
                    if (data.status === 'success' && url && url !== '#') {
                        window.location.href = url;
                    }
                }).catch(error => console.error('Error marking notification:', error));
            } else if (url && url !== '#') {
                window.location.href = url;
            }
            
            updateBadgeCount();
        });
    });

    function markAllAsRead() {
        document.querySelectorAll('.notification-item.unread').forEach(item => {
            item.classList.remove('unread');
            item.classList.add('read');
            const indicator = item.querySelector('.unread-indicator');
            if (indicator) indicator.remove();
            const readIndicator = document.createElement('span');
            readIndicator.className = 'read-indicator';
            readIndicator.innerHTML = '<i class="bi bi-check-circle"></i>';
            item.prepend(readIndicator);
        });

        fetch('<?= base_url('notification/markAsRead') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-hash"]').getAttribute('content')
            },
            body: JSON.stringify({
                mark_all: true,
                [document.querySelector('meta[name="csrf-token"]').getAttribute('content')]: document.querySelector('meta[name="csrf-hash"]').getAttribute('content')
            })
        }).then(response => response.json()).then(data => {
            if (data.status === 'success') {
                updateBadgeCount();
            }
        }).catch(error => console.error('Error marking all notifications:', error));
    }

    function updateBadgeCount() {
        const unreadCount = document.querySelectorAll('.notification-item.unread').length;
        const badge = document.querySelector('.notif-badge');
        if (unreadCount > 0) {
            if (!badge) {
                const badgeSpan = document.createElement('span');
                badgeSpan.className = 'notif-badge';
                badgeSpan.textContent = unreadCount;
                document.querySelector('.notif-icon-wrapper').appendChild(badgeSpan);
            } else {
                badge.textContent = unreadCount;
            }
        } else if (badge) {
            badge.remove();
        }
    }

    setInterval(function() {
        fetch('<?= base_url('notification/fetch') ?>', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(response => response.json()).then(data => {
            if (data.status === 'success' && data.notifikasi) {
                const notifList = document.querySelector('#notif-list');
                let html = '<div class="notification-header">Notifications</div>';
                if (data.notifikasi.length > 0) {
                    data.notifikasi.forEach(notif => {
                        html += `
                            <div class="notification-item ${notif.status == 1 ? 'unread' : 'read'}" 
                                 data-notification-id="${notif.id}"
                                 data-navigation-url="${notif.navigation_url || '#'}"
                                 data-status="${notif.status || 1}">
                                ${notif.status == 1 ? '<span class="unread-indicator"></span>' : ''}
                                ${notif.status == 2 ? '<span class="read-indicator"><i class="bi bi-check-circle"></i></span>' : ''}
                                <div class="notification-content">
                                    <div class="notification-message">${notif.message}</div>
                                    <div class="notification-meta">
                                        <span class="creator">${notif.creator_name || 'Unknown User'}</span>
                                        <span class="date"><time datetime="${notif.createddate}">${new Date(notif.createddate).toLocaleString('en-US', { month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</time></span>
                                    </div>
                                </div>
                            </div>`;
                    });
                    html += '<div class="notification-footer"><button class="mark-all-btn" onclick="markAllAsRead()">Mark All as Read</button></div>';
                } else {
                    html += '<div class="notification-empty">No new notifications</div>';
                }
                notifList.innerHTML = html;
                updateBadgeCount();
                document.querySelectorAll('.notification-item').forEach(item => {
                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        const id = this.dataset.notificationId;
                        const url = this.dataset.navigationUrl;
                        const status = parseInt(this.dataset.status);
                        if (status === 1) {
                            this.classList.remove('unread');
                            this.classList.add('read');
                            const indicator = this.querySelector('.unread-indicator');
                            if (indicator) indicator.remove();
                            const readIndicator = document.createElement('span');
                            readIndicator.className = 'read-indicator';
                            readIndicator.innerHTML = '<i class="bi bi-check-circle"></i>';
                            this.prepend(readIndicator);
                            fetch('<?= base_url('notification/markAsRead') ?>', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-hash"]').getAttribute('content')
                                },
                                body: JSON.stringify({
                                    notification_id: id,
                                    [document.querySelector('meta[name="csrf-token"]').getAttribute('content')]: document.querySelector('meta[name="csrf-hash"]').getAttribute('content')
                                })
                            }).then(response => response.json()).then(data => {
                                if (data.status === 'success' && url && url !== '#') {
                                    window.location.href = url;
                                }
                            }).catch(error => console.error('Error marking notification:', error));
                        } else if (url && url !== '#') {
                            window.location.href = url;
                        }
                        updateBadgeCount();
                    });
                });
            }
        }).catch(error => console.error('Error fetching notifications:', error));
    }, 30000);
});
</script>
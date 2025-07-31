<?php if (!empty($notifikasi)) : ?>
    <?php foreach ($notifikasi as $notif) : ?>
        <li class="notification-item">
            <a class="dropdown-item d-flex align-items-center" href="<?= base_url('document-submission-list?reference_id=' . esc($notif['reference_id'] ?? '')) ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 50 50" class="me-2">
                    <path d="M 30.398438 2 L 7 2 L 7 48 L 43 48 L 43 14.601563 Z M 15 28 L 31 28 L 31 30 L 15 30 Z M 35 36 L 15 36 L 15 34 L 35 34 Z M 35 24 L 15 24 L 15 22 L 35 22 Z M 30 15 L 30 4.398438 L 40.601563 15 Z"></path>
                </svg>
                <div class="notification-content">
                    <div class="fw-bold"><?= esc($notif['creator_name'] ?? 'Pengguna Tidak Dikenal') ?> (ID: <?= esc($notif['creator_id'] ?? 'N/A') ?>)</div>
                    <div><?= esc($notif['message']) ?></div>
                    <small class="text-muted"><?= date('d M Y H:i', strtotime($notif['createddate'])) ?></small>
                </div>
                <span class="ms-auto text-muted">...</span>
            </a>
        </li>
    <?php endforeach; ?>
<?php else : ?>
    <li class="notification-item">
        <span class="dropdown-item text-muted text-center">Tidak ada notifikasi baru</span>
    </li>
<?php endif; ?>

<?php if (!empty($notifikasi)) : ?>
    <?php foreach ($notifikasi as $notif) : ?>
        <li>
            <a class="dropdown-item" href="<?= base_url($notif['link'] ?? '#') ?>">
                <div class="d-flex justify-content-between">
                    <div><?= esc($notif['message']) ?></div>
                    <small class="text-muted"><?= date('d M H:i', strtotime($notif['created_at'])) ?></small>
                </div>
            </a>
        </li>
    <?php endforeach; ?>
<?php else : ?>
    <li>
        <span class="dropdown-item text-muted">Tidak ada notifikasi</span>
    </li>
<?php endif; ?>

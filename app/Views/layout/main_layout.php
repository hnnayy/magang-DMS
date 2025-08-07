<!DOCTYPE html>
<html lang="en">
<head>
    <?= $this->include('layout/header') ?>
</head>
<body>
    <?= $this->include('partials/alerts') ?>
    <?= $this->include('layout/sidebar') ?>

    <div class="sidebar-overlay" style="display:none; position: fixed; inset: 0; z-index: 998;"></div>

    <div class="main-content">
        <?= $this->renderSection('content') ?>
    </div>

    <?= $this->include('layout/footer') ?>
    <?= $this->renderSection('script') ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= base_url('assets/js/dashboard.js') ?>"></script>
</body>
</html>
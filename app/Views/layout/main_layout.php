<!DOCTYPE html>
<html lang="en">
<head>
    <?= $this->include('layout/header') ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- Impor SweetAlert -->
</head>
<body>
    <?= $this->include('layout/sidebar') ?>

    <div class="main-content">
        <?= $this->renderSection('content') ?>
    </div>

    <?= $this->include('layout/footer') ?>
</body>
</html>
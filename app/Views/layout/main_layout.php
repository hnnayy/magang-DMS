<!DOCTYPE html>
<html lang="en">
<head>
    <?= $this->include('layout/header') ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- Impor SweetAlert -->
</head>


<?= $this->include('partials/alerts') ?>


<body>
    <?= $this->include('layout/sidebar') ?>

    <div class="main-content">
        <?= $this->renderSection('content') ?>
    </div>

    <?= $this->include('layout/footer') ?>

    <!-- Tambahkan ini untuk render JS dari setiap halaman -->
    <?= $this->renderSection('script') ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <?= $this->include('layout/header') ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- Impor SweetAlert -->
</head>

<?php if (session('swal')) : ?>
<script>
    Swal.fire({
        icon:   '<?= esc(session('swal')['icon'])  ?>',
        title:  '<?= esc(session('swal')['title']) ?>',
        text:   '<?= esc(session('swal')['text'])  ?>',
        confirmButtonColor: '#6868ff'
    });
</script>
<?php endif; ?>

<body>
    <?= $this->include('layout/sidebar') ?>

    <div class="main-content">
        <?= $this->renderSection('content') ?>
    </div>

    <?= $this->include('layout/footer') ?>
</body>
</html>
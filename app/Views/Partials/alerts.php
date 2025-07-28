<!-- partials/alerts.php -->
<?php if (session()->getFlashdata('added_message')): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '<?= session()->getFlashdata('added_message') ?>',
        });
    </script>
<?php elseif (session()->getFlashdata('updated_message')): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '<?= session()->getFlashdata('updated_message') ?>',
        });
    </script>
<?php elseif (session()->getFlashdata('error')): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Failed!',
            text: '<?= session()->getFlashdata('error') ?>',
        });
    </script>
<?php elseif (session()->getFlashdata('deleted_message')): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '<?= session()->getFlashdata('deleted_message') ?>',
            confirmButtonText: 'OK'
        });
    </script>
<?php endif; ?>

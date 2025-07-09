<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="container">
    <div class="form-section">
        <div class="form-section-divider">
            <h2><?= $title ?? 'Tambah Menu' ?></h2>
        </div>

        <form id="createMenuForm" method="post" action="<?= base_url('Menu/store') ?>">
            <?= csrf_field() ?>

            <!-- Nama Menu -->
            <div class="form-group">
                <label class="form-label" for="menuName">Nama Menu</label>
                <input type="text" id="menuName" name="menu_name" class="form-input" placeholder="Tulis Nama Menu disini..." required>
            </div>

            <!-- Status -->
            <div class="form-group">
                <label class="form-label d-block">Status</label>
                <div style="display: flex; gap: 30px;">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="status" id="statusActive" value="1" required checked>
                        <label class="form-check-label" for="statusActive">Active</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="status" id="statusInactive" value="0" required>
                        <label class="form-check-label" for="statusInactive">Inactive</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="submit-btn">Submit</button>
        </form>
    </div>

    <div class="illustration-section">
        <img src="<?= base_url('assets/images/profil/Logo_Telkom_University.png') ?>" alt="Menu Illustration" class="illustration-img">
    </div>
</div>

<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    <?php if (session()->getFlashdata('success')): ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '<?= session()->getFlashdata('success') ?>',
            confirmButtonText: 'OK',
            confirmButtonColor: '#6C63FF'
        }).then(() => {
            window.location.href = "<?= base_url('Menu') ?>";
        });
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: '<?= session()->getFlashdata('error') ?>',
            confirmButtonText: 'OK',
            confirmButtonColor: '#6C63FF'
        });
    <?php endif; ?>
</script>

<?= $this->endSection() ?>

<?= $this->extend('layout/main_layout') ?> 
<?= $this->section('content') ?>

<div class="container">
    <div class="form-section">
        <div class="form-section-divider">
            <h2><?= $title ?? 'Tambah Role Baru' ?></h2>
        </div>

        <form id="createRoleForm" method="post" action="<?= base_url('create-role/store') ?>">
            <?= csrf_field() ?>

            <!-- Nama Role -->
            <div class="form-group">
                <label class="form-label" for="nama">Nama Role</label>
                <input type="text" id="nama" name="nama" class="form-input" placeholder="Tulis Nama Role disini..." required>
            </div>

            <!-- Level -->
            <div class="form-group">
                <label class="form-label" for="level">Level</label>
                <input type="number" id="level" name="level" class="form-input" placeholder="Masukkan Level..." required>
            </div>

            <!-- Deskripsi -->
            <div class="form-group">
                <label class="form-label" for="desc">Deskripsi</label>
                <textarea id="desc" name="desc" class="form-input" placeholder="Deskripsikan role ini..." rows="3" required></textarea>
            </div>

            <!-- Status -->
            <div class="form-group">
                <label class="form-label d-block">Status</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="active" value="active" required>
                    <label class="form-check-label" for="active">Active</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="inactive" value="inactive">
                    <label class="form-check-label" for="inactive">Inactive</label>
                </div>
            </div>

            <button type="submit" class="submit-btn">Submit</button>
        </form>
    </div>

    <div class="illustration-section">
        <img src="<?= base_url('assets/images/profil/Logo_Telkom_University.png') ?>" alt="Role Illustration" class="illustration-img">
    </div>
</div>

<!-- SweetAlert (Notifikasi) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    <?php if (session()->getFlashdata('success')): ?>
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: '<?= session()->getFlashdata('success') ?>',
            confirmButtonText: 'OK'
        });
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: '<?= session()->getFlashdata('error') ?>',
            confirmButtonText: 'OK'
        });
    <?php endif; ?>
</script>

<?= $this->endSection() ?>

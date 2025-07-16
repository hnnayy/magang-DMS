<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="container-fluid px-4">
    <h4 class="fw-bold py-3 mb-4">Profil Saya</h4>

    <div class="card p-4">
        <form action="<?= base_url('profile/update') ?>" method="post">
            <?= csrf_field() ?>
            <div class="row align-items-center">
                <!-- Kiri: Avatar dan Nama -->
                <div class="col-md-4 text-center border-end">
                    <img src="<?= base_url('assets/images/profil/avatarprofile.jpg') ?>" 
                         class="rounded-circle mb-3" 
                         alt="Avatar" style="width: 180px; height: 180px; object-fit: cover;">
                    <h5 class="mb-1"><?= esc(session('fullname')) ?></h5>
                    <p class="text-muted"><?= esc(session('role_name') ?? '-') ?></p>
                </div>

                <!-- Kanan: Form -->
                <div class="col-md-8 ps-md-4 pt-3 pt-md-0">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Username</label>
                        <input type="text" name="username" class="form-control" 
                               value="<?= esc(session('username')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name</label>
                        <input type="text" name="fullname" class="form-control" 
                               value="<?= esc(session('fullname')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Role</label>
                        <input type="text" class="form-control" 
                        value="<?= esc(session('role_name') ?? '-') ?>" readonly>

                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary px-4">Simpan</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

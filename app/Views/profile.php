<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="container-fluid px-4">
    <h4 class="fw-bold py-3 mb-4">Profil Saya</h4>

    <div class="card p-4">
        <div class="row align-items-center">
            <div class="col-md-4 text-center border-end">
                <img src="<?= base_url('assets/images/profil/avatarprofile.jpg') ?>" 
                    class="rounded-circle avatar-img mb-3" 
                    alt="Avatar">
                <h5 class="mb-1">Kinaya Nuha Safira</h5>
                <p class="text-muted">Admin</p>
            </div>

            <div class="col-md-8 px-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">Username</label>
                    <div class="form-control bg-light border">kinaya18</div>
                </div>
                    <div class="mb-3">
                    <label class="form-label fw-bold">Full Name</label>
                    <div class="form-control bg-light border">Kinaya Nuha Safira</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Role</label>
                    <div class="form-control bg-light border">Admin</div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-img {
        width: 180px;
        height: 180px;
        object-fit: cover;
    }

    .card {
        background-color: #fff;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
    }

    .form-control {
        border-radius: .375rem;
        pointer-events: none; 
    }
</style>

<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn = document.getElementById('profileToggle');
        const menu = document.getElementById('profileMenu');

        toggleBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
        });

        document.addEventListener('click', function (e) {
            const dropdown = document.getElementById('profileDropdown');
            if (!dropdown.contains(e.target)) {
                menu.style.display = 'none';
            }
        });
    });
</script>
<?= $this->endSection() ?>

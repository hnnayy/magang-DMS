<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<style>
    /* Tetap warna teks label hitam */
    .form-check-label {
        color: black !important;
    }
    .form-check-input:checked + .form-check-label,
    .form-check-input:focus + .form-check-label,
    .form-check-input:hover + .form-check-label {
        color: black !important;
    }

    /* Set warna radio button sesuai gambar */
    .form-check-input {
        background-color: transparent !important;
        border-color: #adb5bd !important; /* Warna unchecked */
    }
    .form-check-input:checked {
        background-color: #007bff !important; /* Warna checked */
        border-color: #007bff !important;
    }
    .form-check-input:focus {
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important; /* Focus ring biru */
    }

    /* Pastikan validasi tidak mengubah warna */
    .form-check-input.is-valid,
    .form-check-input.is-invalid,
    .was-validated .form-check-input:valid,
    .was-validated .form-check-input:invalid {
        background-color: transparent !important;
        border-color: #adb5bd !important;
    }
    .form-check-input:checked.is-valid,
    .form-check-input:checked.is-invalid,
    .was-validated .form-check-input:checked:valid,
    .was-validated .form-check-input:checked:invalid {
        background-color: #007bff !important;
        border-color: #007bff !important;
    }
    .form-check-input:focus.is-valid,
    .form-check-input:focus.is-invalid,
    .was-validated .form-check-input:focus:valid,
    .was-validated .form-check-input:focus:invalid {
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
    }
</style>

<div class="container">
    <div class="form-section">
        <div class="form-section-divider">
            <h2><?= $title ?? 'Tambah Menu' ?></h2>
        </div>
        <?php $validation = $validation ?? \Config\Services::validation(); ?>
        <form id="createMenuForm" method="post" action="<?= base_url('Menu/store') ?>" class="needs-validation" novalidate>
            <?= csrf_field() ?>

            <!-- Nama Menu -->
            <div class="form-group">
                <label class="form-label" for="menuName">Nama Menu</label>
                <input type="text" id="menuName" name="menu_name" class="form-control" placeholder="Tulis Nama Menu disini..." pattern="^[a-zA-Z0-9\s]{1,40}$" title="Nama menu hanya boleh berisi huruf, angka, dan spasi, maksimum 40 karakter" maxlength="40" required>
                <div class="invalid-feedback">Nama menu wajib diisi.</div>
            </div>

            <!-- Icon -->
            <div class="form-group">
                <label class="form-label" for="icon">Icon (contoh: fa-home, menu-icon)</label>
                <input type="text" id="icon" name="icon" class="form-control" placeholder="Tulis nama icon disini..." pattern="^[a-z0-9\-]{1,40}$" title="Icon hanya boleh berisi huruf kecil, angka, dan tanda minus (-), maksimum 40 karakter" maxlength="40" required>
                <div class="invalid-feedback">Icon hanya boleh berisi huruf kecil dan tanda minus (-).</div>
            </div>

            <!-- Status -->
            <div class="form-group">
                <label class="form-label d-block">Status</label>
                <div style="display: flex; gap: 30px;">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="status" id="statusActive" value="1" required checked>
                        <label class="form-check-label" for="statusActive">Aktif</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="status" id="statusInactive" value="0" required>
                        <label class="form-check-label" for="statusInactive">Nonaktif</label>
                    </div>
                </div>
                <div class="invalid-feedback">Silakan pilih status.</div>
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

<script>
(() => {
    'use strict';
    const form = document.getElementById('createMenuForm');

    form.addEventListener('submit', e => {
        // Validasi status
        const statusInputs = form.querySelectorAll('input[name="status"]');
        let isStatusValid = false;

        statusInputs.forEach(input => {
            if (input.checked) isStatusValid = true;

            // Reset event untuk hapus kelas validasi
            input.addEventListener('invalid', function() {
                this.classList.remove('is-valid', 'is-invalid');
            });
            input.addEventListener('change', function() {
                this.classList.remove('is-valid', 'is-invalid');
            });
        });

        if (!form.checkValidity() || !isStatusValid) {
            e.preventDefault();
            e.stopPropagation();
            if (!isStatusValid) {
                const statusGroup = document.querySelector('.form-group:has(#statusActive)');
                let feedback = statusGroup.querySelector('.invalid-feedback');
                if (!feedback) {
                    feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback';
                    feedback.textContent = 'Silakan pilih status.';
                    statusGroup.appendChild(feedback);
                }
                feedback.style.display = 'block';
            }
        }

        // Pastikan radio button tidak mendapatkan kelas is-valid atau is-invalid
        statusInputs.forEach(input => {
            input.classList.remove('is-valid', 'is-invalid');
        });

        // Tambahkan class was-validated ke form
        form.classList.add('was-validated');
    }, false);
})();
</script>

<?= $this->endSection() ?>
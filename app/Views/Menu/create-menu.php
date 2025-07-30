<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="container">
    <div class="form-section">
        <div class="form-section-divider">
            <h2><?= $title ?? 'Add Menu' ?></h2>
        </div>

        <?php $validation = $validation ?? \Config\Services::validation(); ?>

        <form id="createMenuForm" method="post" action="<?= base_url('create-menu/store') ?>" class="needs-validation" novalidate>
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label" for="menuName">Menu Name</label>
                <input type="text" id="menuName" name="menu_name" value="<?= old('menu_name') ?>" 
                       class="form-input" placeholder="Enter menu here..." 
                       pattern="^[a-zA-Z0-9\s]{1,50}$" 
                       title="Nama menu hanya boleh berisi huruf, angka, dan spasi, maksimal 50 karakter" 
                       maxlength="50" required>
                <div class="invalid-feedback">Menu name is mandatory and must be alphanumeric.</div>
            </div>

            <div class="form-group">
                <label class="form-label" for="icon">Icon (example: fa-home, menu-icon)</label>
                <input type="text" id="icon" name="icon" value="<?= old('icon') ?>" 
                       class="form-input" placeholder="Enter icon here..." 
                       pattern="^[a-z0-9\s\-]{1,40}$" 
                       title="Icon hanya boleh berisi huruf kecil, angka, spasi, dan tanda minus (-), maksimal 40 karakter" 
                       maxlength="40" required>
                <div class="invalid-feedback">Icons may only contain lowercase letters, numbers, spaces, and minus signs (-).</div>
            </div>

            <div class="form-group">
                <label class="form-label d-block">Status</label>
                <div style="display: flex; gap: 30px;">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="status" id="statusActive" value="1" required <?= old('status') !== '0' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="statusActive">Active</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="status" id="statusInactive" value="2" required <?= old('status') === '2' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="statusInactive">Inactive</label>
                    </div>
                </div>
                <div class="invalid-feedback">Please select a status.</div>
            </div>

            <button type="submit" class="submit-btn">Submit</button>
        </form>
    </div>

    <div class="illustration-section">
        <img src="<?= base_url('assets/images/profil/Logo_Telkom_University.png') ?>" alt="Menu Illustration" class="illustration-img">
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
(() => {
    'use strict';
    const form = document.getElementById('createMenuForm');

    form.addEventListener('submit', e => {
        const statusInputs = form.querySelectorAll('input[name="status"]');
        let isStatusValid = false;

        statusInputs.forEach(input => {
            if (input.checked) isStatusValid = true;

            input.addEventListener('invalid', function () {
                this.classList.remove('is-valid', 'is-invalid');
            });

            input.addEventListener('change', function () {
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

        statusInputs.forEach(input => {
            input.classList.remove('is-valid', 'is-invalid');
        });

        form.classList.add('was-validated');
    }, false);
})();
</script>

<?= $this->endSection() ?>
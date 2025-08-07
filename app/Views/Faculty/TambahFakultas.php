<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>
<div class="container">
    <div class="form-section">
        <div class="form-section-divider">
            <h2><?= $title ?? 'Create Fakulty' ?></h2>
        </div>
        
        <?php if (session('swal')) : ?>
            <script>
                Swal.fire({
                    icon: '<?= session('swal')['icon'] ?>',
                    title: '<?= session('swal')['title'] ?>',
                    text: '<?= session('swal')['text'] ?>'
                });
            </script>
        <?php endif; ?>
        
        <form id="createFakultasForm" method="post" action="<?= base_url('create-faculty/store') ?>" class="needs-validation" novalidate>
            <?= csrf_field() ?>
            <!-- Nama Fakultas -->
            <div class="form-group">
                <label class="form-label" for="name">Fakulty/Directorate Name</label>
                <input type="text" id="name" name="name" class="form-input"
                       placeholder="Enter faculty here..."
                       value="<?= set_value('name') ?>"
                       required>
                <div class="invalid-feedback">Please enter faculty/directorate name.</div>
            </div>
            <!-- Type (Level) -->
            <div class="form-group" id="type-group">
                <label class="form-label d-block">Level</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="type" id="type1" value="1"
                        <?= set_radio('type', '1', true) ?> required>
                    <label class="form-check-label" for="type1">Directorate</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="type" id="type2" value="2"
                        <?= set_radio('type', '2') ?> required>
                    <label class="form-check-label" for="type2">Faculty</label>
                </div>
                <div class="invalid-feedback">Please select a level.</div>
            </div>
            <!-- Status -->
            <div class="form-group" id="status-group">
                <label class="form-label d-block">Status</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="status1" value="1"
                        <?= set_radio('status', '1', true) ?> required>
                    <label class="form-check-label" for="status1">Active</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="status2" value="2"
                        <?= set_radio('status', '2') ?> required>
                    <label class="form-check-label" for="status2">Inactive</label>
                </div>
                <div class="invalid-feedback">Please select a status.</div>
            </div>
            <button type="submit" class="submit-btn">Submit</button>
        </form>
    </div>
    <div class="illustration-section">
        <img src="<?= base_url('assets/images/profil/Logo_Telkom_University.png') ?>" alt="Fakultas Illustration" class="illustration-img">
    </div>
</div>

<!-- SweetAlert (Notifikasi) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Fungsi untuk kapitalisasi setiap kata
    function capitalizeWords(str) {
        return str.replace(/\b\w/g, function(char) {
            return char.toUpperCase();
        });
    }

    document.getElementById('name').addEventListener('input', function () {
        this.value = capitalizeWords(this.value.toLowerCase());
        
        // Real-time validation untuk nama fakultas
        if (this.value.trim()) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else {
            this.classList.remove('is-valid');
        }
    });

    // Form validation
    (() => {
        'use strict';
        const form = document.getElementById('createFakultasForm');

        form.addEventListener('submit', e => {
            let isValid = form.checkValidity();

            // Validasi untuk radio button Type
            const typeInputs = form.querySelectorAll('input[name="type"]');
            const typeGroup = document.getElementById('type-group');
            const isTypeChecked = Array.from(typeInputs).some(input => input.checked);

            if (!isTypeChecked) {
                isValid = false;
                typeGroup.classList.add('is-invalid');
            } else {
                typeGroup.classList.remove('is-invalid');
            }

            // Validasi untuk radio button Status
            const statusInputs = form.querySelectorAll('input[name="status"]');
            const statusGroup = document.getElementById('status-group');
            const isStatusChecked = Array.from(statusInputs).some(input => input.checked);

            if (!isStatusChecked) {
                isValid = false;
                statusGroup.classList.add('is-invalid');
            } else {
                statusGroup.classList.remove('is-invalid');
            }

            // Validasi untuk nama fakultas
            const nameInput = document.getElementById('name');
            if (!nameInput.value.trim()) {
                isValid = false;
                nameInput.classList.add('is-invalid');
                nameInput.classList.remove('is-valid');
            } else {
                nameInput.classList.remove('is-invalid');
                nameInput.classList.add('is-valid');
            }

            if (!isValid) {
                e.preventDefault();
                e.stopPropagation();
            }

            form.classList.add('was-validated');
        }, false);

        // Real-time validation untuk radio buttons
        document.querySelectorAll('input[name="type"]').forEach(input => {
            input.addEventListener('change', function() {
                const typeGroup = document.getElementById('type-group');
                typeGroup.classList.remove('is-invalid');
            });
        });

        document.querySelectorAll('input[name="status"]').forEach(input => {
            input.addEventListener('change', function() {
                const statusGroup = document.getElementById('status-group');
                statusGroup.classList.remove('is-invalid');
            });
        });
    })();
</script>
<?= $this->endSection() ?>
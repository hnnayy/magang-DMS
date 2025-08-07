<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>
<div class="container">
    <div class="form-section">
        <div class="form-section-divider">
            <h2><?= $title ?? 'Add New Role' ?></h2>
        </div>
        <form id="createRoleForm" method="post" action="<?= base_url('create-role/store') ?>" class="needs-validation" novalidate>
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label" for="nama">Role Name</label>
                <input type="text" id="nama" name="nama" class="form-input"
                       placeholder="Enter role name here..."
                       required>
                <div class="invalid-feedback">Please enter role name.</div>
            </div>
            <div class="form-group" id="level-group">
                <label class="form-label d-block">Level</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="level" id="level1" value="1" required>
                    <label class="form-check-label" for="level1">Directorate / Faculty</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="level" id="level2" value="2" required>
                    <label class="form-check-label" for="level2">Unit</label>
                </div>
                <div class="invalid-feedback">Please select a level.</div>
            </div>
            <div class="form-group">
                <label class="form-label" for="desc">Description</label>
                <textarea id="desc" name="desc" class="form-input"
                          placeholder="Describe this role..."
                          rows="3"
                          required></textarea>
                <div class="invalid-feedback">Please enter description.</div>
            </div>
            <div class="form-group" id="status-group">
                <label class="form-label d-block">Status</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="active" value="active" required>
                    <label class="form-check-label" for="active">Active</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="inactive" value="inactive" required>
                    <label class="form-check-label" for="inactive">Inactive</label>
                </div>
                <div class="invalid-feedback">Please select a status.</div>
            </div>
            <button type="submit" class="submit-btn">Submit</button>
        </form>
    </div>
    <div class="illustration-section">
        <img src="<?= base_url('assets/images/profil/Logo_Telkom_University.png') ?>" alt="Role Illustration" class="illustration-img">
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // SweetAlert untuk error duplikasi
    <?php if (session()->getFlashdata('duplicate_error')): ?>
        Swal.fire({
            title: 'Failed!',
            text: '<?= session()->getFlashdata('duplicate_error') ?>',
            icon: 'error',
            confirmButtonText: 'OK',
            confirmButtonColor: '#6366f1'
        });
    <?php endif; ?>

    // SweetAlert untuk pesan sukses
    <?php if (session()->getFlashdata('added_message')): ?>
        Swal.fire({
            title: 'Success!',
            text: '<?= session()->getFlashdata('added_message') ?>',
            icon: 'success',
            confirmButtonText: 'OK',
            confirmButtonColor: '#10b981'
        });
    <?php endif; ?>

    // SweetAlert untuk error lainnya
    <?php if (session()->getFlashdata('error')): ?>
        Swal.fire({
            title: 'Error!',
            text: '<?= session()->getFlashdata('error') ?>',
            icon: 'error',
            confirmButtonText: 'OK',
            confirmButtonColor: '#ef4444'
        });
    <?php endif; ?>

    function capitalizeWords(str) {
        return str.replace(/\b\w/g, function(char) {
            return char.toUpperCase();
        });
    }
    
    document.getElementById('nama').addEventListener('input', function () {
        const lower = this.value.toLowerCase();
        this.value = capitalizeWords(lower);
        if (this.value.trim()) {
            this.classList.remove('is-invalid');
        }
    });
    
    document.getElementById('desc').addEventListener('input', function() {
        if (this.value.trim()) {
            this.classList.remove('is-invalid');
        }
    });
    
    (() => {
        'use strict';
        const form = document.getElementById('createRoleForm');
        
        form.addEventListener('submit', e => {
            let isValid = form.checkValidity();
            
            const levelInputs = form.querySelectorAll('input[name="level"]');
            const levelGroup = document.getElementById('level-group');
            const isLevelChecked = Array.from(levelInputs).some(input => input.checked);
            
            if (!isLevelChecked) {
                isValid = false;
                levelGroup.classList.add('is-invalid');
            } else {
                levelGroup.classList.remove('is-invalid');
            }
            
            const statusInputs = form.querySelectorAll('input[name="status"]');
            const statusGroup = document.getElementById('status-group');
            const isStatusChecked = Array.from(statusInputs).some(input => input.checked);
            
            if (!isStatusChecked) {
                isValid = false;
                statusGroup.classList.add('is-invalid');
            } else {
                statusGroup.classList.remove('is-invalid');
            }
            
            const namaInput = document.getElementById('nama');
            if (!namaInput.value.trim()) {
                isValid = false;
                namaInput.classList.add('is-invalid');
            } else {
                namaInput.classList.remove('is-invalid');
            }
            
            const descInput = document.getElementById('desc');
            if (!descInput.value.trim()) {
                isValid = false;
                descInput.classList.add('is-invalid');
            } else {
                descInput.classList.remove('is-invalid');
            }
            
            if (!isValid) {
                e.preventDefault();
                e.stopPropagation();
            } else {
                namaInput.value = capitalizeWords(namaInput.value.toLowerCase());
            }
            
            form.classList.add('was-validated');
        }, false);
        
        document.querySelectorAll('input[name="level"]').forEach(input => {
            input.addEventListener('change', function() {
                const levelGroup = document.getElementById('level-group');
                levelGroup.classList.remove('is-invalid');
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
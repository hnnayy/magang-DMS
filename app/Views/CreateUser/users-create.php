<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="container">
    <div class="form-section">
        <div class="form-section-divider">
            <h2><?= esc($title ?? 'Add User') ?></h2>
        </div>

        <form id="createUserForm" method="post" action="<?= base_url('create-user/store') ?>" class="needs-validation" novalidate>
            <?= csrf_field() ?>

            <!-- Faculty / Unit Parent -->
            <div class="form-group">
                <label class="form-label" for="fakultas">Faculty/Directorate</label>
                <select id="fakultas" name="fakultas" class="form-input" required onchange="updateUnitOptions()">
                    <option value="" disabled <?= old('fakultas') ? '' : 'selected' ?>>Select Faculty...</option>
                    <?php foreach ($unitParents as $parent): ?>
                        <option value="<?= $parent['id'] ?>" <?= old('fakultas') == $parent['id'] ? 'selected' : '' ?>>
                            <?= esc($parent['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">Please select a faculty/directorate.</div>
            </div>

            <!-- Unit / Program -->
            <div class="form-group">
                <label class="form-label" for="unit">Division/Unit/Study Program</label>
                <select name="unit" id="unit" class="form-input" required>
                    <option value="" disabled <?= old('unit') ? '' : 'selected' ?>>Select Unit...</option>
                    <?php foreach ($units as $u): ?>
                        <?php if (old('fakultas') == $u['parent_id']): ?>
                            <option value="<?= $u['id'] ?>" <?= old('unit') == $u['id'] ? 'selected' : '' ?>>
                                <?= esc($u['name']) ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">Please select a unit/division.</div>
            </div>

            <!-- Username -->
            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <input type="text" id="username" name="username" class="form-input"
                       placeholder="Enter username..."
                       pattern="^[a-z0-9]+$"
                       title="Username may only contain lowercase letters and numbers"
                       value="<?= old('username') ?>"
                       required autocomplete="off">
                <div class="invalid-feedback">Username may only contain lowercase letters and numbers, without spaces.</div>
            </div>

            <!-- Full Name -->
            <div class="form-group">
                <label class="form-label" for="fullname">Full Name</label>
                <input type="text" id="fullname" name="fullname" class="form-input"
                       placeholder="Enter full name..."
                       pattern="^[A-Za-zÀ-ÿ\s]+$"
                       title="Full name must contain at least two words (letters and spaces only)"
                       value="<?= old('fullname') ?>"
                       required>
                <div class="invalid-feedback">Full Name must consist of at least two words and contain no numbers or special characters.</div>
            </div>

            <!-- Role -->
            <div class="form-group">
                <label class="form-label" for="role">Role</label>
                <select id="role" name="role" class="form-input" required>
                    <option value="" disabled <?= old('role') ? '' : 'selected' ?>>Select Role...</option>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= strtolower($r['name']) ?>" <?= old('role') == strtolower($r['name']) ? 'selected' : '' ?>>
                            <?= esc($r['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">Please select a role.</div>
            </div>

            <!-- Status - Default Active -->
            <div class="form-group" id="status-group">
                <label class="form-label d-block">Status</label>
                <label>
                    <input type="radio" name="status" value="1" required 
                           <?= (old('status') == '1' || !old('status')) ? 'checked' : '' ?>>
                    Active
                </label>
                <label style="margin-left: 15px;">
                    <input type="radio" name="status" value="2" required 
                           <?= old('status') == '2' ? 'checked' : '' ?>>
                    Inactive
                </label>
                <div class="invalid-feedback">Please select a status.</div>
            </div>

            <button type="submit" class="submit-btn">Submit</button>
        </form>
    </div>

    <div class="illustration-section">
        <img src="<?= base_url('assets/images/profil/Logo_Telkom_University.png') ?>" alt="User Illustration" class="illustration-img">
    </div>
</div>

<!-- SweetAlert for Flash Messages -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!--  -->

<!-- JS: Update Unit Options Based on Selected Fakultas -->
<script>
    const allUnits = <?= json_encode($units) ?>;

    function updateUnitOptions() {
        const fakultasSelect = document.getElementById('fakultas');
        const unitSelect = document.getElementById('unit');
        const selectedParentId = fakultasSelect.value;

        // Clear current options
        unitSelect.innerHTML = '<option value="" disabled selected>Select Unit...</option>';

        // Filter and add new options
        const filtered = allUnits.filter(unit => unit.parent_id == selectedParentId);
        filtered.forEach(unit => {
            const option = document.createElement('option');
            option.value = unit.id;
            option.textContent = unit.name;
            unitSelect.appendChild(option);
        });
    }

    // Trigger auto fill if old fakultas exists (on failed submit)
    window.addEventListener('DOMContentLoaded', () => {
        const fakultas = document.getElementById('fakultas').value;
        if (fakultas) {
            updateUnitOptions();
            // Set old unit value if exists
            const oldUnit = "<?= old('unit') ?>";
            if (oldUnit) {
                document.getElementById('unit').value = oldUnit;
            }
        }
    });
</script>

<!-- JS: Form Validation -->
<script>
(() => {
    'use strict';
    const form = document.getElementById('createUserForm');

    form.addEventListener('submit', e => {
        let isValid = form.checkValidity();

        // Additional validation for status (though one should already be checked by default)
        const statusInputs = form.querySelectorAll('input[name="status"]');
        const statusGroup = document.getElementById('status-group');
        const isStatusChecked = Array.from(statusInputs).some(input => input.checked);

        if (!isStatusChecked) {
            isValid = false;
            let feedback = statusGroup.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.style.display = 'block';
            }
        } else {
            let feedback = statusGroup.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.style.display = 'none';
            }
        }

        // Additional validation for fullname (at least two words)
        const fullnameInput = document.getElementById('fullname');
        const fullnameValue = fullnameInput.value.trim();
        const words = fullnameValue.split(/\s+/).filter(word => word.length > 0);
        
        if (fullnameValue && words.length < 2) {
            isValid = false;
            fullnameInput.setCustomValidity('Full name must contain at least two words');
        } else {
            fullnameInput.setCustomValidity('');
        }

        if (!isValid) {
            e.preventDefault();
            e.stopPropagation();
        }

        form.classList.add('was-validated');
    }, false);

    // Real-time validation for fullname
    document.getElementById('fullname').addEventListener('input', function() {
        const value = this.value.trim();
        const words = value.split(/\s+/).filter(word => word.length > 0);
        
        if (value && words.length < 2) {
            this.setCustomValidity('Full name must contain at least two words');
        } else {
            this.setCustomValidity('');
        }
    });
})();
</script>

<?= $this->endSection() ?>
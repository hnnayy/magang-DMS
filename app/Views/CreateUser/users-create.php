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
                <div class="search-dropdown-container">
                    <input type="text" 
                           id="fakultas-search" 
                           class="form-input search-input" 
                           placeholder="Search faculty/directorate..." 
                           autocomplete="off"
                           value="<?= old('fakultas_text') ?>"
                           required>
                    <input type="hidden" 
                           id="fakultas" 
                           name="fakultas" 
                           value="<?= old('fakultas') ?>" 
                           required>
                    <div id="fakultas-dropdown" class="search-dropdown-list" style="display: none;"></div>
                </div>
                <div class="invalid-feedback">Please select a faculty/directorate.</div>
            </div>

            <!-- Unit / Program -->
            <div class="form-group">
                <label class="form-label" for="unit">Division/Unit/Study Program</label>
                <div class="search-dropdown-container">
                    <input type="text" 
                           id="unit-search" 
                           class="form-input search-input" 
                           placeholder="Search unit/division..." 
                           autocomplete="off"
                           value="<?= old('unit_text') ?>"
                           required>
                    <input type="hidden" 
                           id="unit" 
                           name="unit" 
                           value="<?= old('unit') ?>" 
                           required>
                    <div id="unit-dropdown" class="search-dropdown-list" style="display: none;"></div>
                </div>
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
                <div class="search-dropdown-container">
                    <input type="text" 
                           id="role-search" 
                           class="form-input search-input" 
                           placeholder="Search role..." 
                           autocomplete="off"
                           value="<?= old('role_text') ?>"
                           required>
                    <input type="hidden" 
                           id="role" 
                           name="role" 
                           value="<?= old('role') ?>" 
                           required>
                    <div id="role-dropdown" class="search-dropdown-list" style="display: none;"></div>
                </div>
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
        title: 'Failed!',
        text: '<?= session()->getFlashdata('error') ?>',
        confirmButtonText: 'OK'
    });
<?php endif; ?>
</script>

<!-- JS: Searchable Dropdown Implementation -->
<script>
    // Data from PHP
    const allUnits = <?= json_encode($units) ?>;
    const unitParents = <?= json_encode($unitParents) ?>;
    const roles = <?= json_encode($roles) ?>;

    // Searchable Dropdown Class
    class SearchableDropdown {
        constructor(searchInputId, hiddenInputId, dropdownId, data, textKey = 'name', valueKey = 'id') {
            this.searchInput = document.getElementById(searchInputId);
            this.hiddenInput = document.getElementById(hiddenInputId);
            this.dropdown = document.getElementById(dropdownId);
            this.data = data;
            this.textKey = textKey;
            this.valueKey = valueKey;
            this.filteredData = [...data];
            this.selectedIndex = -1;
            this.isDropdownOpen = false;
            
            this.init();
        }

        init() {
            this.searchInput.addEventListener('input', (e) => this.handleInput(e));
            this.searchInput.addEventListener('focus', () => this.showDropdown());
            this.searchInput.addEventListener('keydown', (e) => this.handleKeydown(e));
            this.searchInput.addEventListener('click', () => this.showDropdown());
            
            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.search-dropdown-container')) {
                    this.hideDropdown();
                }
            });

            // Prevent blur from hiding dropdown when clicking dropdown items
            this.dropdown.addEventListener('mousedown', (e) => e.preventDefault());
        }

        handleInput(e) {
            const query = e.target.value.toLowerCase().trim();
            this.filteredData = this.data.filter(item => 
                item[this.textKey].toLowerCase().includes(query)
            );
            this.selectedIndex = this.filteredData.length > 0 ? 0 : -1;
            this.renderDropdown();
            this.showDropdown();
            
            // Auto-select if there's an exact match
            const exactMatch = this.data.find(item => 
                item[this.textKey].toLowerCase() === query
            );
            if (exactMatch) {
                this.selectItem(exactMatch, false);
            } else {
                this.hiddenInput.value = '';
                this.searchInput.classList.remove('has-selection');
                this.showValidationError();
            }
        }

        handleKeydown(e) {
            if (!this.isDropdownOpen) return;

            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    this.selectedIndex = Math.min(this.selectedIndex + 1, this.filteredData.length - 1);
                    this.updateSelection();
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    this.selectedIndex = Math.max(this.selectedIndex - 1, 0);
                    this.updateSelection();
                    break;
                case 'Enter':
                    e.preventDefault();
                    if (this.selectedIndex >= 0 && this.filteredData.length > 0) {
                        this.selectItem(this.filteredData[this.selectedIndex]);
                    } else if (this.filteredData.length === 1) {
                        this.selectItem(this.filteredData[0]);
                    }
                    break;
                case 'Escape':
                    e.preventDefault();
                    this.hideDropdown();
                    break;
                case 'Tab':
                    if (this.isDropdownOpen) {
                        e.preventDefault();
                        if (this.selectedIndex >= 0) {
                            this.selectItem(this.filteredData[this.selectedIndex]);
                        } else if (this.filteredData.length === 1) {
                            this.selectItem(this.filteredData[0]);
                        }
                    }
                    break;
            }
        }

        updateSelection() {
            const items = this.dropdown.querySelectorAll('.search-dropdown-item:not(.no-results)');
            items.forEach((item, index) => {
                item.classList.toggle('selected', index === this.selectedIndex);
                if (index === this.selectedIndex) {
                    item.scrollIntoView({ block: 'nearest' });
                }
            });
        }

        selectItem(item, hideDropdown = true) {
            this.searchInput.value = item[this.textKey];
            this.hiddenInput.value = item[this.valueKey];
            this.searchInput.classList.add('has-selection');
            if (hideDropdown) {
                this.hideDropdown();
            }
            this.hideValidationError();
            this.hiddenInput.dispatchEvent(new Event('change'));
        }

        showDropdown() {
            if (this.filteredData.length > 0 || this.searchInput.value) {
                this.renderDropdown();
                this.dropdown.style.display = 'block';
                this.isDropdownOpen = true;
                this.updateSelection();
            }
        }

        hideDropdown() {
            this.dropdown.style.display = 'none';
            this.isDropdownOpen = false;
            this.selectedIndex = -1;
            if (!this.hiddenInput.value && this.searchInput.value) {
                this.showValidationError();
            }
        }

        renderDropdown() {
            this.dropdown.innerHTML = '';
            
            if (this.filteredData.length === 0) {
                const noResults = document.createElement('div');
                noResults.className = 'search-dropdown-item no-results';
                noResults.textContent = 'No results found';
                this.dropdown.appendChild(noResults);
                return;
            }

            this.filteredData.forEach((item, index) => {
                const div = document.createElement('div');
                div.className = `search-dropdown-item ${index === this.selectedIndex ? 'selected' : ''}`;
                div.textContent = item[this.textKey];
                div.addEventListener('click', () => this.selectItem(item));
                this.dropdown.appendChild(div);
            });
        }

        setValue(value, text) {
            this.hiddenInput.value = value;
            this.searchInput.value = text;
            if (value) {
                this.searchInput.classList.add('has-selection');
                this.hideValidationError();
            }
        }

        showValidationError() {
            this.searchInput.classList.add('is-invalid');
            const container = this.searchInput.closest('.form-group');
            const feedback = container?.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.style.display = 'block';
            }
        }

        hideValidationError() {
            this.searchInput.classList.remove('is-invalid');
            const container = this.searchInput.closest('.form-group');
            const feedback = container?.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.style.display = 'none';
            }
        }
    }

    // Initialize searchable dropdowns
    let fakultasDropdown, unitDropdown, roleDropdown;

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Faculty dropdown
        fakultasDropdown = new SearchableDropdown(
            'fakultas-search', 
            'fakultas', 
            'fakultas-dropdown', 
            unitParents
        );

        // Initialize Unit dropdown
        unitDropdown = new SearchableDropdown(
            'unit-search', 
            'unit', 
            'unit-dropdown', 
            []
        );

        // Initialize Role dropdown
        roleDropdown = new SearchableDropdown(
            'role-search', 
            'role', 
            'role-dropdown', 
            roles.map(r => ({id: r.name.toLowerCase(), name: r.name}))
        );

        // Handle faculty change to update units
        document.getElementById('fakultas').addEventListener('change', function() {
            const selectedParentId = this.value;
            const filteredUnits = allUnits.filter(unit => unit.parent_id == selectedParentId);
            
            unitDropdown.data = filteredUnits;
            unitDropdown.filteredData = [...filteredUnits];
            
            // Clear unit selection
            document.getElementById('unit-search').value = '';
            document.getElementById('unit').value = '';
            document.getElementById('unit-search').classList.remove('has-selection');
            unitDropdown.hideValidationError();
        });

        // Set old values if they exist (for form validation errors)
        const oldFakultas = "<?= old('fakultas') ?>";
        const oldFakultasText = "<?= old('fakultas_text') ?>";
        const oldUnit = "<?= old('unit') ?>";
        const oldUnitText = "<?= old('unit_text') ?>";
        const oldRole = "<?= old('role') ?>";
        const oldRoleText = "<?= old('role_text') ?>";

        if (oldFakultas && oldFakultasText) {
            fakultasDropdown.setValue(oldFakultas, oldFakultasText);
            document.getElementById('fakultas').dispatchEvent(new Event('change'));
        }

        if (oldUnit && oldUnitText) {
            setTimeout(() => {
                unitDropdown.setValue(oldUnit, oldUnitText);
            }, 100);
        }

        if (oldRole && oldRoleText) {
            roleDropdown.setValue(oldRole, oldRoleText);
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

        // Custom validation for dropdown fields
        const dropdownFields = [
            { searchInput: document.getElementById('fakultas-search'), hiddenInput: document.getElementById('fakultas'), dropdown: fakultasDropdown },
            { searchInput: document.getElementById('unit-search'), hiddenInput: document.getElementById('unit'), dropdown: unitDropdown },
            { searchInput: document.getElementById('role-search'), hiddenInput: document.getElementById('role'), dropdown: roleDropdown }
        ];

        dropdownFields.forEach(field => {
            if (!field.hiddenInput.value) {
                isValid = false;
                field.dropdown.showValidationError();
            } else {
                field.dropdown.hideValidationError();
            }
        });

        // Additional validation for status
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
            fullnameInput.set	globalThisValidity('Full name must contain at least two words');
        } else {
            fullnameInput.setCustomValidity('');
        }

        // Store text values for old() function in case of validation errors
        const fakultasText = document.getElementById('fakultas-search').value;
        const unitText = document.getElementById('unit-search').value;
        const roleText = document.getElementById('role-search').value;
        
        if (fakultasText) {
            const hiddenFakultasText = document.createElement('input');
            hiddenFakultasText.type = 'hidden';
            hiddenFakultasText.name = 'fakultas_text';
            hiddenFakultasText.value = fakultasText;
            form.appendChild(hiddenFakultasText);
        }
        
        if (unitText) {
            const hiddenUnitText = document.createElement('input');
            hiddenUnitText.type = 'hidden';
            hiddenUnitText.name = 'unit_text';
            hiddenUnitText.value = unitText;
            form.appendChild(hiddenUnitText);
        }
        
        if (roleText) {
            const hiddenRoleText = document.createElement('input');
            hiddenRoleText.type = 'hidden';
            hiddenRoleText.name = 'role_text';
            hiddenRoleText.value = roleText;
            form.appendChild(hiddenRoleText);
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
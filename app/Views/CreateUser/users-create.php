<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="container">
    <div class="form-section">
        <div class="form-section-divider">
            <h2><?= esc($title ?? 'Add User') ?></h2>
        </div>

        <form id="createUserForm" method="post" action="<?= base_url('create-user/store') ?>" class="needs-validation" novalidate>
            <?= csrf_field() ?>

            <!-- Faculty / Unit Parent with Search -->
            <div class="form-group">
                <label class="form-label" for="fakultas">Faculty/Directorate</label>
                <div class="search-dropdown-container">
                    <input type="text" id="fakultas-search" class="form-input search-input" 
                           placeholder="Search faculty..." autocomplete="off">
                    <select id="fakultas" name="fakultas" class="form-input" required onchange="updateUnitOptions()" style="display: none;">
                        <option value="" disabled <?= old('fakultas') ? '' : 'selected' ?>>Select Faculty...</option>
                        <?php foreach ($unitParents as $parent): ?>
                            <option value="<?= $parent['id'] ?>" <?= old('fakultas') == $parent['id'] ? 'selected' : '' ?>>
                                <?= esc($parent['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="fakultas-dropdown" class="search-dropdown-list" style="display: none;">
                        <!-- Options will be populated here -->
                    </div>
                </div>
                <div class="invalid-feedback">Please select a faculty/directorate.</div>
            </div>

            <!-- Unit / Program with Search -->
            <div class="form-group">
                <label class="form-label" for="unit">Division/Unit/Study Program</label>
                <div class="search-dropdown-container">
                    <input type="text" id="unit-search" class="form-input search-input" 
                           placeholder="Search unit..." autocomplete="off">
                    <select name="unit" id="unit" class="form-input" required style="display: none;">
                        <option value="" disabled <?= old('unit') ? '' : 'selected' ?>>Select Unit...</option>
                        <?php foreach ($units as $u): ?>
                            <?php if (old('fakultas') == $u['parent_id']): ?>
                                <option value="<?= $u['id'] ?>" <?= old('unit') == $u['id'] ? 'selected' : '' ?>>
                                    <?= esc($u['name']) ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <div id="unit-dropdown" class="search-dropdown-list" style="display: none;">
                        <!-- Options will be populated here -->
                    </div>
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

            <!-- Role with Search -->
            <div class="form-group">
                <label class="form-label" for="role">Role</label>
                <div class="search-dropdown-container">
                    <input type="text" id="role-search" class="form-input search-input" 
                           placeholder="Search role..." autocomplete="off">
                    <select id="role" name="role" class="form-input" required style="display: none;">
                        <option value="" disabled <?= old('role') ? '' : 'selected' ?>>Select Role...</option>
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= strtolower($r['name']) ?>" <?= old('role') == strtolower($r['name']) ? 'selected' : '' ?>>
                                <?= esc($r['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="role-dropdown" class="search-dropdown-list" style="display: none;">
                        <!-- Options will be populated here -->
                    </div>
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

<!-- JS: Initialize Custom Search Dropdowns -->
<script>
$(document).ready(function() {
    // Custom search functionality for Faculty
    const fakultasData = <?= json_encode($unitParents) ?>;
    const fakultasSearch = $('#fakultas-search');
    const fakultasSelect = $('#fakultas');
    const fakultasDropdown = $('#fakultas-dropdown');
    let selectedFakultasId = null;

    // Custom search functionality for Role
    const roleData = <?= json_encode($roles) ?>;
    const roleSearch = $('#role-search');
    const roleSelect = $('#role');
    const roleDropdown = $('#role-dropdown');
    let selectedRoleId = null;

    // Custom search functionality for Unit
    const allUnits = <?= json_encode($units) ?>;
    const unitSearch = $('#unit-search');
    const unitSelect = $('#unit');
    const unitDropdown = $('#unit-dropdown');
    let selectedUnitId = null;
    let currentUnitData = [];

    // Generic function to populate dropdown
    function populateDropdown(dropdown, data, nameField = 'name', useNameAsId = false) {
        dropdown.empty();
        
        if (data.length === 0) {
            dropdown.append('<div class="search-dropdown-item no-results">No results found</div>');
        } else {
            data.forEach(item => {
                const id = useNameAsId ? item[nameField] : (item.id || item[nameField]);
                const div = $('<div class="search-dropdown-item" data-id="' + id + '">' + item[nameField] + '</div>');
                dropdown.append(div);
            });
        }
    }

    // Initialize Faculty dropdown
    populateDropdown(fakultasDropdown, fakultasData);

    // Initialize Role dropdown
    populateDropdown(roleDropdown, roleData, 'name', true);

    // Initialize Unit dropdown (empty initially)
    populateDropdown(unitDropdown, []);

    // Handle Faculty search input
    fakultasSearch.on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        const filteredData = fakultasData.filter(item => 
            item.name.toLowerCase().includes(searchTerm)
        );
        populateDropdown(fakultasDropdown, filteredData);
        
        if (searchTerm && !fakultasDropdown.is(':visible')) {
            fakultasDropdown.show();
        }
    });

    // Handle Role search input
    roleSearch.on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        const filteredData = roleData.filter(item => 
            item.name.toLowerCase().includes(searchTerm)
        );
        populateDropdown(roleDropdown, filteredData, 'name', true);
        
        if (searchTerm && !roleDropdown.is(':visible')) {
            roleDropdown.show();
        }
    });

    // Handle Unit search input
    unitSearch.on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        const filteredData = currentUnitData.filter(item => 
            item.name.toLowerCase().includes(searchTerm)
        );
        populateDropdown(unitDropdown, filteredData);
        
        if (searchTerm && !unitDropdown.is(':visible')) {
            unitDropdown.show();
        }
    });

    // Handle focus events
    fakultasSearch.on('focus', function() {
        fakultasDropdown.show();
    });

    roleSearch.on('focus', function() {
        roleDropdown.show();
    });

    unitSearch.on('focus', function() {
        if (currentUnitData.length > 0) {
            unitDropdown.show();
        }
    });

    // Handle click outside to close dropdowns
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.search-dropdown-container').length) {
            $('.search-dropdown-list').hide();
        }
    });

    // Handle Faculty item selection
    fakultasDropdown.on('click', '.search-dropdown-item:not(.no-results)', function() {
        const selectedId = $(this).data('id');
        const selectedText = $(this).text();
        
        selectedFakultasId = selectedId;
        fakultasSearch.val(selectedText).addClass('has-selection');
        fakultasSelect.val(selectedId);
        fakultasDropdown.hide();
        
        // Clear unit selection when faculty changes
        unitSearch.val('').removeClass('has-selection');
        unitSelect.val('');
        selectedUnitId = null;
        
        // Update unit options
        updateUnitOptions();
    });

    // Handle Role item selection
    roleDropdown.on('click', '.search-dropdown-item:not(.no-results)', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const selectedText = $(this).text().trim();
        const selectedItem = roleData.find(item => item.name === selectedText);
        
        if (selectedItem) {
            const selectedValue = selectedItem.name.toLowerCase();
            selectedRoleId = selectedValue;
            roleSearch.val(selectedText).addClass('has-selection');
            roleSelect.val(selectedValue);
            roleDropdown.hide();
        }
    });

    // Handle Unit item selection
    unitDropdown.on('click', '.search-dropdown-item:not(.no-results)', function() {
        const selectedId = $(this).data('id');
        const selectedText = $(this).text();
        
        selectedUnitId = selectedId;
        unitSearch.val(selectedText).addClass('has-selection');
        unitSelect.val(selectedId);
        unitDropdown.hide();
    });

    // Function to update unit options
    function updateUnitOptions() {
        const selectedParentId = fakultasSelect.val();
        
        if (selectedParentId) {
            // Filter units based on selected faculty
            currentUnitData = allUnits.filter(unit => unit.parent_id == selectedParentId);
            populateDropdown(unitDropdown, currentUnitData);
            
            // Enable unit search
            unitSearch.prop('disabled', false).attr('placeholder', 'Search unit...');
        } else {
            // Clear units if no faculty selected
            currentUnitData = [];
            populateDropdown(unitDropdown, []);
            unitSearch.prop('disabled', true).attr('placeholder', 'Select faculty first...');
        }
    }

    // Set initial values if old values exist
    const oldFakultas = "<?= old('fakultas') ?>";
    if (oldFakultas) {
        const selectedItem = fakultasData.find(item => item.id == oldFakultas);
        if (selectedItem) {
            selectedFakultasId = selectedItem.id;
            fakultasSearch.val(selectedItem.name).addClass('has-selection');
            fakultasSelect.val(selectedItem.id);
            updateUnitOptions();
        }
    }

    const oldRole = "<?= old('role') ?>";
    if (oldRole) {
        const selectedItem = roleData.find(item => item.name.toLowerCase() == oldRole);
        if (selectedItem) {
            selectedRoleId = selectedItem.name.toLowerCase();
            roleSearch.val(selectedItem.name).addClass('has-selection');
            roleSelect.val(selectedItem.name.toLowerCase());
        }
    }

    const oldUnit = "<?= old('unit') ?>";
    if (oldUnit && oldFakultas) {
        // Wait for unit options to be updated first
        setTimeout(() => {
            const selectedItem = currentUnitData.find(item => item.id == oldUnit);
            if (selectedItem) {
                selectedUnitId = selectedItem.id;
                unitSearch.val(selectedItem.name).addClass('has-selection');
                unitSelect.val(selectedItem.id);
            }
        }, 100);
    }

    // Initial state for unit search
    if (!oldFakultas) {
        unitSearch.prop('disabled', true).attr('placeholder', 'Select faculty first...');
    }

    // Make updateUnitOptions globally accessible
    window.updateUnitOptions = updateUnitOptions;
});
</script>

<!-- JS: Form Validation -->
<script>
(() => {
    'use strict';
    const form = document.getElementById('createUserForm');

    form.addEventListener('submit', e => {
        let isValid = form.checkValidity();

        // Validate custom search dropdowns
        const fakultasSearch = $('#fakultas-search');
        const fakultasSelect = $('#fakultas');
        const roleSearch = $('#role-search');
        const roleSelect = $('#role');
        const unitSearch = $('#unit-search');
        const unitSelect = $('#unit');
        
        if (fakultasSelect.prop('required') && !fakultasSelect.val()) {
            isValid = false;
            fakultasSearch.addClass('is-invalid');
        } else {
            fakultasSearch.removeClass('is-invalid').addClass('is-valid');
        }

        if (roleSelect.prop('required') && !roleSelect.val()) {
            isValid = false;
            roleSearch.addClass('is-invalid');
        } else {
            roleSearch.removeClass('is-invalid').addClass('is-valid');
        }

        if (unitSelect.prop('required') && !unitSelect.val()) {
            isValid = false;
            unitSearch.addClass('is-invalid');
        } else {
            unitSearch.removeClass('is-invalid').addClass('is-valid');
        }

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
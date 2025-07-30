<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="container">
    <div class="form-section">
        <h1 class="form-title">Create Unit</h1>
        <hr>

        <?php if (session('swal')) : ?>
            <script>
                Swal.fire({
                    icon: '<?= session('swal')['icon'] ?>',
                    title: '<?= session('swal')['title'] ?>',
                    text: '<?= session('swal')['text'] ?>'
                });
            </script>
        <?php endif; ?>
        
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <form id="addDocumentForm" action="<?= site_url('create-unit/store') ?>" method="post" class="needs-validation" novalidate>
            <?= csrf_field() ?>

            <!-- Faculty/Directorate with Search -->
            <div class="form-group">
                <label class="form-label" for="fakultas-direktorat">Faculty/Directorate</label>
                <div class="search-dropdown-container">
                    <input type="text" id="fakultas-search" class="form-input search-input" 
                           placeholder="Search faculty/directorate..." autocomplete="off" required>
                    <select id="fakultas-direktorat" name="parent_id" class="form-input" required style="display: none;">
                        <option value="">-- Select Faculty/Directorate --</option>
                        <?php foreach ($fakultas as $f) : ?>
                            <option value="<?= $f['id'] ?>" <?= set_select('parent_id', $f['id']) ?>>
                                <?= esc($f['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="fakultas-dropdown" class="search-dropdown-list" style="display: none;">
                        <!-- Options will be populated here -->
                    </div>
                </div>
                <div class="invalid-feedback">Please select a faculty/directorate.</div>
            </div>

            <div class="form-group">
                <label class="form-label" for="unit">Unit</label>
                <input type="text"
                       id="unit"
                       name="unit_name"
                       class="form-input"
                       placeholder="Enter Unit here..."
                       value="<?= set_value('unit_name') ?>"
                       required>
                <div class="invalid-feedback">Please enter unit name.</div>
            </div>

            <div class="form-group" id="status-group">
                <label class="form-label d-block">Status</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="statusActive" value="1" 
                           <?= set_radio('status', '1', true) ?> required>
                    <label class="form-check-label" for="statusActive">Active</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="statusInactive" value="2" 
                           <?= set_radio('status', '2') ?> required>
                    <label class="form-check-label" for="statusInactive">Inactive</label>
                </div>
                <div class="invalid-feedback">Please select a status.</div>
            </div>

            <button type="submit" class="submit-btn">Submit</button>
        </form>
    </div>

    <div class="illustration-section">
        <img src="<?= base_url('assets/images/profil/Logo_Telkom_University.png') ?>"
             alt="User Illustration"
             class="illustration-img">
    </div>
</div>

<!-- JS: Initialize Custom Search Dropdown -->
<script>
$(document).ready(function() {
    // Custom search functionality for Faculty
    const fakultasData = <?= json_encode($fakultas) ?>;
    const fakultasSearch = $('#fakultas-search');
    const fakultasSelect = $('#fakultas-direktorat');
    const fakultasDropdown = $('#fakultas-dropdown');
    let selectedFakultasId = null;

    // Generic function to populate dropdown
    function populateDropdown(dropdown, data, nameField = 'name') {
        dropdown.empty();
        
        if (data.length === 0) {
            dropdown.append('<div class="search-dropdown-item no-results">No results found</div>');
        } else {
            data.forEach(item => {
                const div = $('<div class="search-dropdown-item" data-id="' + item.id + '">' + item[nameField] + '</div>');
                dropdown.append(div);
            });
        }
    }

    // Initialize Faculty dropdown
    populateDropdown(fakultasDropdown, fakultasData);

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

        // Real-time validation - remove invalid state if user starts typing
        if (searchTerm.length > 0) {
            $(this).removeClass('is-invalid');
        }
    });

    // Handle focus event
    fakultasSearch.on('focus', function() {
        fakultasDropdown.show();
    });

    // Handle click outside to close dropdown
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
        
        // Remove validation error if exists
        fakultasSearch.removeClass('is-invalid').addClass('is-valid');
    });

    // Set initial value if set_select value exists
    const selectedValue = fakultasSelect.val();
    if (selectedValue) {
        const selectedItem = fakultasData.find(item => item.id == selectedValue);
        if (selectedItem) {
            selectedFakultasId = selectedItem.id;
            fakultasSearch.val(selectedItem.name).addClass('has-selection');
        }
    }

    // Clear selection when search input is cleared manually
    fakultasSearch.on('keyup', function() {
        if ($(this).val() === '') {
            $(this).removeClass('has-selection is-valid is-invalid');
            fakultasSelect.val('');
            selectedFakultasId = null;
        }
        
        // Trigger validation check if form was already validated
        if ($('#addDocumentForm').hasClass('was-validated')) {
            const fakultasSelect = $('#fakultas-direktorat');
            if (!fakultasSelect.val() && $(this).val().trim() === '') {
                $(this).addClass('is-invalid').removeClass('is-valid');
            }
        }
    });
});
</script>

<!-- JS: Form Validation -->
<script>
(() => {
    'use strict';
    const form = document.getElementById('addDocumentForm');

    form.addEventListener('submit', e => {
        let isValid = form.checkValidity();

        // Validate custom search dropdown
        const fakultasSearch = $('#fakultas-search');
        const fakultasSelect = $('#fakultas-direktorat');
        
        if (fakultasSelect.prop('required') && !fakultasSelect.val()) {
            isValid = false;
            fakultasSearch.addClass('is-invalid').removeClass('is-valid');
            // Manually show invalid feedback
            fakultasSearch.closest('.form-group').find('.invalid-feedback').show();
        } else {
            fakultasSearch.removeClass('is-invalid').addClass('is-valid');
            fakultasSearch.closest('.form-group').find('.invalid-feedback').hide();
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

        if (!isValid) {
            e.preventDefault();
            e.stopPropagation();
        }

        form.classList.add('was-validated');
    }, false);

    // Real-time validation for unit name
    document.getElementById('unit').addEventListener('input', function() {
        if (this.value.trim()) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else {
            this.classList.remove('is-valid');
        }
    });

    // Real-time validation for faculty search
    $('#fakultas-search').on('input', function() {
        const fakultasSelect = $('#fakultas-direktorat');
        if (fakultasSelect.val()) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        } else if ($(this).val().trim() === '') {
            $(this).removeClass('is-valid is-invalid');
        }
    });

    // Additional validation when faculty search loses focus
    $('#fakultas-search').on('blur', function() {
        const fakultasSelect = $('#fakultas-direktorat');
        
        // Only validate if form was already validated (after first submit attempt)
        if ($('#addDocumentForm').hasClass('was-validated')) {
            if ($(this).val().trim() !== '' && !fakultasSelect.val()) {
                // User typed something but didn't select from dropdown
                $(this).addClass('is-invalid').removeClass('is-valid');
            } else if ($(this).val().trim() === '' && fakultasSelect.prop('required')) {
                // Field is empty but required
                $(this).addClass('is-invalid').removeClass('is-valid');
            }
        }
    });
})();
</script>

<?= $this->endSection() ?>
<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<!-- Custom CSS untuk privilege form -->
<link rel="stylesheet" href="<?= base_url('assets/css/privilege.css') ?>" />

<div class="privilege-container">
    <h2 class="form-title">Create Privilege</h2>

    <div class="form-content">
        <form method="post" id="privilegeForm" class="needs-validation" novalidate>
            <!-- CSRF Token -->
            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" id="csrf-token">

            <!-- Role -->
            <div class="form-group">
                <label class="form-label" for="role">Role</label>
                <div class="search-dropdown-container">
                    <input type="text" 
                           id="role-search" 
                           class="form-input search-input" 
                           placeholder="Search role..." 
                           autocomplete="off"
                           required>
                    <input type="hidden" 
                           id="role" 
                           name="role" 
                           required>
                    <div id="role-dropdown" class="search-dropdown-list" style="display: none;"></div>
                </div>
                <div class="invalid-feedback">Please select a role.</div>
            </div>

            <!-- Submenu -->
            <div class="form-group">
                <label class="form-label" for="submenu">Submenu</label>
                <div class="search-dropdown-container">
                    <input type="text" 
                           id="submenu-search" 
                           class="form-input search-input" 
                           placeholder="Search submenu..." 
                           autocomplete="off"
                           required>
                    <div id="submenu-dropdown" class="search-dropdown-list" style="display: none;"></div>
                </div>
                <div id="selected-submenus" class="selected-items-display" style="margin-top: 10px;"></div>
                <div id="submenu-hidden-inputs"></div>
                <div class="invalid-feedback">Please select at least one submenu.</div>
            </div>

            <!-- Privileges -->
            <div class="form-group">
                <label for="privileges" class="form-label">Privileges</label>
                <div class="privileges-options">
                    <label><input type="checkbox" name="privileges[]" value="create"> Create</label>
                    <label><input type="checkbox" name="privileges[]" value="update"> Update</label>
                    <label><input type="checkbox" name="privileges[]" value="delete"> Delete</label>
                    <label><input type="checkbox" name="privileges[]" value="approve"> Approve</label>
                </div>
            </div>

            <!-- Tombol -->
            <div class="form-actions text-center">
                <button type="submit" class="btn btn-primary w-100">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(function () {
    // Data dari PHP - pastikan format sesuai dengan controller
    const roles = <?= json_encode($roles) ?>;
    const submenus = <?= json_encode(array_map(function($s) { 
        return ['id' => $s['id'], 'name' => $s['menu_name'] . ' > ' . $s['name']]; 
    }, $submenus)) ?>;

    console.log('Roles data:', roles);
    console.log('Submenus data:', submenus);

    // Searchable Dropdown Class
    class SearchableDropdown {
        constructor(searchInputId, hiddenInputId, dropdownId, data, options = {}) {
            this.searchInput = document.getElementById(searchInputId);
            this.hiddenInput = document.getElementById(hiddenInputId);
            this.dropdown = document.getElementById(dropdownId);
            this.data = data;
            this.filteredData = [...data];
            this.selectedIndex = -1;
            this.isMultiple = options.multiple || false;
            this.selectedItems = [];
            
            this.init();
        }

        init() {
            this.searchInput.addEventListener('input', (e) => this.handleInput(e));
            this.searchInput.addEventListener('focus', () => this.showDropdown());
            this.searchInput.addEventListener('blur', (e) => this.handleBlur(e));
            this.searchInput.addEventListener('keydown', (e) => this.handleKeydown(e));
            
            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.search-dropdown-container')) {
                    this.hideDropdown();
                }
            });
        }

        handleInput(e) {
            const query = e.target.value.toLowerCase();
            this.filteredData = this.data.filter(item => 
                item.name.toLowerCase().includes(query)
            );
            this.selectedIndex = -1;
            this.renderDropdown();
            this.showDropdown();
            
            if (!this.isMultiple) {
                // For single select, clear hidden input if text doesn't match
                const exactMatch = this.data.find(item => 
                    item.name.toLowerCase() === query.toLowerCase()
                );
                if (!exactMatch) {
                    this.hiddenInput.value = '';
                }
            }
        }

        handleBlur(e) {
            setTimeout(() => {
                if (!this.dropdown.contains(document.activeElement)) {
                    this.hideDropdown();
                    if (!this.isMultiple) {
                        // Restore original value if no valid selection
                        const currentValue = this.hiddenInput.value;
                        if (currentValue) {
                            const selectedItem = this.data.find(item => item.id == currentValue);
                            if (selectedItem) {
                                this.searchInput.value = selectedItem.name;
                            }
                        } else {
                            this.searchInput.value = '';
                        }
                    }
                }
            }, 150);
        }

        handleKeydown(e) {
            if (!this.dropdown.style.display || this.dropdown.style.display === 'none') {
                return;
            }

            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    this.selectedIndex = Math.min(this.selectedIndex + 1, this.filteredData.length - 1);
                    this.updateSelection();
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
                    this.updateSelection();
                    break;
                case 'Enter':
                    e.preventDefault();
                    if (this.selectedIndex >= 0) {
                        this.selectItem(this.filteredData[this.selectedIndex]);
                    }
                    break;
                case 'Escape':
                    this.hideDropdown();
                    break;
            }
        }

        updateSelection() {
            const items = this.dropdown.querySelectorAll('.search-dropdown-item:not(.no-results)');
            items.forEach((item, index) => {
                item.classList.toggle('selected', index === this.selectedIndex);
            });
        }

        selectItem(item) {
            if (this.isMultiple) {
                this.selectMultipleItem(item);
            } else {
                this.selectSingleItem(item);
            }
        }

        selectSingleItem(item) {
            this.searchInput.value = item.name;
            this.hiddenInput.value = item.id;
            this.hideDropdown();
            
            // Remove validation error if exists
            this.searchInput.classList.remove('is-invalid');
        }

        selectMultipleItem(item) {
            // Check if item is already selected
            const existingIndex = this.selectedItems.findIndex(selected => selected.id === item.id);
            
            if (existingIndex === -1) {
                // Add item
                this.selectedItems.push(item);
            } else {
                // Remove item
                this.selectedItems.splice(existingIndex, 1);
            }
            
            this.updateMultipleDisplay();
            this.searchInput.value = ''; // Clear search after selection
            this.filteredData = [...this.data]; // Reset filtered data
            this.renderDropdown();
            
            // Remove validation error if exists
            this.searchInput.classList.remove('is-invalid');
        }

        updateMultipleDisplay() {
            const displayContainer = document.getElementById('selected-submenus');
            const hiddenInputsContainer = document.getElementById('submenu-hidden-inputs');
            
            // Clear previous
            displayContainer.innerHTML = '';
            hiddenInputsContainer.innerHTML = '';
            
            // Create display tags and individual hidden inputs for form submission
            this.selectedItems.forEach(item => {
                // Create display tag
                const tag = document.createElement('div');
                tag.className = 'selected-item-tag';
                tag.innerHTML = `
                    <span>${item.name}</span>
                    <button type="button" class="remove-btn" data-id="${item.id}">×</button>
                `;
                displayContainer.appendChild(tag);
                
                // Create individual hidden input for each selected submenu
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'submenu[]';
                hiddenInput.value = item.id;
                hiddenInputsContainer.appendChild(hiddenInput);
            });
            
            console.log('Updated multiple display. Selected items:', this.selectedItems.length);
            console.log('Hidden inputs created:', hiddenInputsContainer.children.length);
            
            // Bind remove buttons (use event delegation to avoid duplicate listeners)
            displayContainer.removeEventListener('click', this.removeHandler);
            this.removeHandler = (e) => {
                if (e.target.classList.contains('remove-btn')) {
                    const itemId = e.target.dataset.id;
                    this.removeItem(itemId);
                }
            };
            displayContainer.addEventListener('click', this.removeHandler);
        }

        removeItem(itemId) {
            this.selectedItems = this.selectedItems.filter(item => item.id != itemId);
            this.updateMultipleDisplay();
        }

        showDropdown() {
            this.renderDropdown();
            this.dropdown.style.display = 'block';
        }

        hideDropdown() {
            this.dropdown.style.display = 'none';
            this.selectedIndex = -1;
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
                div.className = 'search-dropdown-item';
                
                if (this.isMultiple) {
                    // Show checkmark for selected items
                    const isSelected = this.selectedItems.some(selected => selected.id === item.id);
                    div.innerHTML = `
                        <span style="margin-right: 8px;">${isSelected ? '✓' : ''}</span>
                        ${item.name}
                    `;
                    if (isSelected) {
                        div.style.backgroundColor = '#e6f3ff'; /* Warna biru muda untuk item yang dipilih */
                        div.style.color = '#2c5282'; /* Warna teks lebih gelap agar kontras */
                    }
                } else {
                    div.textContent = item.name;
                }
                
                div.addEventListener('click', () => this.selectItem(item));
                this.dropdown.appendChild(div);
            });
        }

        reset() {
            this.searchInput.value = '';
            if (this.hiddenInput) {
                this.hiddenInput.value = '';
            }
            this.selectedItems = [];
            if (this.isMultiple) {
                this.updateMultipleDisplay();
            }
            this.hideDropdown();
            this.searchInput.classList.remove('is-invalid');
        }
    }

    // Initialize dropdowns
    const roleDropdown = new SearchableDropdown(
        'role-search', 
        'role', 
        'role-dropdown', 
        roles
    );

    const submenuDropdown = new SearchableDropdown(
        'submenu-search', 
        null, // No single hidden input needed for multiple
        'submenu-dropdown', 
        submenus,
        { multiple: true }
    );

    // Form submission
    $('#privilegeForm').on('submit', function (e) {
        e.preventDefault();
        const form = this;
        let isValid = true;
        
        // Clear previous validation
        $(form).removeClass('was-validated');
        $('.form-input').removeClass('is-invalid');

        // Validate role
        const roleValue = $('#role').val();
        if (!roleValue) {
            $('#role-search').addClass('is-invalid');
            isValid = false;
            console.log('Role validation failed - no role selected');
        } else {
            $('#role-search').removeClass('is-invalid');
            console.log('Role selected:', roleValue);
        }

        // Validate submenu - check hidden inputs
        const submenuInputs = $('#submenu-hidden-inputs input[name="submenu[]"]');
        if (submenuInputs.length === 0) {
            $('#submenu-search').addClass('is-invalid');
            isValid = false;
            console.log('Submenu validation failed - no submenus selected');
        } else {
            $('#submenu-search').removeClass('is-invalid');
            console.log('Submenus selected:', submenuInputs.length);
        }

        console.log('Form validation result:', isValid);

        if (isValid) {
            // Show loading
            const submitBtn = $(form).find('button[type="submit"]');
            const originalText = submitBtn.text();
            submitBtn.text('Saving...').prop('disabled', true);

            // Use jQuery serialize for proper form data handling
            const formData = $(form).serialize();
            console.log('Form data being sent:', formData);

            $.ajax({
                url: '<?= base_url('create-privilege/store') ?>',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    console.log('Success response:', response);
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message || 'Privilege has been successfully saved.',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Reset form
                        form.reset();
                        roleDropdown.reset();
                        submenuDropdown.reset();
                        $('.form-input').removeClass('is-invalid');
                    });
                },
                error: function(xhr, status, error) {
                    console.log('Error response:', xhr);
                    console.log('Status:', status);
                    console.log('Error:', error);
                    
                    let errorMessage = 'Failed to save privilege';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    } else if (xhr.responseText) {
                        try {
                            const errorResponse = JSON.parse(xhr.responseText);
                            errorMessage = errorResponse.error || errorMessage;
                        } catch (e) {
                            console.log('Could not parse error response');
                        }
                    }
                    
                    Swal.fire({ 
                        icon: 'error', 
                        title: 'Error', 
                        text: errorMessage 
                    });
                },
                complete: function() {
                    // Restore button
                    submitBtn.text(originalText).prop('disabled', false);
                }
            });
        }
    });
});
</script>

<?= $this->endSection() ?>
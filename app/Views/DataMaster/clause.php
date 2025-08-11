<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<div class="container-fluid clause-container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Clause Management</h4>
        <div class="d-flex gap-3 align-items-center">
            <div class="clause-search-container">
                <i class="fas fa-search clause-search-icon"></i>
                <input type="text" class="form-control" id="clauseSearchInput" placeholder="Search clauses...">
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#clauseAddModal">
                <i class="fas fa-plus"></i> Add Clause
            </button>
        </div>
    </div>

    <!-- ISO/IEC 20000-1:2011 Category -->
    <div class="clause-standard-section" data-standard="ISO/IEC 20000-1:2011">
        <h5 class="clause-standard-title">ISO/IEC 20000-1:2011</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle clause-table">
                <thead class="table-light">
                    <tr>
                        <th style="width: 80px;">No</th>
                        <th>Clause</th>
                        <th>Description</th>
                        <th style="width: 130px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center">1</td>
                        <td>
                            <span class="clause-code">4.1 Management responsibility</span>
                        </td>
                        <td>Understanding the organization and its context</td>
                        <td class="text-center">
                            <div class="clause-action-buttons">
                                <button class="clause-edit-btn" 
                                        onclick="editClauseDocument(1, 'ISO/IEC 20000-1:2011', '4.1 Management responsibility', 'Understanding the organization and its context', 1)"
                                        title="Edit">
                                    <i class="bi bi-pencil-square fs-5"></i>
                                </button>
                                <button class="clause-delete-btn" 
                                        onclick="confirmClauseDelete(this)"
                                        title="Delete">
                                    <i class="bi bi-trash fs-5"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-center">2</td>
                        <td>
                            <span class="clause-code">4.1.1 Management commitment</span>
                        </td>
                        <td>Understanding the needs and expectations of interested parties</td>
                        <td class="text-center">
                            <div class="clause-action-buttons">
                                <button class="clause-edit-btn" 
                                        onclick="editClauseDocument(2, 'ISO/IEC 20000-1:2011', '4.1.1 Management commitment', 'Understanding the needs and expectations of interested parties', 1)"
                                        title="Edit">
                                    <i class="bi bi-pencil-square fs-5"></i>
                                </button>
                                <button class="clause-delete-btn" 
                                        onclick="confirmClauseDelete(this)"
                                        title="Delete">
                                    <i class="bi bi-trash fs-5"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-center">3</td>
                        <td>
                            <span class="clause-code">4.1.2 Service management policy</span>
                        </td>
                        <td>Determining the scope of the quality management system</td>
                        <td class="text-center">
                            <div class="clause-action-buttons">
                                <button class="clause-edit-btn" 
                                        onclick="editClauseDocument(3, 'ISO/IEC 20000-1:2011', '4.1.2 Service management policy', 'Determining the scope of the quality management system', 1)"
                                        title="Edit">
                                    <i class="bi bi-pencil-square fs-5"></i>
                                </button>
                                <button class="clause-delete-btn" 
                                        onclick="confirmClauseDelete(this)"
                                        title="Delete">
                                    <i class="bi bi-trash fs-5"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-center">4</td>
                        <td>
                            <span class="clause-code">4.1.3 Authority, responsibility and communication</span>
                        </td>
                        <td>Leadership and commitment</td>
                        <td class="text-center">
                            <div class="clause-action-buttons">
                                <button class="clause-edit-btn" 
                                        onclick="editClauseDocument(4, 'ISO/IEC 20000-1:2011', '4.1.3 Authority, responsibility and communication', 'Leadership and commitment', 1)"
                                        title="Edit">
                                    <i class="bi bi-pencil-square fs-5"></i>
                                </button>
                                <button class="clause-delete-btn" 
                                        onclick="confirmClauseDelete(this)"
                                        title="Delete">
                                    <i class="bi bi-trash fs-5"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ISO/IEC 20000-1:2018 Category -->
    <div class="clause-standard-section" data-standard="ISO/IEC 20000-1:2018">
        <h5 class="clause-standard-title">ISO/IEC 20000-1:2018</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle clause-table">
                <thead class="table-light">
                    <tr>
                        <th style="width: 80px;">No</th>
                        <th>Clause</th>
                        <th>Description</th>
                        <th style="width: 130px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center">1</td>
                        <td>
                            <span class="clause-code">5 Leadership</span>
                        </td>
                        <td>Understanding the organization and its context</td>
                        <td class="text-center">
                            <div class="clause-action-buttons">
                                <button class="clause-edit-btn" 
                                        onclick="editClauseDocument(5, 'ISO/IEC 20000-1:2018', '5 Leadership', 'Understanding the organization and its context', 2)"
                                        title="Edit">
                                    <i class="bi bi-pencil-square fs-5"></i>
                                </button>
                                <button class="clause-delete-btn" 
                                        onclick="confirmClauseDelete(this)"
                                        title="Delete">
                                    <i class="bi bi-trash fs-5"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-center">2</td>
                        <td>
                            <span class="clause-code">4.4 Service management system</span>
                        </td>
                        <td>Actions to address risks and opportunities</td>
                        <td class="text-center">
                            <div class="clause-action-buttons">
                                <button class="clause-edit-btn" 
                                        onclick="editClauseDocument(6, 'ISO/IEC 20000-1:2018', '4.4 Service management system', 'Actions to address risks and opportunities', 2)"
                                        title="Edit">
                                    <i class="bi bi-pencil-square fs-5"></i>
                                </button>
                                <button class="clause-delete-btn" 
                                        onclick="confirmClauseDelete(this)"
                                        title="Delete">
                                    <i class="bi bi-trash fs-5"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- No Results Message -->
    <div id="clauseNoResults" class="clause-no-results" style="display: none;">
        <i class="fas fa-search fa-3x mb-3"></i>
        <p>No clauses found matching your search.</p>
    </div>
</div>

<!-- Add Modal with Searchable Dropdown -->
<div class="modal fade" id="clauseAddModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Clause</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="clauseAddForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="clauseAddDocumentType" class="form-label">Standard <span class="text-danger">*</span></label>
                        <div class="clause-position-relative">
                            <input type="text" 
                                   class="form-control" 
                                   id="clauseAddDocumentType" 
                                   name="standard_name"
                                   placeholder="Search for standard..."
                                   autocomplete="off"
                                   required>
                            <input type="hidden" id="clauseAddDocumentTypeId" name="document_type_id">
                            <div id="clauseAddDropdown" class="clause-searchable-dropdown-menu">
                                <div class="clause-dropdown-item" data-value="1" data-text="ISO/IEC 20000-1:2011">
                                    ISO/IEC 20000-1:2011
                                </div>
                                <div class="clause-dropdown-item" data-value="2" data-text="ISO/IEC 20000-1:2018">
                                    ISO/IEC 20000-1:2018
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="clauseAddCode" class="form-label">Clause <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="clauseAddCode" name="kode" placeholder="e.g., 4.1 Management responsibility" required>
                    </div>
                    <div class="mb-3">
                        <label for="clauseAddDocumentName" class="form-label">Description <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="clauseAddDocumentName" name="name" placeholder="Enter clause description" required>
                    </div>
                </div>
                <div class="modal-footer clause-modal-footer-grid">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal with Searchable Dropdown -->
<div class="modal fade" id="clauseEditModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Clause</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="clauseEditForm">
                <div class="modal-body">
                    <input type="hidden" id="clauseEditId" name="id">
                    <div class="mb-3">
                        <label for="clauseEditDocumentType" class="form-label">Standard <span class="text-danger">*</span></label>
                        <div class="clause-position-relative">
                            <input type="text" 
                                   class="form-control" 
                                   id="clauseEditDocumentType" 
                                   name="standard_name"
                                   placeholder="Search for standard..."
                                   autocomplete="off"
                                   required>
                            <input type="hidden" id="clauseEditDocumentTypeId" name="document_type_id">
                            <div id="clauseEditDropdown" class="clause-searchable-dropdown-menu">
                                <div class="clause-dropdown-item" data-value="1" data-text="ISO/IEC 20000-1:2011">
                                    ISO/IEC 20000-1:2011
                                </div>
                                <div class="clause-dropdown-item" data-value="2" data-text="ISO/IEC 20000-1:2018">
                                    ISO/IEC 20000-1:2018
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="clauseEditCode" class="form-label">Clause <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="clauseEditCode" name="kode" required>
                    </div>
                    <div class="mb-3">
                        <label for="clauseEditDescription" class="form-label">Description <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="clauseEditDescription" name="description" placeholder="Enter clause description" required>
                    </div>
                </div>
                <div class="modal-footer clause-modal-footer-grid">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Data storage (simulating backend)
    let clauseNextId = 7;
    const clauseDocumentTypes = {
        1: 'ISO/IEC 20000-1:2011',
        2: 'ISO/IEC 20000-1:2018', 
    };
    
    // Searchable Dropdown functionality - Generalized function
    function initializeSearchableDropdown(inputSelector, dropdownSelector, hiddenInputSelector) {
        const input = $(inputSelector);
        const dropdown = $(dropdownSelector);
        const hiddenInput = $(hiddenInputSelector);
        let selectedIndex = -1;
        
        // Show dropdown when input is focused
        input.on('focus', function() {
            dropdown.show();
            filterDropdownItems('', dropdown);
        });
        
        // Hide dropdown when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest(input.parent()).length) {
                dropdown.hide();
                selectedIndex = -1;
            }
        });
        
        // Filter dropdown items based on input
        input.on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            filterDropdownItems(searchTerm, dropdown);
            selectedIndex = -1;
        });
        
        // Handle keyboard navigation
        input.on('keydown', function(e) {
            const visibleItems = dropdown.find('.clause-dropdown-item:not(.hidden)');
            
            switch(e.keyCode) {
                case 38: // Up arrow
                    e.preventDefault();
                    selectedIndex = Math.max(0, selectedIndex - 1);
                    highlightItem(visibleItems, selectedIndex);
                    break;
                    
                case 40: // Down arrow
                    e.preventDefault();
                    selectedIndex = Math.min(visibleItems.length - 1, selectedIndex + 1);
                    highlightItem(visibleItems, selectedIndex);
                    break;
                    
                case 13: // Enter
                    e.preventDefault();
                    if (selectedIndex >= 0 && visibleItems.eq(selectedIndex).length) {
                        selectItem(visibleItems.eq(selectedIndex), input, dropdown, hiddenInput);
                    }
                    break;
                    
                case 27: // Escape
                    dropdown.hide();
                    selectedIndex = -1;
                    break;
            }
        });
        
        // Handle item clicks
        dropdown.on('click', '.clause-dropdown-item', function(e) {
            e.preventDefault();
            selectItem($(this), input, dropdown, hiddenInput);
        });
        
        function highlightItem(visibleItems, index) {
            visibleItems.removeClass('highlighted');
            if (index >= 0 && index < visibleItems.length) {
                visibleItems.eq(index).addClass('highlighted');
            }
        }
    }
    
    function filterDropdownItems(searchTerm, dropdown) {
        dropdown.find('.clause-dropdown-item').each(function() {
            const text = $(this).data('text').toLowerCase();
            if (text.includes(searchTerm)) {
                $(this).removeClass('hidden');
            } else {
                $(this).addClass('hidden');
            }
        });
        
        // Reset selection highlighting
        dropdown.find('.clause-dropdown-item').removeClass('highlighted selected');
    }
    
    function selectItem(item, input, dropdown, hiddenInput) {
        const value = item.data('value');
        const text = item.data('text');
        
        input.val(text);
        hiddenInput.val(value);
        dropdown.hide();
        
        // Remove all selection classes and add to selected item
        dropdown.find('.clause-dropdown-item').removeClass('selected highlighted');
        item.addClass('selected');
    }
    
    // Search functionality
    function performClauseSearch() {
        const searchTerm = $('#clauseSearchInput').val().toLowerCase().trim();
        let hasVisibleResults = false;
        
        $('.clause-standard-section').each(function() {
            const section = $(this);
            const sectionTitle = section.find('.clause-standard-title').text().toLowerCase();
            let sectionHasResults = false;
            
            section.find('tbody tr').each(function() {
                const row = $(this);
                const clause = row.find('.clause-code').text().toLowerCase();
                const description = row.find('td:nth-child(3)').text().toLowerCase();
                
                const isMatch = searchTerm === '' || 
                               sectionTitle.includes(searchTerm) ||
                               clause.includes(searchTerm) || 
                               description.includes(searchTerm);
                
                if (isMatch) {
                    row.show();
                    sectionHasResults = true;
                    hasVisibleResults = true;
                } else {
                    row.hide();
                }
            });
            
            // Show/hide section based on whether it has visible rows
            if (sectionHasResults || searchTerm === '') {
                section.show();
            } else {
                section.hide();
            }
            
            // Update row numbers for visible rows
            if (sectionHasResults) {
                let visibleIndex = 1;
                section.find('tbody tr:visible').each(function() {
                    $(this).find('td:first-child').text(visibleIndex++);
                });
            }
        });
        
        // Show/hide no results message
        if (hasVisibleResults || searchTerm === '') {
            $('#clauseNoResults').hide();
        } else {
            $('#clauseNoResults').show();
        }
    }
    
    // Edit function
    function editClauseDocument(id, type, code, name, typeId) {
        $('#clauseEditId').val(id);
        $('#clauseEditDocumentType').val(type);
        $('#clauseEditDocumentTypeId').val(typeId);
        $('#clauseEditCode').val(code);
        $('#clauseEditDescription').val(name);
        
        // Set the selected state for the dropdown
        $('#clauseEditDropdown .clause-dropdown-item').removeClass('selected');
        $('#clauseEditDropdown .clause-dropdown-item[data-value="' + typeId + '"]').addClass('selected');
        
        new bootstrap.Modal(document.getElementById('clauseEditModal')).show();
    }

    // Delete confirmation
    function confirmClauseDelete(button) {
        Swal.fire({
            title: 'Are you sure?',
            text: 'This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Remove the table row
                const row = button.closest('tr');
                const table = button.closest('table');
                row.remove();
                
                // Renumber remaining visible rows in the same table
                const rows = table.querySelectorAll('tbody tr:visible');
                rows.forEach((row, index) => {
                    row.cells[0].textContent = index + 1;
                });
                
                // Perform search to update display
                performClauseSearch();
                
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted!',
                    text: 'Clause has been deleted successfully.',
                    confirmButtonText: 'OK'
                });
            }
        });
    }

    $(document).ready(function() {
        // Initialize tooltips
        $('[title]').tooltip();
        
        // Initialize searchable dropdowns for both add and edit modals
        initializeSearchableDropdown('#clauseAddDocumentType', '#clauseAddDropdown', '#clauseAddDocumentTypeId');
        initializeSearchableDropdown('#clauseEditDocumentType', '#clauseEditDropdown', '#clauseEditDocumentTypeId');
        
        // Search functionality
        $('#clauseSearchInput').on('input', performClauseSearch);

        // Handle add form submission
        $('#clauseAddForm').on('submit', function(e) {
            e.preventDefault();
            
            const typeId = $('#clauseAddDocumentTypeId').val();
            const typeName = $('#clauseAddDocumentType').val();
            const code = $('#clauseAddCode').val().trim();
            const name = $('#clauseAddDocumentName').val().trim();
            
            if (!typeId || !code || !name) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Please fill in all required fields.',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            // Find the appropriate table section
            let targetSection = null;
            $('.clause-standard-section').each(function() {
                const standardAttr = $(this).attr('data-standard');
                if (standardAttr === typeName) {
                    targetSection = $(this);
                    return false;
                }
            });
            
            if (!targetSection) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Invalid standard selected.',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            // Add new row
            const tbody = targetSection.find('tbody');
            const rowCount = tbody.find('tr').length + 1;
            
            const newRow = `
                <tr>
                    <td class="text-center">${rowCount}</td>
                    <td>
                        <span class="clause-code">${code}</span>
                    </td>
                    <td>${name}</td>
                    <td class="text-center">
                        <div class="clause-action-buttons">
                            <button class="clause-edit-btn" 
                                    onclick="editClauseDocument(${clauseNextId}, '${typeName}', '${code}', '${name.replace(/'/g, "\\'")}', ${typeId})"
                                    title="Edit">
                                <i class="bi bi-pencil-square fs-5"></i>
                            </button>
                            <button class="clause-delete-btn" 
                                    onclick="confirmClauseDelete(this)"
                                    title="Delete">
                                <i class="bi bi-trash fs-5"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            
            tbody.append(newRow);
            clauseNextId++;
            
            // Clear form and close modal
            $('#clauseAddForm')[0].reset();
            $('#clauseAddDocumentTypeId').val(''); // Clear hidden input
            $('#clauseAddDropdown .clause-dropdown-item').removeClass('selected');
            bootstrap.Modal.getInstance(document.getElementById('clauseAddModal')).hide();
            
            // Refresh search results
            performClauseSearch();
            
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Clause has been added successfully.',
                confirmButtonText: 'OK'
            });
        });

        // Handle edit form submission
        $('#clauseEditForm').on('submit', function(e) {
            e.preventDefault();
            
            const id = $('#clauseEditId').val();
            const code = $('#clauseEditCode').val().trim();
            const typeName = $('#clauseEditDocumentType').val();
            const typeId = $('#clauseEditDocumentTypeId').val();
            const description = $('#clauseEditDescription').val().trim();
            
            if (!code || !typeName || !typeId || !description) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Please fill in all required fields.',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            // Find and update the row
            $('button[onclick*="editClauseDocument(' + id + ',"]').each(function() {
                const row = $(this).closest('tr');
                row.find('.clause-code').text(code);
                row.find('td:nth-child(3)').text(description); // Update description
                
                // Update onclick attributes for edit button in this row
                const editBtn = row.find('button[onclick*="editClauseDocument"]');
                const newOnclick = `editClauseDocument(${id}, '${typeName}', '${code}', '${description.replace(/'/g, "\\'")}', ${typeId})`;
                editBtn.attr('onclick', newOnclick);
            });
            
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('clauseEditModal')).hide();
            
            // Refresh search results
            performClauseSearch();
            
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Clause has been updated successfully.',
                confirmButtonText: 'OK'
            });
        });

        // Reset form when modal is hidden
        $('#clauseAddModal, #clauseEditModal').on('hidden.bs.modal', function() {
            $(this).find('form')[0].reset();
            // Reset dropdown selection and hidden inputs
            $(this).find('.clause-searchable-dropdown-menu .clause-dropdown-item').removeClass('selected highlighted');
            $(this).find('.clause-searchable-dropdown-menu').hide();
            $(this).find('input[type="hidden"]').val('');
        });
    });
</script>

<?= $this->endSection() ?>

<?= $this->section('additional_scripts') ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?= $this->endSection() ?>
<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="px-4 py-3 w-100">
    <h4>Clause Management</h4>
    <hr>

    <!-- Filter Section -->
    <div class="bg-light border rounded p-3 mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-xl-4 col-lg-4 col-md-6">
                <label for="filterStandard" class="form-label fw-semibold">Filter Standard</label>
                <div class="dropdown">
                    <input type="text" 
                           class="form-control dropdown-toggle" 
                           id="filterStandard" 
                           placeholder="Search standards..." 
                           data-bs-toggle="dropdown" 
                           aria-expanded="false"
                           autocomplete="off">
                    <ul class="dropdown-menu w-100" id="standardDropdown" style="max-height: 200px; overflow-y: auto;">
                        <li><a class="dropdown-item" href="#" data-value="">All Standards</a></li>
                        <?php 
                        // Show ALL standards from $standards variable, not just those with clauses
                        foreach ($standards as $standard) {
                            echo '<li><a class="dropdown-item" href="#" data-value="' . esc($standard['nama_standar']) . '">' . esc($standard['nama_standar']) . '</a></li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>
            <div class="col-xl-4 col-lg-4 col-md-6">
                <label for="searchClause" class="form-label fw-semibold">Search Clause</label>
                <input type="text" class="form-control" id="searchClause" placeholder="Search clauses...">
            </div>
            <div class="col-xl-4 col-lg-4 col-md-12">
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary flex-fill" id="resetFilters">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </button>
                    <button type="button" class="btn btn-primary flex-fill" data-bs-toggle="modal" data-bs-target="#clauseAddModal">
                        <i class="fas fa-plus me-1"></i> Add Clause
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive shadow-sm rounded bg-white p-3">
        <table class="table table-bordered table-hover align-middle" id="documentTable">
            <thead class="table-light">
                <tr>
                    <th class="text-center">No</th>
                    <th>Standard</th>
                    <th>Clause</th>
                    <th>Description</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody id="tableBody">
            <?php if (!empty($groupedClauses)) : ?>
                <?php 
                $no = 1;
                foreach ($groupedClauses as $standardName => $clauses): 
                    foreach ($clauses as $clause): ?>
                <tr data-clause-id="<?= $clause['id'] ?>" data-standard="<?= esc($standardName) ?>">
                    <td class="text-center"><?= $no++ ?></td>
                    <td><?= esc($standardName) ?></td>
                    <td>
                        <span class="clause-code"><?= esc($clause['nama_klausul']) ?></span>
                    </td>
                    <td><?= esc($clause['description']) ?></td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-2">
                            <button class="btn btn-link p-0 text-primary" data-bs-toggle="modal" data-bs-target="#clauseEditModal"
                                onclick="editClause(<?= $clause['id'] ?>, '<?= esc($clause['nama_standar']) ?>', '<?= esc($clause['nama_klausul']) ?>', '<?= esc($clause['description']) ?>', <?= $clause['standar_id'] ?>)"
                                title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button class="btn btn-link p-0" onclick="confirmClauseDelete(<?= $clause['id'] ?>)" title="Delete">
                                <i class="bi bi-trash text-danger"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php 
                    endforeach;
                endforeach; ?>
            <?php else : ?>
                <tr id="noDataRow">
                    <td class="text-center" colspan="5">No data found</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="clauseAddModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <div class="modal-header">
                <h5 class="modal-title">Add Clause</h5>
            </div>
            <form id="clauseAddForm" method="post" action="<?= base_url('document-clauses/store') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="clauseAddStandard" class="form-label">Standard <span class="text-danger">*</span></label>
                        <div class="dropdown">
                            <input type="text" 
                                   class="form-control dropdown-toggle" 
                                   id="clauseAddStandard" 
                                   placeholder="Select a standard..." 
                                   data-bs-toggle="dropdown" 
                                   aria-expanded="false"
                                   autocomplete="off"
                                   required>
                            <input type="hidden" id="clauseAddStandardId" name="standar_id">
                            <ul class="dropdown-menu w-100" id="addStandardDropdown" style="max-height: 200px; overflow-y: auto;">
                                <?php foreach ($standards as $standard): ?>
                                    <li><a class="dropdown-item" href="#" data-value="<?= $standard['id'] ?>" data-name="<?= esc($standard['nama_standar']) ?>"><?= esc($standard['nama_standar']) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="clauseAddCode" class="form-label">Clause <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="clauseAddCode" name="nama_klausul" required>
                    </div>
                    <div class="mb-3">
                        <label for="clauseAddDescription" class="form-label">Description <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="clauseAddDescription" name="description" placeholder="Enter clause description" required>
                    </div>
                </div>
                <div class="modal-footer d-grid gap-2" style="grid-template-columns: 1fr 1fr;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="clauseEditModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <div class="modal-header">
                <h5 class="modal-title">Edit Clause</h5>
            </div>
            <form id="clauseEditForm" method="post" action="<?= base_url('document-clauses/edit') ?>">
                <?= csrf_field() ?>
                <input type="hidden" id="clauseEditId" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="clauseEditStandard" class="form-label">Standard <span class="text-danger">*</span></label>
                        <div class="dropdown">
                            <input type="text" 
                                   class="form-control dropdown-toggle" 
                                   id="clauseEditStandard" 
                                   placeholder="Select a standard..." 
                                   data-bs-toggle="dropdown" 
                                   aria-expanded="false"
                                   autocomplete="off"
                                   required>
                            <input type="hidden" id="clauseEditStandardId" name="standar_id">
                            <ul class="dropdown-menu w-100" id="editStandardDropdown" style="max-height: 200px; overflow-y: auto;">
                                <?php foreach ($standards as $standard): ?>
                                    <li><a class="dropdown-item" href="#" data-value="<?= $standard['id'] ?>" data-name="<?= esc($standard['nama_standar']) ?>"><?= esc($standard['nama_standar']) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="clauseEditCode" class="form-label">Clause <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="clauseEditCode" name="nama_klausul" required>
                    </div>
                    <div class="mb-3">
                        <label for="clauseEditDescription" class="form-label">Description <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="clauseEditDescription" name="description" placeholder="Enter clause description" required>
                    </div>
                </div>
                <div class="modal-footer d-grid gap-2" style="grid-template-columns: 1fr 1fr;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Success/Error messages -->
<?php if (session()->has('swal')) : ?>
<script>
    Swal.fire({
        icon: '<?= session('swal.icon') ?>',
        title: '<?= session('swal.title') ?>',
        text: '<?= session('swal.text') ?>',
        confirmButtonColor: '#7066E0'
    });
</script>
<?php endif; ?>

<?php if (session()->has('added_message')) : ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '<?= session('added_message') ?>',
        confirmButtonColor: '#28a745'
    });
</script>
<?php endif; ?>

<?php if (session()->has('updated_message')) : ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '<?= session('updated_message') ?>',
        confirmButtonColor: '#28a745'
    });
</script>
<?php endif; ?>

<?php if (session()->has('deleted_message')) : ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '<?= session('deleted_message') ?>',
        confirmButtonColor: '#28a745'
    });
</script>
<?php endif; ?>

<script>
    let clauseTable;
    let selectedStandard = '';
    let addSelectedStandardId = '';
    let editSelectedStandardId = '';

    // Edit function
    function editClause(id, standardName, clauseNumber, clauseDescription, standardId) {
        $('#clauseEditId').val(id);
        $('#clauseEditStandard').val(standardName);
        $('#clauseEditStandardId').val(standardId);
        $('#clauseEditCode').val(clauseNumber);
        $('#clauseEditDescription').val(clauseDescription);
        editSelectedStandardId = standardId;
    }

    // Delete function
    function confirmClauseDelete(clauseId) {
        Swal.fire({
            title: 'Are you sure?',
            text: 'This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: 'rgba(118, 125, 131, 1)',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                deleteClause(clauseId);
            }
        });
    }

    async function deleteClause(clauseId) {
        try {
            const formData = new FormData();
            formData.append('id', clauseId);
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

            const response = await fetch('<?= base_url('document-clauses/delete') ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (result.status === 'success') {
                // Remove row from DataTable
                clauseTable.row($(`tr[data-clause-id="${clauseId}"]`)).remove().draw();
                
                // Check if table is empty after deletion and show "No data found"
                checkAndShowNoDataRow();
                
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted!',
                    text: result.message,
                    confirmButtonColor: '#28a745'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.message || 'Failed to delete clause.',
                    confirmButtonColor: '#d33'
                });
            }
        } catch (error) {
            console.error('Error deleting clause:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while deleting the clause.',
                confirmButtonColor: '#d33'
            });
        }
    }

    // Function to check and show "No data found" row
    function checkAndShowNoDataRow() {
        const visibleRows = $('#tableBody tr:visible').not('#noDataRow').length;
        
        if (visibleRows === 0) {
            // Remove existing no data row if any
            $('#noDataRow').remove();
            // Add new no data row
            $('#tableBody').append('<tr id="noDataRow"><td class="text-center" colspan="5">No data found</td></tr>');
        } else {
            // Remove no data row if there are visible rows
            $('#noDataRow').remove();
        }
    }

    $(document).ready(function() {
        // Initialize DataTable
        clauseTable = $('#documentTable').DataTable({
            "language": {
                "search": "Search:",
                "lengthMenu": "Show _MENU_ entries",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "infoEmpty": "Showing 0 to 0 of 0 entries",
                "infoFiltered": "(filtered from _MAX_ total entries)",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                }
            },
            "searching": false // Disable default search since we're using custom filters
        });

        // FILTER STANDARD SEARCHABLE DROPDOWN
        // Show dropdown when clicked
        $('#filterStandard').on('click', function() {
            $('#standardDropdown .dropdown-item').parent().show();
            $(this).dropdown('show');
        });

        // Searchable logic for filter
        $('#filterStandard').on('input', function() {
            const searchText = this.value.toLowerCase();
            const dropdownItems = $('#standardDropdown .dropdown-item');
            
            dropdownItems.each(function() {
                const itemText = $(this).text().toLowerCase();
                if (itemText.includes(searchText) || searchText === '') {
                    $(this).parent().show();
                } else {
                    $(this).parent().hide();
                }
            });

            if (!$(this).next('.dropdown-menu').hasClass('show')) {
                $(this).dropdown('show');
            }
        });

        // Handle filter dropdown selection
        $('#standardDropdown').on('click', '.dropdown-item', function(e) {
            e.preventDefault();
            const selectedText = $(this).text();
            const selectedValue = $(this).data('value');
            
            $('#filterStandard').val(selectedText);
            selectedStandard = selectedValue;
            
            $('#filterStandard').dropdown('hide');
            applyFilters();
        });

        // ADD MODAL SEARCHABLE DROPDOWN
        // Show dropdown when clicked
        $('#clauseAddStandard').on('click', function() {
            $('#addStandardDropdown .dropdown-item').parent().show();
            $(this).dropdown('show');
        });

        // Searchable logic for add modal
        $('#clauseAddStandard').on('input', function() {
            const searchText = this.value.toLowerCase();
            const dropdownItems = $('#addStandardDropdown .dropdown-item');
            
            dropdownItems.each(function() {
                const itemText = $(this).text().toLowerCase();
                if (itemText.includes(searchText) || searchText === '') {
                    $(this).parent().show();
                } else {
                    $(this).parent().hide();
                }
            });

            if (!$(this).next('.dropdown-menu').hasClass('show')) {
                $(this).dropdown('show');
            }
        });

        // Handle add dropdown selection
        $('#addStandardDropdown').on('click', '.dropdown-item', function(e) {
            e.preventDefault();
            const selectedText = $(this).data('name');
            const selectedValue = $(this).data('value');
            
            $('#clauseAddStandard').val(selectedText);
            $('#clauseAddStandardId').val(selectedValue);
            addSelectedStandardId = selectedValue;
            
            $('#clauseAddStandard').dropdown('hide');
        });

        // EDIT MODAL SEARCHABLE DROPDOWN
        // Show dropdown when clicked
        $('#clauseEditStandard').on('click', function() {
            $('#editStandardDropdown .dropdown-item').parent().show();
            $(this).dropdown('show');
        });

        // Searchable logic for edit modal
        $('#clauseEditStandard').on('input', function() {
            const searchText = this.value.toLowerCase();
            const dropdownItems = $('#editStandardDropdown .dropdown-item');
            
            dropdownItems.each(function() {
                const itemText = $(this).text().toLowerCase();
                if (itemText.includes(searchText) || searchText === '') {
                    $(this).parent().show();
                } else {
                    $(this).parent().hide();
                }
            });

            if (!$(this).next('.dropdown-menu').hasClass('show')) {
                $(this).dropdown('show');
            }
        });

        // Handle edit dropdown selection
        $('#editStandardDropdown').on('click', '.dropdown-item', function(e) {
            e.preventDefault();
            const selectedText = $(this).data('name');
            const selectedValue = $(this).data('value');
            
            $('#clauseEditStandard').val(selectedText);
            $('#clauseEditStandardId').val(selectedValue);
            editSelectedStandardId = selectedValue;
            
            $('#clauseEditStandard').dropdown('hide');
        });

        // Custom filter functions
        function applyFilters() {
            const searchText = $('#searchClause').val().toLowerCase();
            const standardFilter = selectedStandard;

            // Remove existing no data row before filtering
            $('#noDataRow').remove();

            let visibleCount = 0;

            $('#tableBody tr').not('#noDataRow').each(function() {
                const row = this;
                
                // Get text content from each cell
                const standard = $(row).find('td:nth-child(2)').text();
                const clause = $(row).find('td:nth-child(3)').text().toLowerCase();
                const description = $(row).find('td:nth-child(4)').text().toLowerCase();

                let show = true;

                // Apply search filter (searches in clause and description)
                if (searchText && !clause.includes(searchText) && !description.includes(searchText)) {
                    show = false;
                }

                // Apply standard filter
                if (standardFilter && standard !== standardFilter) {
                    show = false;
                }

                // Show/hide row
                if (show) {
                    $(row).show();
                    visibleCount++;
                } else {
                    $(row).hide();
                }
            });

            // Show "No data found" if no rows are visible
            if (visibleCount === 0) {
                $('#tableBody').append('<tr id="noDataRow"><td class="text-center" colspan="5">No data found</td></tr>');
            }

            // Update DataTable display
            clauseTable.draw();
        }

        // Bind filter events
        $('#searchClause').on('keyup', function() {
            applyFilters();
        });

        // Reset filters
        $('#resetFilters').on('click', function() {
            $('#searchClause').val('');
            $('#filterStandard').val('');
            selectedStandard = '';
            
            // Remove no data row
            $('#noDataRow').remove();
            
            // Show all data rows
            $('#tableBody tr').not('#noDataRow').show();
            
            // Check if we need to show no data row
            checkAndShowNoDataRow();
            
            clauseTable.draw();
        });

        // Handle add form submission
        $('#clauseAddForm').on('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('<?= base_url('document-clauses/store') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    bootstrap.Modal.getInstance(document.getElementById('clauseAddModal')).hide();
                    $('#clauseAddForm')[0].reset();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: result.message,
                        confirmButtonColor: '#28a745'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    if (result.errors) {
                        let errorMessages = [];
                        for (let field in result.errors) {
                            errorMessages.push(result.errors[field]);
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            html: errorMessages.join('<br>'),
                            confirmButtonColor: '#d33'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: result.message || 'Failed to add clause.',
                            confirmButtonColor: '#d33'
                        });
                    }
                }
            } catch (error) {
                console.error('Error adding clause:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while adding the clause.',
                    confirmButtonColor: '#d33'
                });
            }
        });
        
        // Handle edit form submission
        $('#clauseEditForm').on('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('<?= base_url('document-clauses/edit') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    bootstrap.Modal.getInstance(document.getElementById('clauseEditModal')).hide();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: result.message,
                        confirmButtonColor: '#28a745'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    if (result.errors) {
                        let errorMessages = [];
                        for (let field in result.errors) {
                            errorMessages.push(result.errors[field]);
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            html: errorMessages.join('<br>'),
                            confirmButtonColor: '#d33'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: result.message || 'Failed to update clause.',
                            confirmButtonColor: '#d33'
                        });
                    }
                }
            } catch (error) {
                console.error('Error updating clause:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while updating the clause.',
                    confirmButtonColor: '#d33'
                });
            }
        });
        
        // Reset forms when modal is hidden
        $('#clauseAddModal').on('hidden.bs.modal', function() {
            $(this).find('form')[0].reset();
            $('#clauseAddStandard').val('');
            $('#clauseAddStandardId').val('');
            addSelectedStandardId = '';
        });

        $('#clauseEditModal').on('hidden.bs.modal', function() {
            $(this).find('form')[0].reset();
            $('#clauseEditStandard').val('');
            $('#clauseEditStandardId').val('');
            editSelectedStandardId = '';
        });

        // Autofocus on modal open
        $('#clauseAddModal').on('shown.bs.modal', function() {
            $('#clauseAddStandard').focus();
        });

        $('#clauseEditModal').on('shown.bs.modal', function() {
            $('#clauseEditCode').focus();
        });

        // Initial check for no data
        checkAndShowNoDataRow();
    });
</script>

<?= $this->endSection() ?>
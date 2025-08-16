<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Clause Management</h4>
    </div>

    <!-- Show Entries and Search -->
    <div class="mb-2">
        <div class="row align-items-center">
            <div class="col-md-3">
                <div class="d-flex align-items-center">
                    <label for="entriesPerPage" class="form-label me-2 mb-0">Show</label>
                    <select class="form-select form-select-sm" id="entriesPerPage" onchange="changeEntriesPerPage()" style="width: 70px;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="ms-2">entries</span>
                </div>
            </div>
            <div class="col-md-9">
                <div class="d-flex justify-content-end align-items-center gap-2">
                    <div class="input-group" style="width: 250px;">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" id="clauseSearchInput" placeholder="Search clauses..." autocomplete="off">
                    </div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#clauseAddModal">
                        <i class="bi bi-plus-circle me-1"></i>Add New Clause
                    </button>
                </div>
            </div>
            </div>
        </div>
    </div>

    <!-- Single Table with Grouped Data -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width: 8%; text-align: center;">No</th>
                    <th style="width: 20%;">Standard</th>
                    <th style="width: 25%;">Clause</th>
                    <th style="width: 35%;">Description</th>
                    <th style="width: 12%; text-align: center;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($clauses)): ?>
                    <tr id="noDataRow">
                        <td colspan="5" class="text-center py-4">
                            <span class="text-muted">No data</span>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $currentStandard = ''; 
                    $rowNumber = 1; 
                    ?>
                    <?php foreach ($clauses as $clause): ?>
                    <?php 
                    $isNewStandard = ($currentStandard !== $clause['nama_standar']);
                    if ($isNewStandard) {
                        $currentStandard = $clause['nama_standar'];
                    }
                    ?>
                    <tr data-clause-id="<?= $clause['id'] ?>" 
                        data-standard="<?= esc($clause['nama_standar']) ?>">
                        <td class="text-center"><?= $rowNumber ?></td>
                        <td><?= esc($clause['nama_standar']) ?></td>
                        <td><?= esc($clause['nama_klausul']) ?></td>
                        <td><?= esc($clause['description']) ?></td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <button class="btn btn-sm btn-outline-primary border-0" 
                                        onclick="editClause(<?= $clause['id'] ?>, '<?= esc($clause['nama_standar']) ?>', '<?= esc($clause['nama_klausul']) ?>', '<?= esc($clause['description']) ?>', <?= $clause['standar_id'] ?>)"
                                        title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger border-0" 
                                        onclick="confirmClauseDelete(<?= $clause['id'] ?>)"
                                        title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php $rowNumber++; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Info and Controls -->
    <div class="row align-items-center mt-2">
        <div class="col-md-6">
            <div id="paginationInfo" class="text-muted">
                Showing 1 to 10 of 45 entries
            </div>
        </div>
        <div class="col-md-6">
            <nav aria-label="Clause pagination">
                <ul class="pagination pagination-sm justify-content-end mb-0" id="paginationControls">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" onclick="changePage('previous')" aria-label="Previous">Previous</a>
                    </li>
                    <li class="page-item active">
                        <a class="page-link" href="#" onclick="changePage(1)">1</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#" onclick="changePage(2)">2</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#" onclick="changePage(3)">3</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#" onclick="changePage(4)">4</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#" onclick="changePage(5)">5</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#" onclick="changePage('next')" aria-label="Next">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <!-- No Results Message -->
    <div id="clauseNoResults" class="text-center py-5" style="display: none;">
        <i class="fas fa-search fa-3x mb-3 text-muted"></i>
        <p class="text-muted">No clauses found matching your search.</p>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="clauseAddModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Clause</h5>
            </div>
            <form id="clauseAddForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="clauseAddStandard" class="form-label">Standard <span class="text-danger">*</span></label>
                        <select class="form-select" id="clauseAddStandard" name="standar_id" required>
                            <option value="">Select a standard...</option>
                            <?php foreach ($standards as $standard): ?>
                                <option value="<?= $standard['id'] ?>"><?= esc($standard['nama_standar']) ?></option>
                            <?php endforeach; ?>
                        </select>
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
<div class="modal fade" id="clauseEditModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Clause</h5>
            </div>
            <form id="clauseEditForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <input type="hidden" id="clauseEditId" name="id">
                    <div class="mb-3">
                        <label for="clauseEditStandard" class="form-label">Standard <span class="text-danger">*</span></label>
                        <select class="form-select" id="clauseEditStandard" name="standar_id" required>
                            <option value="">Select a standard...</option>
                            <?php foreach ($standards as $standard): ?>
                                <option value="<?= $standard['id'] ?>"><?= esc($standard['nama_standar']) ?></option>
                            <?php endforeach; ?>
                        </select>
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

<script>
    // Search functionality
    function performClauseSearch() {
        const searchTerm = $('#clauseSearchInput').val().toLowerCase().trim();
        let hasVisibleResults = false;
        
        console.log('Search term:', searchTerm); // Debug log
        
        // Check if we have data to search
        const dataRows = $('tbody tr:not(#noDataRow)');
        if (dataRows.length === 0) {
            console.log('No data available to search');
            return;
        }
        
        // First, show all data rows to apply search filter
        dataRows.show();
        
        dataRows.each(function() {
            const row = $(this);
            
            // Get text content from each column
            const standard = row.find('td:nth-child(2)').text().toLowerCase().trim();
            const clause = row.find('td:nth-child(3)').text().toLowerCase().trim();
            const description = row.find('td:nth-child(4)').text().toLowerCase().trim();
            
            console.log('Row data:', { standard, clause, description }); // Debug log
            
            // Check if search term matches any of the columns
            const isMatch = searchTerm === '' || 
                           standard.includes(searchTerm) ||
                           clause.includes(searchTerm) || 
                           description.includes(searchTerm);
            
            if (isMatch) {
                hasVisibleResults = true;
                row.show();
            } else {
                row.hide();
            }
        });
        
        // Update display based on search results
        if (hasVisibleResults || searchTerm === '') {
            $('#clauseNoResults').hide();
            $('.table-responsive').show();
            updateFilteredEntries(); // Update pagination after filtering
        } else {
            $('#clauseNoResults').show();
            $('.table-responsive').hide();
        }
        
        console.log('Has visible results:', hasVisibleResults); // Debug log
    }
    
    // Edit function
    function editClause(id, standardName, clauseNumber, clauseDescription, standardId) {
        $('#clauseEditId').val(id);
        $('#clauseEditStandard').val(standardId);
        $('#clauseEditCode').val(clauseNumber);
        $('#clauseEditDescription').val(clauseDescription);
        
        new bootstrap.Modal(document.getElementById('clauseEditModal')).show();
    }
    
    // Delete function
    function confirmClauseDelete(clauseId) {
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
                $(`tr[data-clause-id="${clauseId}"]`).remove();
                
                // Update pagination after deletion
                initializePagination();
                
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted!',
                    text: result.message,
                    confirmButtonText: 'OK'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.message || 'Failed to delete clause.',
                    confirmButtonText: 'OK'
                });
            }
        } catch (error) {
            console.error('Error deleting clause:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while deleting the clause.',
                confirmButtonText: 'OK'
            });
        }
    }
    
    // Pagination variables
    let currentPage = 1;
    let entriesPerPage = 10;
    let totalEntries = 0;
    let filteredEntries = [];
    
    // Function to change entries per page
    function changeEntriesPerPage() {
        entriesPerPage = parseInt($('#entriesPerPage').val());
        currentPage = 1; // Reset to first page
        updatePagination();
        displayCurrentPage();
    }
    
    // Function to change page
    function changePage(page) {
        const totalPages = Math.ceil(filteredEntries.length / entriesPerPage);
        
        if (page === 'previous') {
            if (currentPage > 1) {
                currentPage--;
            }
        } else if (page === 'next') {
            if (currentPage < totalPages) {
                currentPage++;
            }
        } else {
            currentPage = parseInt(page);
        }
        
        updatePagination();
        displayCurrentPage();
    }
    
    // Function to update pagination controls
    function updatePagination() {
        const totalPages = Math.ceil(filteredEntries.length / entriesPerPage);
        const startEntry = ((currentPage - 1) * entriesPerPage) + 1;
        const endEntry = Math.min(currentPage * entriesPerPage, filteredEntries.length);
        
        // Update pagination info
        $('#paginationInfo').text(`Showing ${startEntry} to ${endEntry} of ${filteredEntries.length} entries`);
        
        // Update pagination controls
        let paginationHtml = '';
        
        // Previous button
        paginationHtml += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage('previous')" aria-label="Previous">Previous</a>
        </li>`;
        
        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            if (i === currentPage) {
                paginationHtml += `<li class="page-item active">
                    <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
                </li>`;
            } else if (i <= 5 || (currentPage <= 3 && i <= 5) || (currentPage >= totalPages - 2 && i >= totalPages - 4) || Math.abs(i - currentPage) <= 1) {
                paginationHtml += `<li class="page-item">
                    <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
                </li>`;
            } else if (i === currentPage - 2 || i === currentPage + 2) {
                paginationHtml += `<li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>`;
            }
        }
        
        // Next button
        paginationHtml += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage('next')" aria-label="Next">Next</a>
        </li>`;
        
        $('#paginationControls').html(paginationHtml);
    }
    
    // Function to display current page
    function displayCurrentPage() {
        const startIndex = (currentPage - 1) * entriesPerPage;
        const endIndex = startIndex + entriesPerPage;
        
        // Hide all data rows first (but not the noDataRow)
        $('tbody tr:not(#noDataRow)').hide();
        
        // Show only rows for current page
        for (let i = startIndex; i < endIndex && i < filteredEntries.length; i++) {
            $(filteredEntries[i]).show();
        }
        
        updateTableDisplay();
    }
    
    // Function to initialize pagination data
    function initializePagination() {
        filteredEntries = $('tbody tr:not(#noDataRow)').toArray();
        totalEntries = filteredEntries.length;
        currentPage = 1;
        
        if (totalEntries === 0) {
            // Show no data message and hide pagination
            $('#paginationInfo').text('Showing 0 to 0 of 0 entries');
            $('#paginationControls').html('');
            return;
        }
        
        updatePagination();
        displayCurrentPage();
    }
    
    // Function to update filtered entries (used after search)
    function updateFilteredEntries() {
        filteredEntries = $('tbody tr:visible:not(#noDataRow)').toArray();
        currentPage = 1; // Reset to first page when filtering
        
        console.log('Filtered entries count:', filteredEntries.length); // Debug log
        
        if (filteredEntries.length === 0) {
            // Show no results message
            $('#clauseNoResults').show();
            $('.table-responsive').hide();
            $('#paginationInfo').text('Showing 0 to 0 of 0 entries');
            $('#paginationControls').html('');
        } else {
            // Update pagination and display
            $('#clauseNoResults').hide();
            $('.table-responsive').show();
            updatePagination();
            displayCurrentPage();
        }
    }
    
    // Function to update table display after changes
    function updateTableDisplay() {
        let rowNumber = ((currentPage - 1) * entriesPerPage) + 1;
        
        $('tbody tr:visible:not(#noDataRow)').each(function() {
            const row = $(this);
            // Update row number
            row.find('td:first-child').text(rowNumber++);
        });
    }
    
    $(document).ready(function() {
        console.log('Document ready - initializing clause management'); // Debug log
        
        // Initialize pagination
        initializePagination();
        
        // Initialize tooltips
        $('[title]').tooltip();
        
        // Search functionality - with event delegation to ensure it works
        $(document).off('input', '#clauseSearchInput').on('input', '#clauseSearchInput', function() {
            console.log('Search input triggered'); // Debug log
            performClauseSearch();
        });
        
        // Also add keyup event for better responsiveness
        $(document).off('keyup', '#clauseSearchInput').on('keyup', '#clauseSearchInput', function() {
            performClauseSearch();
        });
        
        // Test if jQuery is working
        console.log('jQuery version:', $.fn.jquery);
        console.log('Search input element found:', $('#clauseSearchInput').length > 0);
        
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
                        confirmButtonText: 'OK'
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
                            confirmButtonText: 'OK'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: result.message || 'Failed to add clause.',
                            confirmButtonText: 'OK'
                        });
                    }
                }
            } catch (error) {
                console.error('Error adding clause:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while adding the clause.',
                    confirmButtonText: 'OK'
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
                        confirmButtonText: 'OK'
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
                            confirmButtonText: 'OK'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: result.message || 'Failed to update clause.',
                            confirmButtonText: 'OK'
                        });
                    }
                }
            } catch (error) {
                console.error('Error updating clause:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while updating the clause.',
                    confirmButtonText: 'OK'
                });
            }
        });
        
        $('#clauseAddModal, #clauseEditModal').on('hidden.bs.modal', function() {
            $(this).find('form')[0].reset();
        });
    });
    
    // Fallback initialization in case document ready doesn't work properly
    window.addEventListener('load', function() {
        console.log('Window loaded - setting up fallback search');
        
        const searchInput = document.getElementById('clauseSearchInput');
        if (searchInput && typeof performClauseSearch === 'function') {
            // Remove any existing listeners first
            searchInput.removeEventListener('input', performClauseSearch);
            searchInput.removeEventListener('keyup', performClauseSearch);
            
            // Add new listeners
            searchInput.addEventListener('input', performClauseSearch);
            searchInput.addEventListener('keyup', performClauseSearch);
            
            console.log('Fallback search listeners attached');
        }
    });
</script>
<?= $this->endSection() ?>

<?= $this->section('additional_scripts') ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Additional search initialization after all scripts are loaded
$(function() {
    console.log('Additional jQuery initialization');
    
    // Double-check search functionality
    $('#clauseSearchInput').off('input keyup').on('input keyup', function() {
        console.log('Search triggered from additional init');
        performClauseSearch();
    });
    
    // Test search immediately
    if ($('#clauseSearchInput').length) {
        console.log('Search input found and ready');
    } else {
        console.error('Search input not found!');
    }
});
</script>
<?= $this->endSection() ?>
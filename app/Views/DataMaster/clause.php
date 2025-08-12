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

    <!-- Dynamic Standard Sections -->
    <?php foreach ($groupedClauses as $standardName => $clauses): ?>
    <div class="clause-standard-section" data-standard="<?= esc($standardName) ?>">
        <h5 class="clause-standard-title"><?= esc($standardName) ?></h5>
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
                    <?php foreach ($clauses as $index => $clause): ?>
                    <tr data-clause-id="<?= $clause['id'] ?>">
                        <td class="text-center"><?= $index + 1 ?></td>
                        <td>
                            <span class="clause-code"><?= esc($clause['nomor_klausul']) ?></span>
                        </td>
                        <td><?= esc($clause['nama_klausul']) ?></td>
                        <td class="text-center">
                            <div class="clause-action-buttons">
                                <button class="clause-edit-btn" 
                                        onclick="editClause(<?= $clause['id'] ?>, '<?= esc($clause['nama_standar']) ?>', '<?= esc($clause['nomor_klausul']) ?>', '<?= esc($clause['nama_klausul']) ?>', <?= $clause['standar_id'] ?>)"
                                        title="Edit">
                                    <i class="bi bi-pencil-square fs-5"></i>
                                </button>
                                <button class="clause-delete-btn" 
                                        onclick="confirmClauseDelete(<?= $clause['id'] ?>)"
                                        title="Delete">
                                    <i class="bi bi-trash fs-5"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- No Results Message -->
    <div id="clauseNoResults" class="clause-no-results" style="display: none;">
        <i class="fas fa-search fa-3x mb-3"></i>
        <p>No clauses found matching your search.</p>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="clauseAddModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Clause</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                        <input type="text" class="form-control" id="clauseAddCode" name="nomor_klausul" required>
                    </div>
                    <div class="mb-3">
                        <label for="clauseAddDescription" class="form-label">Description <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="clauseAddDescription" name="nama_klausul" placeholder="Enter clause description" required>
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

<!-- Edit Modal -->
<div class="modal fade" id="clauseEditModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Clause</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                        <input type="text" class="form-control" id="clauseEditCode" name="nomor_klausul" required>
                    </div>
                    <div class="mb-3">
                        <label for="clauseEditDescription" class="form-label">Description <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="clauseEditDescription" name="nama_klausul" placeholder="Enter clause description" required>
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
            
            if (sectionHasResults || searchTerm === '') {
                section.show();
            } else {
                section.hide();
            }
            
            if (sectionHasResults) {
                let visibleIndex = 1;
                section.find('tbody tr:visible').each(function() {
                    $(this).find('td:first-child').text(visibleIndex++);
                });
            }
        });
        
        if (hasVisibleResults || searchTerm === '') {
            $('#clauseNoResults').hide();
        } else {
            $('#clauseNoResults').show();
        }
    }
    
    // Edit function
    function editClause(id, standardName, clauseNumber, description, standardId) {
        $('#clauseEditId').val(id);
        $('#clauseEditStandard').val(standardId);
        $('#clauseEditCode').val(clauseNumber);
        $('#clauseEditDescription').val(description);
        
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
        // Tambahkan token CSRF
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
            performClauseSearch();
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
    
    $(document).ready(function() {
        // Initialize tooltips
        $('[title]').tooltip();
        
        // Search functionality
        $('#clauseSearchInput').on('input', performClauseSearch);
        
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
                    // Close modal and reset form
                    bootstrap.Modal.getInstance(document.getElementById('clauseAddModal')).hide();
                    $('#clauseAddForm')[0].reset();
                    
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: result.message,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Reload page to show updated data
                        location.reload();
                    });
                } else {
                    // Show validation errors
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
                    // Close modal
                    bootstrap.Modal.getInstance(document.getElementById('clauseEditModal')).hide();
                    
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: result.message,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Reload page to show updated data
                        location.reload();
                    });
                } else {
                    // Show validation errors
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
        
        // Reset form when modal is hidden
        $('#clauseAddModal, #clauseEditModal').on('hidden.bs.modal', function() {
            $(this).find('form')[0].reset();
        });
    });
</script>
<?= $this->endSection() ?>

<?= $this->section('additional_scripts') ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?= $this->endSection() ?>
<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Document Standards List</h4>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="fas fa-plus me-2"></i>Add Document Standard
                </button>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="standardsTable" class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 80px;">No</th>
                                    <th>Standard Name</th>
                                    <th>Description</th>
                                    <th class="text-center" style="width: 120px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($standards)) : ?>
                                    <?php foreach ($standards as $index => $standard) : ?>
                                        <tr>
                                            <td class="text-center"><?= $index + 1 ?></td>
                                            <td><?= esc($standard['nama_standar']) ?></td>
                                            <td><?= esc($standard['description'] ?? '-') ?></td>
                                            <td class="text-center">
                                                <i class="bi bi-pencil-square text-primary me-2" 
                                                   style="cursor: pointer; font-size: 16px;" 
                                                   onclick="editStandard(<?= $standard['id'] ?>, '<?= esc($standard['nama_standar'], 'js') ?>', '<?= esc($standard['description'] ?? '', 'js') ?>')"
                                                   title="Edit"></i>
                                                <i class="bi bi-trash text-danger delete-icon" 
                                                   style="cursor: pointer; font-size: 16px;" 
                                                   data-id="<?= $standard['id'] ?>"
                                                   title="Delete"></i>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Document Standard</h5>
            </div>
            <form id="addForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add_nama_standar" class="form-label">Standard Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add_nama_standar" name="nama_standar" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="add_description" class="form-label">Description</label>
                        <textarea class="form-control" id="add_description" name="description" rows="4"></textarea>
                        <div class="invalid-feedback"></div>
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
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Document Standard</h5>
            </div>
            <form id="editForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="mb-3">
                        <label for="edit_nama_standar" class="form-label">Standard Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_nama_standar" name="nama_standar" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="4"></textarea>
                        <div class="invalid-feedback"></div>
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

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#standardsTable').DataTable({
        "pageLength": 10,
        "lengthMenu": [10, 25, 50, 100],
        "pagingType": "simple_numbers",
        "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
               '<"row"<"col-sm-12"tr>>' +
               '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"<"d-flex justify-content-end"p>>>',
        "language": {
            "search": "Search:",
            "lengthMenu": "Show _MENU_ entries",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "paginate": {
                "previous": "Previous",
                "next": "Next"
            },
            "emptyTable": ""  // Remove 'No data available in table' message
        },
        "columnDefs": [
            { "orderable": false, "targets": 3 } // Disable sorting on Action column
        ],
        "drawCallback": function(settings) {
            // Remove outer container from pagination
            $('.dataTables_paginate .pagination').unwrap();
            
            // Fix active state for pagination buttons
            $('.dataTables_paginate .paginate_button').each(function() {
                if ($(this).hasClass('current')) {
                    $(this).addClass('active');
                }
            });
        }
    });

    // Event delegation for delete icon
    $(document).on('click', '.delete-icon', function() {
        const id = $(this).data('id');
        deleteStandard(id);
    });

    // Add Form Submit
    document.getElementById('addForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('<?= base_url('document-standards/store') ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('addModal'));
                modal.hide();
                
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Successfully Added',
                    confirmButtonColor: '#7066E0',
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });
            } else {
                if (data.errors) {
                    let errorMessage = 'Validation failed:';
                    if (data.errors.nama_standar) {
                        const input = document.getElementById('add_nama_standar');
                        input.classList.add('is-invalid');
                        input.nextElementSibling.textContent = data.errors.nama_standar;
                        errorMessage += `\n- ${data.errors.nama_standar}`;
                    }
                    if (data.errors.description) {
                        const input = document.getElementById('add_description');
                        input.classList.add('is-invalid');
                        input.nextElementSibling.textContent = data.errors.description;
                        errorMessage += `\n- ${data.errors.description}`;
                    }
                    // Show SweetAlert for validation errors
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: errorMessage,
                        confirmButtonColor: '#d33'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || 'An error occurred',
                        confirmButtonColor: '#d33'
                    });
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Network error occurred',
                confirmButtonColor: '#d33'
            });
        });
    });

    // Edit Form Submit
    document.getElementById('editForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('<?= base_url('document-standards/update') ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
                modal.hide();
                
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Successfully Updated',
                    confirmButtonColor: '#7066E0',
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });
            } else {
                if (data.errors) {
                    if (data.errors.nama_standar) {
                        const input = document.getElementById('edit_nama_standar');
                        input.classList.add('is-invalid');
                        input.nextElementSibling.textContent = data.errors.nama_standar;
                    }
                    if (data.errors.description) {
                        const input = document.getElementById('edit_description');
                        input.classList.add('is-invalid');
                        input.nextElementSibling.textContent = data.errors.description;
                    }
                    // Show SweetAlert for validation errors
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Validation failed: ' + (data.errors.nama_standar || data.errors.description || 'Please check the form'),
                        confirmButtonColor: '#d33'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || 'An error occurred',
                        confirmButtonColor: '#d33'
                    });
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Network error occurred',
                confirmButtonColor: '#d33'
            });
        });
    });

    // Delete Standard Function
    function deleteStandard(id) {
        Swal.fire({
            icon: 'warning',
            title: 'Are you sure?',
            text: 'This action cannot be undone.',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#767d83',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('id', id);
                formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
                
                fetch('<?= base_url('document-standards/delete') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Standard deleted successfully',
                            confirmButtonColor: '#7066E0',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'Failed to delete',
                            confirmButtonColor: '#d33'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Network error occurred',
                        confirmButtonColor: '#d33'
                    });
                });
            }
        });
    }

    // Clear validation on modal show
    document.getElementById('addModal').addEventListener('show.bs.modal', function() {
        const form = document.getElementById('addForm');
        form.reset();
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    });

    document.getElementById('editModal').addEventListener('show.bs.modal', function() {
        const form = document.getElementById('editForm');
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    });
});

// Edit Standard Function
function editStandard(id, nama, description) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nama_standar').value = nama;
    document.getElementById('edit_description').value = description || '';
    
    const editModal = new bootstrap.Modal(document.getElementById('editModal'));
    editModal.show();
}
</script>

<?= $this->endSection() ?>
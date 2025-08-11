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
                        <table class="table table-bordered">
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
                                                <i class="bi bi-trash text-danger" 
                                                   style="cursor: pointer; font-size: 16px;" 
                                                   onclick="deleteStandard(<?= $standard['id'] ?>)"
                                                   title="Delete"></i>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No data available</td>
                                    </tr>
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
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
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
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                if (data.errors) {
                    if (data.errors.nama_standar) {
                        const input = document.getElementById('add_nama_standar');
                        input.classList.add('is-invalid');
                        input.nextElementSibling.textContent = data.errors.nama_standar;
                    }
                    if (data.errors.description) {
                        const input = document.getElementById('add_description');
                        input.classList.add('is-invalid');
                        input.nextElementSibling.textContent = data.errors.description;
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || 'An error occurred'
                    });
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Network error occurred'
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
                    timer: 1500,
                    showConfirmButton: false
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
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || 'An error occurred'
                    });
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Network error occurred'
            });
        });
    });

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
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || 'Failed to delete'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Network error occurred'
                });
            });
        }
    });
}
</script>

<?= $this->endSection() ?>
<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<?php 
// Check privileges for this page
$privileges = session('privileges');
$canCreate = isset($privileges['document-type']['can_create']) && $privileges['document-type']['can_create'] == 1;
$canUpdate = isset($privileges['document-type']['can_update']) && $privileges['document-type']['can_update'] == 1;
$canDelete = isset($privileges['document-type']['can_delete']) && $privileges['document-type']['can_delete'] == 1;
?>

<!-- SweetAlert CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container-fluid">
    <div class="bg-white rounded shadow-sm p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Document Type List</h4>
            </div>
            <?php if ($canCreate): ?>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDocumentTypeModal">
                    + Add Document Type
                </button>
            <?php endif; ?>
        </div>
        <div class="table-responsive">
            <div class="border rounded">
                <?php if (!empty($kategori_dokumen)): ?>
                    <table class="table table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="80" class="text-center">No</th>
                                <th>Type Name</th>
                                <th width="150">Code</th>
                                <th width="200" class="text-center">Use Predefined Code</th>
                                <?php if ($canUpdate || $canDelete): ?>
                                    <th width="120" class="text-center">Action</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($kategori_dokumen as $kategori): ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td><?= esc($kategori['nama']) ?></td>
                                    <td><?= esc($kategori['kode']) ?></td>
                                    <td class="text-center">
                                        <?php if ($kategori['use_predefined_codes']): ?>
                                            <span class="badge bg-success">Yes</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">No</span>
                                        <?php endif; ?>
                                    </td>
                                    <?php if ($canUpdate || $canDelete): ?>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            <?php if ($canUpdate): ?>
                                                <button class="btn btn-link p-0 text-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editModal<?= $kategori['id'] ?>"
                                                        title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($canDelete): ?>
                                                <form action="<?= base_url('document-type/delete') ?>" method="post" onsubmit="return confirmDelete(event, this);">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="id" value="<?= $kategori['id'] ?>">
                                                    <button type="submit" class="btn btn-link p-0 text-danger" title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <!-- Edit Modal - Only render if user has update privilege -->
                                <?php if ($canUpdate): ?>
                                <div class="modal fade" id="editModal<?= $kategori['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Document Type</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="post" action="<?= base_url('document-type/edit') ?>">
                                                <div class="modal-body">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="id" value="<?= $kategori['id'] ?>">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <label for="editNama<?= $kategori['id'] ?>" class="form-label">Type Name</label>
                                                            <input type="text" class="form-control text-uppercase-auto" name="nama"
                                                                   id="editNama<?= $kategori['id'] ?>" 
                                                                   value="<?= esc($kategori['nama']) ?>" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="editKode<?= $kategori['id'] ?>" class="form-label">Code</label>
                                                            <input type="text" class="form-control text-uppercase-auto" name="kode"
                                                                   id="editKode<?= $kategori['id'] ?>" 
                                                                   value="<?= esc($kategori['kode']) ?>">
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                   id="editPredefined<?= $kategori['id'] ?>" 
                                                                   name="use_predefined" value="1"
                                                                   <?= $kategori['use_predefined_codes'] ? 'checked' : '' ?>>
                                                            <label class="form-check-label" for="editPredefined<?= $kategori['id'] ?>">
                                                                Use Predefined Code
                                                            </label>
                                                        </div>
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
                                <?php endif; ?>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Document Types Found</h5>
                        <?php if ($canCreate): ?>
                            <p class="text-muted">Click "Add Document Type" to create your first document type.</p>
                        <?php else: ?>
                            <p class="text-muted">No document types available.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal - Only show if user has create privilege -->
<?php if ($canCreate): ?>
<div class="modal fade" id="addDocumentTypeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Document Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="<?= base_url('document-type/add') ?>">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="addNama" class="form-label">Type Name</label>
                            <input type="text" class="form-control text-uppercase-auto" name="nama" id="addNama" required>
                        </div>
                        <div class="col-md-6">
                            <label for="addKode" class="form-label">Code</label>
                            <input type="text" class="form-control text-uppercase-auto" name="kode" id="addKode">
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="addPredefined" name="use_predefined" value="1">
                            <label class="form-check-label" for="addPredefined">
                                Use Predefined Code
                            </label>
                        </div>
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
<?php endif; ?>

<!-- Bootstrap CSS & Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<!-- Custom CSS for uppercase input -->
<style>
    .text-uppercase-auto {
        text-transform: uppercase;
    }
    
    .text-uppercase-auto::placeholder {
        text-transform: none;
    }
</style>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Check privileges from PHP session
    const canCreate = <?= json_encode($canCreate) ?>;
    const canUpdate = <?= json_encode($canUpdate) ?>;
    const canDelete = <?= json_encode($canDelete) ?>;

    // SweetAlert notifications - Load setelah DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        // Check for success messages
        <?php if (session('added_message')): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '<?= session('added_message') ?>',
                confirmButtonText: 'OK'
            });
        <?php endif; ?>

        <?php if (session('updated_message')): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '<?= session('updated_message') ?>',
                confirmButtonText: 'OK'
            });
        <?php endif; ?>

        <?php if (session('deleted_message')): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '<?= session('deleted_message') ?>',
                confirmButtonText: 'OK'
            });
        <?php endif; ?>

        // Check for error messages
        <?php if (session('error')): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '<?= session('error') ?>',
                confirmButtonText: 'OK'
            });
        <?php endif; ?>
    });

    // Only define confirmDelete function if user has delete privilege
    <?php if ($canDelete): ?>
    function confirmDelete(event, form) {
        event.preventDefault();
        
        if (typeof Swal !== 'undefined') {
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
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait a moment',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    form.submit();
                }
            });
        } else {
            // Fallback jika SweetAlert tidak tersedia
            if (confirm('Are you sure you want to delete this document type?')) {
                form.submit();
            }
        }
        
        return false;
    }
    <?php endif; ?>

    // Only add event listeners if user has create privilege
    <?php if ($canCreate): ?>
    // Reset form when modal is closed
    document.getElementById('addDocumentTypeModal').addEventListener('hidden.bs.modal', function () {
        const form = this.querySelector('form');
        form.reset();
        document.getElementById('addPredefined').checked = false;
    });

    // Autofocus on open modals
    document.getElementById('addDocumentTypeModal').addEventListener('shown.bs.modal', function () {
        document.getElementById('addNama').focus();
    });
    <?php endif; ?>

    // Only add event listeners for edit modals if user has update privilege
    <?php if ($canUpdate && !empty($kategori_dokumen)): ?>
    <?php foreach ($kategori_dokumen as $kategori): ?>
    document.getElementById('editModal<?= $kategori['id'] ?>').addEventListener('shown.bs.modal', function () {
        document.getElementById('editNama<?= $kategori['id'] ?>').focus();
    });
    <?php endforeach ?>
    <?php endif; ?>

    $(document).ready(function() {
        $('[title]').tooltip();
        
        // Auto uppercase function for inputs with class 'text-uppercase-auto'
        $(document).on('input', '.text-uppercase-auto', function() {
            const cursorPosition = this.selectionStart;
            const oldLength = this.value.length;
            this.value = this.value.toUpperCase();
            const newLength = this.value.length;
            
            // Restore cursor position
            this.setSelectionRange(cursorPosition + (newLength - oldLength), cursorPosition + (newLength - oldLength));
        });
        
        // Also handle paste events
        $(document).on('paste', '.text-uppercase-auto', function(e) {
            const element = this;
            setTimeout(function() {
                element.value = element.value.toUpperCase();
            }, 1);
        });
    });
</script>
<?= $this->endSection() ?>
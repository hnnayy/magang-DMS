<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<?php
// Ambil privilege dari session untuk submenu ini
$privileges = session()->get('privileges');
$currentSubmenu = 'document-code'; // atau sesuai dengan slug submenu Anda

// Set default privileges jika tidak ada
$canCreate = isset($privileges[$currentSubmenu]['can_create']) ? $privileges[$currentSubmenu]['can_create'] : 0;
$canUpdate = isset($privileges[$currentSubmenu]['can_update']) ? $privileges[$currentSubmenu]['can_update'] : 0;
$canDelete = isset($privileges[$currentSubmenu]['can_delete']) ? $privileges[$currentSubmenu]['can_delete'] : 0;
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Predefined Document Code</h4>
        <?php if ($canCreate): ?>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-plus"></i> Add Document Code
            </button>
        <?php endif; ?>
    </div>

    <!-- Grouping data -->
    <?php 
    $grouped = [];
    foreach ($kode_dokumen as $kode) {
        $kategori = $kode['kategori_nama'];
        if (!isset($grouped[$kategori])) {
            $grouped[$kategori] = [];
        }
        $grouped[$kategori][] = $kode;
    }
    ?>

    <?php foreach ($grouped as $kategori => $documents): ?>
        <div class="mb-4">
            <h5 class="text-primary mb-3"><?= esc($kategori) ?></h5>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px;">No</th>
                            <th style="width: 150px;">Code</th>
                            <th>Document Name</th>
                            <?php if ($canUpdate || $canDelete): ?>
                                <th style="width: 130px;">Action</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($documents as $kode): ?>
                            <tr>
                                <td class="text-center"><?= $no ?></td>
                                <td>
                                    <span class="badge bg-<?= $no % 4 == 1 ? 'warning text-dark' : ($no % 4 == 2 ? 'info' : ($no % 4 == 3 ? 'success' : 'secondary')) ?>">
                                        <?= esc($kode['kode']) ?>
                                    </span>
                                </td>
                                <td><?= esc($kode['nama']) ?></td>
                                <?php if ($canUpdate || $canDelete): ?>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            <?php if ($canUpdate): ?>
                                                <button class="btn btn-link p-0 text-primary" 
                                                        onclick="editDocument(<?= $kode['id'] ?>, '<?= esc($kode['kategori_nama'], 'js') ?>', '<?= esc($kode['kode'], 'js') ?>', '<?= esc($kode['nama'], 'js') ?>', <?= $kode['document_type_id'] ?>)"
                                                        title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($canDelete): ?>
                                                <form action="<?= base_url('document-code/delete') ?>" method="post" onsubmit="return confirmDelete(event, this);">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="id" value="<?= $kode['id'] ?>">
                                                    <button type="submit" class="btn btn-link p-0 text-danger" title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php $no++; endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($grouped)): ?>
        <div class="text-center py-5">
            <div class="mb-3">
                <i class="fas fa-file-alt fa-3x text-muted"></i>
            </div>
            <h5 class="text-muted">No document codes found</h5>
            <?php if ($canCreate): ?>
                <p class="text-muted">Click "Add Document Code" to create your first document code.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Add Modal -->
<?php if ($canCreate): ?>
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Document Code</h5>
            </div>
            <form method="post" action="<?= base_url('document-code/add') ?>" id="addDocumentCodeForm" novalidate>
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="addDocumentType" class="form-label">Document Type</label>
                        <select class="form-select doccode-text-uppercase-auto" id="addDocumentType" name="jenis" required>
                            <option value="">Select Document Type</option>
                            <?php foreach ($kategori_dokumen as $kategori): ?>
                                <?php if (isset($kategori['use_predefined_codes']) && $kategori['use_predefined_codes'] == 1): ?>
                                    <option value="<?= $kategori['id'] ?>"><?= esc(strtoupper($kategori['nama'])) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <div class="doccode-invalid-feedback">
                            Document Type is required.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="addCode" class="form-label">Code</label>
                        <input type="text" class="form-control doccode-text-uppercase-auto" id="addCode" name="kode" required>
                        <div class="doccode-invalid-feedback">
                            Code is required.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="addDocumentName" class="form-label">Document Name</label>
                        <input type="text" class="form-control doccode-text-uppercase-auto" id="addDocumentName" name="nama" required>
                        <div class="doccode-invalid-feedback">
                            Document Name is required.
                        </div>
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
<?php endif; ?>

<!-- Edit Modal -->
<?php if ($canUpdate): ?>
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Document Code</h5>
            </div>
            <form method="post" action="<?= base_url('document-code/edit') ?>" id="editDocumentCodeForm" novalidate>
                <?= csrf_field() ?>
                <div class="modal-body">
                    <input type="hidden" id="editId" name="id">
                    <input type="hidden" id="editDocumentTypeId" name="document_type_id">
                    <div class="mb-3">
                        <label for="editDocumentType" class="form-label">Document Type</label>
                        <input type="text" class="form-control doccode-text-uppercase-auto" id="editDocumentType" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="editCode" class="form-label">Code</label>
                        <input type="text" class="form-control doccode-text-uppercase-auto" id="editCode" name="kode" required>
                        <div class="doccode-invalid-feedback">
                            Code is required.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editDocumentName" class="form-label">Document Name</label>
                        <input type="text" class="form-control doccode-text-uppercase-auto" id="editDocumentName" name="nama" required>
                        <div class="doccode-invalid-feedback">
                            Document Name is required.
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

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Custom form validation function
    function validateDocumentCodeForm(form) {
        let isValid = true;
        const requiredFields = form.querySelectorAll('input[required], select[required]');
        
        requiredFields.forEach(function(field) {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        return isValid;
    }

    <?php if ($canUpdate): ?>
    function editDocument(id, type, code, name, typeId) {
        $('#editId').val(id);
        $('#editDocumentType').val(type.toUpperCase());
        $('#editDocumentTypeId').val(typeId);
        $('#editCode').val(code.toUpperCase());
        $('#editDocumentName').val(name.toUpperCase());
        
        // Remove any existing validation classes
        const editModal = document.getElementById('editModal');
        const inputs = editModal.querySelectorAll('.form-control, .form-select');
        inputs.forEach(function(input) {
            input.classList.remove('is-invalid');
        });
        
        new bootstrap.Modal(document.getElementById('editModal')).show();
    }
    <?php endif; ?>

    <?php if ($canDelete): ?>
    function confirmDelete(event, form) {
        event.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: 'This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: 'rgba(118, 125, 131, 1)',
            confirmButtonText: 'Yes, delete it',
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
        return false;
    }
    <?php endif; ?>

    $(document).ready(function() {
        $('[title]').tooltip();
        
        // Auto uppercase function for inputs with class 'doccode-text-uppercase-auto'
        $(document).on('input', '.doccode-text-uppercase-auto', function() {
            const cursorPosition = this.selectionStart;
            const oldLength = this.value.length;
            this.value = this.value.toUpperCase();
            const newLength = this.value.length;
            
            // Restore cursor position
            this.setSelectionRange(cursorPosition + (newLength - oldLength), cursorPosition + (newLength - oldLength));
        });
        
        // Also handle paste events
        $(document).on('paste', '.doccode-text-uppercase-auto', function(e) {
            const element = this;
            setTimeout(function() {
                element.value = element.value.toUpperCase();
            }, 1);
        });

        <?php if ($canCreate): ?>
        // Add Document Code Form Validation
        document.getElementById('addDocumentCodeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (validateDocumentCodeForm(this)) {
                this.submit();
            }
        });

        // Reset add modal when closed
        document.getElementById('addModal').addEventListener('hidden.bs.modal', function () {
            const form = this.querySelector('form');
            form.reset();
            
            // Remove validation classes
            const inputs = form.querySelectorAll('.form-control, .form-select');
            inputs.forEach(function(input) {
                input.classList.remove('is-invalid');
            });
        });

        // Autofocus on add modal open
        document.getElementById('addModal').addEventListener('shown.bs.modal', function () {
            document.getElementById('addDocumentType').focus();
        });

        // Real-time validation for add form
        document.getElementById('addDocumentType').addEventListener('change', function() {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
            }
        });

        document.getElementById('addCode').addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
            }
        });

        document.getElementById('addDocumentName').addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
            }
        });
        <?php endif; ?>

        <?php if ($canUpdate): ?>
        // Edit Document Code Form Validation
        document.getElementById('editDocumentCodeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (validateDocumentCodeForm(this)) {
                this.submit();
            }
        });

        // Autofocus on edit modal open
        document.getElementById('editModal').addEventListener('shown.bs.modal', function () {
            document.getElementById('editCode').focus();
        });

        // Real-time validation for edit form
        document.getElementById('editCode').addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
            }
        });

        document.getElementById('editDocumentName').addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
            }
        });
        <?php endif; ?>

        // Display flash messages
        <?php if (session()->has('added_message')): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '<?= esc(session()->getFlashdata('added_message')) ?>',
                showConfirmButton: true,
                timer: 3000
            });
        <?php endif; ?>

        <?php if (session()->has('updated_message')): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '<?= esc(session()->getFlashdata('updated_message')) ?>',
                showConfirmButton: true,
                timer: 3000
            });
        <?php endif; ?>

        <?php if (session()->has('deleted_message')): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '<?= esc(session()->getFlashdata('deleted_message')) ?>',
                showConfirmButton: true,
                timer: 3000
            });
        <?php endif; ?>

        <?php if (session()->has('error')): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?= esc(session()->getFlashdata('error')) ?>',
                showConfirmButton: true,
                timer: 5000
            });
        <?php endif; ?>
    });
</script>

<?= $this->endSection() ?>
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
        <h2>Predefined Document Code</h2>
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
                                            <?php if ($canDelete): ?>
                                                <form action="<?= base_url('document-code/delete') ?>" method="post" onsubmit="return confirmDelete(event, this);">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="id" value="<?= $kode['id'] ?>">
                                                    <button type="submit" class="btn btn-link p-0 text-danger" title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if ($canUpdate): ?>
                                                <button class="btn btn-link p-0 text-primary" 
                                                        onclick="editDocument(<?= $kode['id'] ?>, '<?= esc($kode['kategori_nama'], 'js') ?>', '<?= esc($kode['kode'], 'js') ?>', '<?= esc($kode['nama'], 'js') ?>', <?= $kode['document_type_id'] ?>)"
                                                        title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
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
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="<?= base_url('document-code/add') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="addDocumentType" class="form-label">Document Type</label>
                        <select class="form-select" id="addDocumentType" name="jenis" required>
                            <option value="">Select Document Type</option>
                            <?php foreach ($kategori_dokumen as $kategori): ?>
                                <option value="<?= $kategori['id'] ?>"><?= esc($kategori['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="addCode" class="form-label">Code</label>
                        <input type="text" class="form-control" id="addCode" name="kode" required>
                    </div>
                    <div class="mb-3">
                        <label for="addDocumentName" class="form-label">Document Name</label>
                        <input type="text" class="form-control" id="addDocumentName" name="nama" required>
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
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="<?= base_url('document-code/edit') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <input type="hidden" id="editId" name="id">
                    <input type="hidden" id="editDocumentTypeId" name="document_type_id">
                    <div class="mb-3">
                        <label for="editDocumentType" class="form-label">Document Type</label>
                        <input type="text" class="form-control" id="editDocumentType" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="editCode" class="form-label">Code</label>
                        <input type="text" class="form-control" id="editCode" name="kode" required>
                    </div>
                    <div class="mb-3">
                        <label for="editDocumentName" class="form-label">Document Name</label>
                        <input type="text" class="form-control" id="editDocumentName" name="nama" required>
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
    <?php if ($canUpdate): ?>
    function editDocument(id, type, code, name, typeId) {
        $('#editId').val(id);
        $('#editDocumentType').val(type);
        $('#editDocumentTypeId').val(typeId);
        $('#editCode').val(code);
        $('#editDocumentName').val(name);
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
    });
</script>

<?= $this->endSection() ?>
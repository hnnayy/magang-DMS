<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<!-- Bootstrap CSS & Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<div class="px-4 py-3 w-100">
    <h4>Document Type & Code Configuration</h4>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '<?= session()->getFlashdata('success') ?>',
                confirmButtonText: 'OK',
                showConfirmButton: true
            });
        });
    </script>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '<?= session()->getFlashdata('error') ?>',
                confirmButtonText: 'OK',
                showConfirmButton: true
            });
        });
    </script>
    <?php endif; ?>

    <!-- Tabel Jenis Dokumen -->
    <div class="table-responsive shadow-sm rounded bg-white p-3 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Document Type List</h5>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addKategoriModal">
                <i class="bi bi-plus-lg"></i> Add Document Type
            </button>
        </div>

        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th class="text-center">No</th>
                    <th>Type Name</th>
                    <th>Code</th>
                    <th class="text-center">Use Predefined Code</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($kategori_dokumen as $i => $kategori): ?>
                <tr>
                    <td class="text-center"><?= $i + 1 ?></td>
                    <td><?= esc($kategori['nama']) ?></td>
                    <td><?= esc($kategori['kode']) ?></td>
                    <td class="text-center">
                        <?php if($kategori['use_predefined_codes']): ?>
                            <span class="badge bg-success">Yes</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">No</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-2">
                            <a href="#" class="text-primary" 
                               onclick="editKategori(<?= $kategori['id'] ?>, '<?= esc($kategori['nama']) ?>', '<?= esc($kategori['kode']) ?>', <?= $kategori['use_predefined_codes'] ? 'true' : 'false' ?>)"
                               title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <a href="#" class="text-danger" 
                               onclick="deleteKategori(<?= $kategori['id'] ?>, '<?= esc($kategori['nama']) ?>')"
                               title="Delete">
                                <i class="bi bi-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Tabel Kode-Nama Dokumen -->
    <div class="table-responsive shadow-sm rounded bg-white p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Predefined Document Code-Name List</h5>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addKodeModal">
                <i class="bi bi-plus-lg"></i> Add Document Code
            </button>
        </div>

        <?php if (!empty($kode_dokumen)): ?>
            <?php foreach ($kode_dokumen as $jenis => $list): ?>
                <div class="mb-4">
                    <h6 class="text-primary fw-bold mb-3"><?= strtoupper($jenis) ?></h6>
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">No</th>
                                <th>Code</th>
                                <th>Document Name</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($list as $i => $item): ?>
                                <tr>
                                    <td class="text-center"><?= $i + 1 ?></td>
                                    <td><?= esc($item['kode']) ?></td>
                                    <td><?= esc($item['nama']) ?></td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="#" class="text-primary" 
                                               onclick="editKode(<?= $item['id'] ?>, '<?= esc($item['kode']) ?>', '<?= esc($item['nama']) ?>', '<?= $jenis ?>')"
                                               title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="#" class="text-danger" 
                                               onclick="deleteKode(<?= $item['id'] ?>, '<?= esc($item['kode']) ?>')"
                                               title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted text-center py-4">No document codes available.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Add Kategori -->
<div class="modal fade" id="addKategoriModal" tabindex="-1" aria-labelledby="addKategoriModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="addKategoriForm" action="<?= base_url('admin/dokumen/add-kategori') ?>" method="POST" class="needs-validation" novalidate>
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="addKategoriModalLabel">Add Document Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Type Name</label>
                        <input type="text" class="form-control" name="nama" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()" required>
                        <div class="invalid-feedback">Please enter type name.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Code</label>
                        <input type="text" class="form-control" name="kode" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()" required>
                        <div class="invalid-feedback">Please enter code.</div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="use_predefined_codes" value="1" id="usePredefined">
                            <label class="form-check-label" for="usePredefined">
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

<!-- Modal Edit Kategori -->
<div class="modal fade" id="editKategoriModal" tabindex="-1" aria-labelledby="editKategoriModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="editKategoriForm" action="<?= base_url('admin/dokumen/edit-kategori') ?>" method="POST" class="needs-validation" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="editKategoriId">
                <div class="modal-header">
                    <h5 class="modal-title" id="editKategoriModalLabel">Edit Document Type</h5>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Type Name</label>
                        <input type="text" class="form-control" id="editKategoriNama" name="nama" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()" required>
                        <div class="invalid-feedback">Please enter type name.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Code</label>
                        <input type="text" class="form-control" name="kode" id="editKategoriKode" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()" required>
                        <div class="invalid-feedback">Please enter code.</div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="use_predefined_codes" value="1" id="editUsePredefined">
                            <label class="form-check-label" for="editUsePredefined">
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

<!-- Modal Add Kode -->
<div class="modal fade" id="addKodeModal" tabindex="-1" aria-labelledby="addKodeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="addKodeForm" action="<?= base_url('admin/dokumen/add-kode') ?>" method="POST" class="needs-validation" novalidate>
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="addKodeModalLabel">Add Document Code</h5>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Document Type</label>
                        <select class="form-select" name="jenis" required>
                            <option value="">Select Document Type</option>
                            <?php foreach ($kategori_dokumen as $kategori): ?>
                                <option value="<?= $kategori['id'] ?>"><?= esc($kategori['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Please select document type.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Code</label>
                        <input type="text" class="form-control" name="kode" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()" required>
                        <div class="invalid-feedback">Please enter code.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Document Name</label>
                        <input type="text" class="form-control" name="nama" required>
                        <div class="invalid-feedback">Please enter document name.</div>
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

<!-- Modal Edit Kode -->
<div class="modal fade" id="editKodeModal" tabindex="-1" aria-labelledby="editKodeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="editKodeForm" action="<?= base_url('admin/dokumen/edit-kode') ?>" method="POST" class="needs-validation" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="editKodeId">
                <div class="modal-header">
                    <h5 class="modal-title" id="editKodeModalLabel">Edit Document Code</h5>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Document Type</label>
                        <input type="text" class="form-control" id="editKodeJenisNama" readonly>
                        <input type="hidden" name="jenis" id="editKodeJenisValue">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Code</label>
                        <input type="text" class="form-control" name="kode" id="editKodeKode" placeholder="Enter code..." oninput="this.value = this.value.toUpperCase()" required>
                        <div class="invalid-feedback">Please enter code.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Document Name</label>
                        <input type="text" class="form-control" name="nama" id="editKodeNama" placeholder="Enter document name..." required>
                        <div class="invalid-feedback">Please enter document name.</div>
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

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });

        // Form validation for all modals
        setupFormValidation('addKategoriForm');
        setupFormValidation('editKategoriForm');
        setupFormValidation('addKodeForm');
        setupFormValidation('editKodeForm');
    });

    function setupFormValidation(formId) {
        const form = document.getElementById(formId);
        if (!form) return;

        form.addEventListener('submit', function(event) {
            event.preventDefault();
            event.stopPropagation();

            let isValid = form.checkValidity();

            if (isValid) {
                // If form is valid, submit it
                form.classList.remove('was-validated');
                form.submit();
            } else {
                // Show validation errors
                form.classList.add('was-validated');
            }
        }, false);

        // Real-time validation for inputs
        const inputs = form.querySelectorAll('input[required], select[required]');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                if (this.value.trim()) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                    if (form.classList.contains('was-validated')) {
                        this.classList.add('is-invalid');
                    }
                }
            });

            // For select elements
            input.addEventListener('change', function() {
                if (this.value) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                    if (form.classList.contains('was-validated')) {
                        this.classList.add('is-invalid');
                    }
                }
            });
        });

        // Reset validation when modal is closed
        const modal = form.closest('.modal');
        if (modal) {
            modal.addEventListener('hidden.bs.modal', function() {
                form.classList.remove('was-validated');
                form.querySelectorAll('.is-invalid, .is-valid').forEach(el => {
                    el.classList.remove('is-invalid', 'is-valid');
                });
                form.reset();
            });
        }
    }

    function editKategori(id, nama, kode, usePredefined) {
        document.getElementById('editKategoriId').value = id;
        document.getElementById('editKategoriNama').value = nama.toUpperCase();
        document.getElementById('editKategoriKode').value = kode;
        document.getElementById('editUsePredefined').checked = usePredefined;
        
        // Reset validation state
        const form = document.getElementById('editKategoriForm');
        form.classList.remove('was-validated');
        form.querySelectorAll('.is-invalid, .is-valid').forEach(el => {
            el.classList.remove('is-invalid', 'is-valid');
        });
        
        var modal = new bootstrap.Modal(document.getElementById('editKategoriModal'));
        modal.show();
    }

    function deleteKategori(id, nama) {
        Swal.fire({
            title: 'Yakin ingin menghapus?',
            text: 'Jenis dokumen "' + nama + '" akan dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?= base_url('admin/dokumen/delete-kategori') ?>';

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '<?= csrf_token() ?>';
                csrfInput.value = '<?= csrf_hash() ?>';
                form.appendChild(csrfInput);

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id';
                idInput.value = id;
                form.appendChild(idInput);

                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function editKode(id, kode, nama, jenisNama, jenisId) {
        document.getElementById('editKodeId').value = id;
        document.getElementById('editKodeKode').value = kode;
        document.getElementById('editKodeNama').value = nama;
        document.getElementById('editKodeJenisNama').value = jenisNama.toUpperCase(); 
        document.getElementById('editKodeJenisValue').value = jenisId;  

        // Reset validation state
        const form = document.getElementById('editKodeForm');
        form.classList.remove('was-validated');
        form.querySelectorAll('.is-invalid, .is-valid').forEach(el => {
            el.classList.remove('is-invalid', 'is-valid');
        });

        var modal = new bootstrap.Modal(document.getElementById('editKodeModal'));
        modal.show();
    }

    function deleteKode(id, kode) {
        Swal.fire({
            title: 'Yakin ingin menghapus?',
            text: 'Kode dokumen "' + kode + '" akan dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?= base_url('admin/dokumen/delete-kode') ?>';

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '<?= csrf_token() ?>';
                csrfInput.value = '<?= csrf_hash() ?>';
                form.appendChild(csrfInput);

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id';
                idInput.value = id;
                form.appendChild(idInput);

                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>

<?= $this->endSection() ?>
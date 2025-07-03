<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<!-- Bootstrap CSS & Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<div class="px-4 py-3 w-100">
    <h4>Konfigurasi Jenis & Kode Dokumen</h4>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Tabel Jenis Dokumen -->
    <div class="table-responsive shadow-sm rounded bg-white p-3 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Daftar Jenis Dokumen</h5>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addKategoriModal">
                <i class="bi bi-plus-lg"></i> Tambah Jenis Dokumen
            </button>
        </div>

        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th class="text-center">No</th>
                    <th>Nama Jenis</th>
                    <th>Kode</th>
                    <th class="text-center">Gunakan Kode Predefined</th>
                    <th class="text-center">Aksi</th>
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
                            <span class="badge bg-success">Ya</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Tidak</span>
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
                               title="Hapus">
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
            <h5 class="mb-0">Daftar Kode-Nama Dokumen Predefined</h5>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addKodeModal">
                <i class="bi bi-plus-lg"></i> Tambah Kode Dokumen
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
                                <th>Kode</th>
                                <th>Nama Dokumen</th>
                                <th class="text-center">Aksi</th>
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
            <p class="text-muted text-center py-4">Belum ada kode dokumen yang tersedia.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Add Kategori -->
<div class="modal fade" id="addKategoriModal" tabindex="-1" aria-labelledby="addKategoriModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= base_url('admin/dokumen/add-kategori') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="addKategoriModalLabel">Tambah Jenis Dokumen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Jenis</label>
                            <input type="text" class="form-control" name="nama" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kode</label>
                            <input type="text" class="form-control" name="kode" required>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="use_predefined_codes" value="1" id="usePredefined">
                                <label class="form-check-label" for="usePredefined">
                                    Gunakan Kode Predefined
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Kategori -->
<div class="modal fade" id="editKategoriModal" tabindex="-1" aria-labelledby="editKategoriModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= base_url('admin/dokumen/edit-kategori') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="editKategoriId">
                <div class="modal-header">
                    <h5 class="modal-title" id="editKategoriModalLabel">Edit Jenis Dokumen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Jenis</label>
                            <input type="text" class="form-control" name="nama" id="editKategoriNama" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kode</label>
                            <input type="text" class="form-control" name="kode" id="editKategoriKode" required>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="use_predefined_codes" value="1" id="editUsePredefined">
                                <label class="form-check-label" for="editUsePredefined">
                                    Gunakan Kode Predefined
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Add Kode -->
<div class="modal fade" id="addKodeModal" tabindex="-1" aria-labelledby="addKodeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= base_url('admin/dokumen/add-kode') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="addKodeModalLabel">Tambah Kode Dokumen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Jenis Dokumen</label>
                            <select class="form-select" name="jenis" required>
                                <option value="">Pilih Jenis Dokumen</option>
                                <?php foreach ($kategori_dokumen as $kategori): ?>
                                    <option value="<?= $kategori['kode'] ?>"><?= esc($kategori['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kode</label>
                            <input type="text" class="form-control" name="kode" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nama Dokumen</label>
                            <input type="text" class="form-control" name="nama" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Kode -->
<div class="modal fade" id="editKodeModal" tabindex="-1" aria-labelledby="editKodeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= base_url('admin/dokumen/edit-kode') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="editKodeId">
                <div class="modal-header">
                    <h5 class="modal-title" id="editKodeModalLabel">Edit Kode Dokumen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Jenis Dokumen</label>
                            <select class="form-select" name="jenis" id="editKodeJenis" required>
                                <option value="">Pilih Jenis Dokumen</option>
                                <?php foreach ($kategori_dokumen as $kategori): ?>
                                    <option value="<?= $kategori['kode'] ?>"><?= esc($kategori['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kode</label>
                            <input type="text" class="form-control" name="kode" id="editKodeKode" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nama Dokumen</label>
                            <input type="text" class="form-control" name="nama" id="editKodeNama" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});

// Functions for Kategori
function editKategori(id, nama, kode, usePredefined) {
    document.getElementById('editKategoriId').value = id;
    document.getElementById('editKategoriNama').value = nama;
    document.getElementById('editKategoriKode').value = kode;
    document.getElementById('editUsePredefined').checked = usePredefined;
    
    var modal = new bootstrap.Modal(document.getElementById('editKategoriModal'));
    modal.show();
}

function deleteKategori(id, nama) {
    if (confirm('Yakin ingin menghapus jenis dokumen "' + nama + '"?')) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= base_url('admin/dokumen/delete-kategori') ?>';
        
        var csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '<?= csrf_token() ?>';
        csrfInput.value = '<?= csrf_hash() ?>';
        form.appendChild(csrfInput);
        
        var idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = id;
        form.appendChild(idInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Functions for Kode
function editKode(id, kode, nama, jenis) {
    document.getElementById('editKodeId').value = id;
    document.getElementById('editKodeKode').value = kode;
    document.getElementById('editKodeNama').value = nama;
    document.getElementById('editKodeJenis').value = jenis;
    
    var modal = new bootstrap.Modal(document.getElementById('editKodeModal'));
    modal.show();
}

function deleteKode(id, kode) {
    if (confirm('Yakin ingin menghapus kode dokumen "' + kode + '"?')) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= base_url('admin/dokumen/delete-kode') ?>';
        
        var csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '<?= csrf_token() ?>';
        csrfInput.value = '<?= csrf_hash() ?>';
        form.appendChild(csrfInput);
        
        var idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = id;
        form.appendChild(idInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?= $this->endSection() ?>
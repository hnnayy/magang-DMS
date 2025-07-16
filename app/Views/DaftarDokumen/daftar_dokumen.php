<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<!-- CSS External Links -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

<!-- Custom CSS -->
<link rel="stylesheet" href="<?= base_url('assets/css/daftar-dokumen.css') ?>">

<div class="container-fluid px-4 py-4">
    <h4 class="mb-4">Daftar Dokumen</h4>

    <!-- FILTER -->
    <div class="bg-light p-3 rounded mb-4 d-flex flex-wrap align-items-center gap-2">
        <strong class="form-label mb-0 me-2">Filter Data</strong>

        <div style="min-width:180px;">
            <select class="form-select filter-input" id="filterStandar">
                <option value="">Semua Standar</option>
                <?php foreach ($standards as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= $s['nama_standar'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="min-width:180px;">
            <select class="form-select filter-input" id="filterKlausul">
                <option value="">Semua Klausul</option>
                <?php foreach ($clauses as $c): ?>
                    <option value="<?= $c['id'] ?>">
                        <?= $c['nomor_klausul'] ?> - <?= $c['nama_klausul'] ?> (<?= $c['nama_standar'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="min-width:180px;">
            <select class="form-select filter-input" id="filterPemilik">
                <option value="">Semua Pemilik Doc</option>
            </select>
        </div>

        <div style="min-width:180px;">
            <select class="form-select filter-input" id="filterJenis">
                <option value="">Semua Jenis Doc</option>
                <?php foreach ($kategori_dokumen as $k): ?>
                    <option value="<?= $k['id'] ?>"><?= $k['name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <button class="btn btn-primary" id="btnFilter">Filter</button>

        <form action="<?= base_url('daftar-dokumen/export-excel') ?>" method="post" class="ms-auto">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-success">
                <i class="bi bi-file-earmark-excel"></i> Excel
            </button>
        </form>
    </div>

    <div class="card">
        <div class="card-body">
            <!-- Container untuk tombol export dan search -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="dt-buttons-container gap-2"></div>
                <div class="dt-search-container"></div>
            </div>

            <!-- TABEL -->
            <div class="table-responsive">
                <table id="dokumenTable" class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Standar</th>
                            <th>Klausul</th>
                            <th>Jenis Dokumen</th>
                            <th>No Dokumen</th>
                            <th>Nomor</th>
                            <th>Nama Dokumen</th>
                            <th>Pemilik Dokumen</th>
                            <th>File Dokumen</th>
                            <th>Revisi</th>
                            <th>Tanggal Efektif</th>
                            <th>Disetujui Oleh</th>
                            <th>Tanggal Disetujui</th>
                            <th>Last Update</th>
                            <th class="aksi-column">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($document as $row): ?>
                            <tr>
                                <td>
                                    <select class="form-select multiple-standar" name="standar[]" multiple>
                                        <?php foreach ($standards as $s): ?>
                                            <option value="<?= $s['id'] ?>"><?= $s['nama_standar'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <select class="form-select multiple-klausul" name="klausul[]" multiple>
                                        <?php foreach ($clauses as $c): ?>
                                            <option value="<?= $c['id'] ?>"><?= $c['nomor_klausul'] ?> - <?= $c['nama_klausul'] ?> (<?= $c['nama_standar'] ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><?= esc($row['jenis_dokumen'] ?? '-') ?></td>
                                <td><?= esc($row['kode_jenis_dokumen'] ?? '-') ?></td>
                                <td><?= esc($row['number'] ?? '-') ?></td>
                                <td><?= esc($row['title'] ?? '-') ?></td>
                                <td><?= esc('-') ?></td>
                                <td>
                                    <?php if (!empty($row['filepath'])): ?>
                                        <a href="<?= base_url('uploads/' . $row['filepath']) ?>" target="_blank">Download</a>
                                    <?php else: ?> - <?php endif; ?>
                                </td>
                                <td><?= esc($row['revision'] ?? '-') ?></td>
                                <td><?= esc($row['date_published'] ?? '-') ?></td>
                                <td><?= esc($row['approveby'] ?? '-') ?></td>
                                <td><?= esc($row['approvedate'] ?? '-') ?></td>
                                <td><?= esc($row['updated_at'] ?? '-') ?></td>
                                <td class="aksi-column">
                                    <a href="#" class="text-warning me-2" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="<?= base_url('daftar-dokumen/delete/' . $row['id']) ?>" method="post" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm text-danger" onclick="return confirm('Yakin ingin menghapus dokumen ini?')" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Modal Edit Dokumen -->
                            <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $row['id'] ?>" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <form method="post" action="<?= base_url('daftar-dokumen/update') ?>" enctype="multipart/form-data">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <div class="modal-content">
                                            <div class="modal-header border-0">
                                                <h6 class="modal-title fw-semibold">Edit Dokumen</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                            </div>
                                            <div class="modal-body px-4 py-3">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label small">Jenis Dokumen</label>
                                                        <select name="type" class="form-select form-select-sm">
                                                            <?php foreach ($kategori_dokumen as $kategori): ?>
                                                                <option value="<?= $kategori['id'] ?>" <?= ($row['type'] == $kategori['id']) ? 'selected' : '' ?>>
                                                                    <?= $kategori['name'] ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small">Kode Jenis</label>
                                                        <input type="text" class="form-control form-control-sm" name="kode_jenis_dokumen" value="<?= esc($row['kode_jenis_dokumen']) ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small">Nomor</label>
                                                        <input type="text" class="form-control form-control-sm" name="number" value="<?= esc($row['number']) ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small">Nama Dokumen</label>
                                                        <input type="text" class="form-control form-control-sm" name="title" value="<?= esc($row['title']) ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small">Pemilik</label>
                                                        <input type="text" class="form-control form-control-sm" name="pemilik" value="<?= esc($row['pemilik'] ?? '-') ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small">Ganti File</label>
                                                        <input type="file" class="form-control form-control-sm" name="file">
                                                        <?php if (!empty($row['filepath'])): ?>
                                                            <small class="text-muted">Saat ini: <?= esc($row['filepath']) ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small">Revisi</label>
                                                        <input type="text" class="form-control form-control-sm" name="revision" value="<?= esc($row['revision']) ?>">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small">Tanggal Efektif</label>
                                                        <input type="date" class="form-control form-control-sm" name="date_published" value="<?= esc($row['date_published']) ?>">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small">Disetujui Oleh</label>
                                                        <input type="text" class="form-control form-control-sm" name="approveby" value="<?= esc($row['approveby'] ?? '-') ?>">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small">Tanggal Disetujui</label>
                                                        <input type="datetime-local" class="form-control form-control-sm" name="approvedate" value="<?= esc($row['approvedate'] ?? '') ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small">Last Update</label>
                                                        <input type="datetime-local" class="form-control form-control-sm" name="updated_at" value="<?= esc($row['updated_at'] ?? '') ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0 px-4 pb-3">
                                                <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- JS External Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<!-- Custom JS -->
<script>
$(document).ready(function() {
    // Initialize Choices.js
    const standarSelects = document.querySelectorAll('.multiple-standar');
    const klausulSelects = document.querySelectorAll('.multiple-klausul');
    standarSelects.forEach(select => new Choices(select, { removeItemButton: true }));
    klausulSelects.forEach(select => new Choices(select, { removeItemButton: true }));

    // Initialize DataTables
    const table = $('#dokumenTable').DataTable({
        dom: 'rt<"d-flex justify-content-between align-items-center mt-3"<"d-flex align-items-center"l>p>',
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        language: {
            lengthMenu: "Show _MENU_ entries",
            paginate: {
                previous: "Previous",
                next: "Next"
            }
        },
        columnDefs: [
            { orderable: false, targets: [0, 1, -1] } // Disable sorting for select columns and action column
        ]
    });

    // Move export buttons to container
    table.buttons().container().appendTo('.dt-buttons-container');
    
    // Create custom search box
    const searchHtml = `
        <div class="d-flex align-items-center">
            <label class="me-2 mb-0">Search:</label>
            <input type="search" class="form-control form-control-sm" id="customSearch" style="width: 200px;">
        </div>
    `;
    $('.dt-search-container').html(searchHtml);
    
    // Connect custom search with DataTables
    $('#customSearch').on('keyup', function() {
        table.search(this.value).draw();
    });

    // Filter functionality
    $('#btnFilter').on('click', function() {
        // Add your filter logic here
        console.log('Filter clicked');
        // You can extend this to implement custom filtering
    });
});
</script>

<?= $this->endSection() ?>

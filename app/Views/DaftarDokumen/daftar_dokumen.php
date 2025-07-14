<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<!-- CSS Choices.js & Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<style>
    .choices__inner,
    .choices__list--multiple .choices__item {
        font-size: 12px;
    }

    .choices__list--dropdown .choices__item {
        font-size: 12px;
        white-space: normal;
        word-break: break-word;
    }

    .choices__list--dropdown {
        max-height: 200px;
        overflow-y: auto;
    }

    .form-select[multiple] {
        min-height: 38px;
    }

    .choices[data-type*=select-multiple] {
        min-width: 220px;
    }

    .choices__inner::after {
        content: '';
        position: absolute;
        top: 50%;
        right: 12px;
        border-style: solid;
        border-width: 6px 4px 0 4px;
        border-color: #999 transparent transparent transparent;
        transform: translateY(-50%);
    }

    .aksi-column,
    td.aksi-column {
        min-width: 90px;
        text-align: center;
        white-space: nowrap;
    }

    .filter-input {
        font-size: 14px;
    }
</style>

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
            <tbody id="dokumenBody">
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
                    <!-- Modal Edit Dokumen -->
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
                            <input type="text" class="form-control form-control-sm" name="approveby" value="-">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Tanggal Disetujui</label>
                            <input type="datetime-local" class="form-control form-control-sm" name="approvedate" value="-">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Last Update</label>
                            <input type="datetime-local" class="form-control form-control-sm" name="updated_at" value="-">
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

    <!-- Pagination & Show Entries -->
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div>
            <label>Show 
                <select id="entriesPerPage" class="form-select d-inline-block" style="width: auto;">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="9999">All</option>
                </select> entries
            </label>
        </div>
        <div id="pagination" class="btn-group"></div>
    </div>
</div>

<!-- JS Choices.js + Pagination Manual -->
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const standarSelects = document.querySelectorAll('.multiple-standar');
    const klausulSelects = document.querySelectorAll('.multiple-klausul');
    standarSelects.forEach(select => new Choices(select, { removeItemButton: true }));
    klausulSelects.forEach(select => new Choices(select, { removeItemButton: true }));

    const tableBody = document.getElementById('dokumenBody');
    const rows = Array.from(tableBody.querySelectorAll('tr'));
    const entriesSelect = document.getElementById('entriesPerPage');
    const paginationContainer = document.getElementById('pagination');
    let currentPage = 1;
    let rowsPerPage = parseInt(entriesSelect.value);

    function renderTable() {
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        tableBody.innerHTML = '';
        rows.slice(start, end).forEach(row => tableBody.appendChild(row));
    }

    function renderPagination() {
        paginationContainer.innerHTML = '';
        const pageCount = Math.ceil(rows.length / rowsPerPage);
        for (let i = 1; i <= pageCount; i++) {
            const btn = document.createElement('button');
            btn.textContent = i;
            btn.className = 'btn btn-sm btn-outline-primary mx-1';
            if (i === currentPage) btn.classList.add('active');
            btn.addEventListener('click', () => {
                currentPage = i;
                renderTable();
                renderPagination();
            });
            paginationContainer.appendChild(btn);
        }
    }

    entriesSelect.addEventListener('change', () => {
        rowsPerPage = parseInt(entriesSelect.value);
        currentPage = 1;
        renderTable();
        renderPagination();
    });

    renderTable();
    renderPagination();
});
</script>

<?= $this->endSection() ?>

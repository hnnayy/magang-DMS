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
            <select class="form-select filter-input" id="filterStandar" multiple>
                <?php foreach ($standards as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= $s['nama_standar'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="min-width:180px;">
            <select class="form-select filter-input" id="filterKlausul" multiple>
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
                <?php 
                // Get unique document owners from the document data
                $unique_owners = array_unique(array_filter(array_column($document, 'createdby')));
                foreach ($unique_owners as $owner): 
                ?>
                    <option value="<?= esc($owner) ?>"><?= esc($owner) ?></option>
                <?php endforeach; ?>
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

        <div class="d-flex gap-2">
            <button class="btn btn-primary btn-sm-2" id="btnFilter">Filter</button>
            <button class="btn btn-success btn-sm-2" id="excel-button-container">Export Excel</button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <!-- Container untuk tombol export, show entries, dan search -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="dt-buttons-container"></div>
                    <div class="dt-length-container"></div>
                </div>
                <div class="dt-search-container"></div>
            </div>

            <!-- TABEL dengan wrapper untuk sticky pagination -->
            <div class="table-wrapper position-relative">
                <div class="table-responsive">
                    <div class="datatable-info-container mt-2"></div>
                    <table id="dokumenTable" class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>Standar</th>
                                <th>Klausul</th>
                                <th>Jenis Dokumen</th>
                                <th>Kode & Nama Dokumen</th>
                                <th>Nomor Dokumen</th>
                                <th>Nama Dokumen</th>
                                <th>Pemilik Dokumen</th>
                                <th>File Dokumen</th>
                                <th>Revisi</th>
                                <th>Tanggal Efektif</th>
                                <th>Disetujui Oleh</th>
                                <th>Tanggal Disetujui</th>
                                <th class="aksi-column">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($document as $row): ?>
                                <tr data-standar="<?= implode(',', array_column(array_filter($standards, function($s) use ($row) { return in_array($s['id'], explode(',', $row['standar_ids'] ?? '')); }), 'id')) ?>" 
                                    data-klausul="<?= implode(',', array_column(array_filter($clauses, function($c) use ($row) { return in_array($c['id'], explode(',', $row['klausul_ids'] ?? '')); }), 'id')) ?>"
                                    data-pemilik="<?= esc($row['createdby'] ?? '') ?>">
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
                                    <td><?php if (!empty($row['kode_dokumen_kode']) && !empty($row['kode_dokumen_nama'])): ?>
                                        <div>
                                            <?= esc($row['kode_dokumen_kode']) ?> - <?= esc($row['kode_dokumen_nama']) ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?></td>
                                    <td><?= esc($row['number'] ?? '-') ?></td>
                                    <td><?= esc($row['title'] ?? '-') ?></td>
                                    <td><?= esc($row['createdby'] ?? '-') ?></td>
                                    <td>
                                        <?php if (!empty($row['filepath'])): ?>
                                            <a href="<?= base_url('uploads/' . $row['filepath']) ?>" target="_blank" class="text-decoration-none">
                                                <i class="bi bi-file-earmark-text text-primary"></i> 
                                                <span class="text-truncate d-inline-block" style="max-width: 100px;">
                                                    <?= esc($row['filename'] ?? $row['filepath']) ?>
                                                </span>
                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($row['revision'] ?? '-') ?></td>
                                    <td><?= esc($row['date_published'] ?? '-') ?></td>
                                    <td><?= esc($row['approved_by_name'] ?? '-') ?></td>
                                    <td><?= esc($row['approvedate'] ?? '-') ?></td>
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
                                                            <label class="form-label small">Pemilik Dokumen</label>
                                                            <input type="text" class="form-control form-control-sm" name="createdby" value="<?= esc($row['createdby'] ?? '') ?>">
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
                                                            <input type="text" class="form-control form-control-sm" name="approveby" value="<?= esc($row['approved_by_name'] ?? '') ?>">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label small">Tanggal Disetujui</label>
                                                            <input type="datetime-local" class="form-control form-control-sm" name="approvedate" value="<?= esc(date('Y-m-d\TH:i', strtotime($row['approvedate'] ?? 'now'))) ?>">
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
                
                <!-- Pagination container yang akan di-sticky -->
                <div class="pagination-container"></div>
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
    // Initialize Choices.js for table selects
    const standarSelects = document.querySelectorAll('.multiple-standar');
    const klausulSelects = document.querySelectorAll('.multiple-klausul');
    standarSelects.forEach(select => new Choices(select, { removeItemButton: true }));
    klausulSelects.forEach(select => new Choices(select, { removeItemButton: true }));

    // Initialize Choices.js for filter selects
    const filterStandar = new Choices('#filterStandar', {
        removeItemButton: true,
        placeholder: true,
        placeholderValue: 'Pilih Standar...'
    });
    
    const filterKlausul = new Choices('#filterKlausul', {
        removeItemButton: true,
        placeholder: true,
        placeholderValue: 'Pilih Klausul...'
    });

    // Initialize DataTables
    const table = $('#dokumenTable').DataTable({
        dom: 'rt<"d-flex justify-content-between align-items-center mt-3"<"d-flex align-items-center"l><"pagination-wrapper"p>>',
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
            { orderable: false, targets: [0, 1, -1] }
        ],
        buttons: [
            {
                extend: 'excel',
                title: 'Daftar_Dokumen',
                exportOptions: { 
                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]
                }
            }
        ],
        drawCallback: function() {
            const paginationHtml = $('.dataTables_paginate').html();
            if (paginationHtml) {
                $('.pagination-container').html('<div class="dataTables_paginate">' + paginationHtml + '</div>');
                $('.dataTables_paginate').not('.pagination-container .dataTables_paginate').hide();
            }

            $('.dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_info, .dataTables_wrapper .dataTables_paginate').css({
                'position': 'sticky',
                'left': '0',
                'background': 'white',
                'z-index': '10'
            });
        }
    });

    // Move export buttons to container
    table.buttons().container().appendTo('.dt-buttons-container');
    
    // Pindahkan tombol Excel ke container di sebelah Filter
    const excelButton = $('.dt-buttons-container .buttons-excel').detach();
    excelButton.removeClass('dt-button').addClass('btn btn-success');
    $('#excel-button-container').html(excelButton);
    
    // Move length control to container ABOVE the table
    $('.dt-length-container').html(`
        <div class="d-flex align-items-center">
            <label class="me-2 mb-0">Show:</label>
            <select class="form-select form-select-sm" id="customLength" style="width: 80px;">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
            <label class="ms-2 mb-0">entries</label>
        </div>
    `);
    
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

    // Connect custom length selector with DataTables
    $('#customLength').on('change', function() {
        table.page.len(parseInt(this.value)).draw();
    });

    // Handle pagination clicks di container baru
    $(document).on('click', '.pagination-container .paginate_button', function(e) {
        e.preventDefault();
        const pageNum = $(this).data('dt-idx');
        if (pageNum !== undefined) {
            table.page(pageNum).draw('page');
        } else if ($(this).hasClass('previous')) {
            table.page('previous').draw('page');
        } else if ($(this).hasClass('next')) {
            table.page('next').draw('page');
        }
    });

    // Updated Multi-Select Filter Logic with Pemilik Dokumen
    $('#btnFilter').on('click', function() {
        const selectedStandar = filterStandar.getValue(true);
        const selectedKlausul = filterKlausul.getValue(true);
        const selectedPemilik = $('#filterPemilik').val();
        const selectedJenis = $('#filterJenis').val();

        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            const row = table.row(dataIndex).node();
            const rowStandar = $(row).data('standar') ? $(row).data('standar').toString().split(',') : [];
            const rowKlausul = $(row).data('klausul') ? $(row).data('klausul').toString().split(',') : [];
            const rowPemilik = $(row).data('pemilik') || '';
            
            // Check filters
            const standarMatch = !selectedStandar.length || selectedStandar.some(s => rowStandar.includes(s));
            const klausulMatch = !selectedKlausul.length || selectedKlausul.some(k => rowKlausul.includes(k));
            const pemilikMatch = !selectedPemilik || rowPemilik.includes(selectedPemilik);
            const jenisMatch = !selectedJenis || data[2].includes($('#filterJenis option:selected').text());
            
            return standarMatch && klausulMatch && pemilikMatch && jenisMatch;
        });
        
        table.draw();
        $.fn.dataTable.ext.search.pop();
    });
});
</script>

<?= $this->endSection() ?>
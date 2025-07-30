<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<!-- CSS External Links -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<!-- Custom CSS -->
<link rel="stylesheet" href="<?= base_url('assets/css/daftar-dokumen.css') ?>">

<!-- Inline CSS for Readonly/Disabled Fields and Custom Multi-Select -->
<style>
    input[readonly], select[disabled] {
        background-color: #f1f1f1;
        cursor: not-allowed;
        opacity: 0.7;
    }
    /* Custom styling for multi-select in edit modal */
    .custom-multi-select {
        height: auto;
        min-height: 38px;
        padding: 0.25rem 0.5rem;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        background-color: white;
    }
    .custom-multi-select option {
        padding: 0.25rem 0.5rem;
    }
    .custom-multi-select option:checked {
        background-color: #6f42c1;
        color: white;
    }
    .custom-multi-select[multiple] {
        overflow-y: auto;
        max-height: 150px;
    }
    /* Ensure compatibility with Bootstrap */
    .custom-multi-select:focus {
        border-color: #6f42c1;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(111, 66, 193, 0.25);
    }
    /* Styling untuk pesan "Tidak ada data" */
    .no-data-message {
        text-align: center;
        padding: 20px;
        color: #6c757d;
        font-style: italic;
    }
</style>

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
                                <tr data-standar="<?= implode(',', array_filter(explode(',', $row['standar_ids'] ?? ''))) ?>" 
                                    data-klausul="<?= implode(',', array_filter(explode(',', $row['klausul_ids'] ?? ''))) ?>"
                                    data-pemilik="<?= esc($row['createdby'] ?? '') ?>">
                                    <td>
                                        <?php
                                        $standar_ids = array_filter(explode(',', $row['standar_ids'] ?? ''));
                                        $standar_names = array_map(function($id) use ($standards) {
                                            foreach ($standards as $s) {
                                                if ($s['id'] == $id) {
                                                    return esc($s['nama_standar']);
                                                }
                                            }
                                            return null;
                                        }, $standar_ids);
                                        $standar_names = array_filter($standar_names);
                                        echo !empty($standar_names) ? implode(', ', $standar_names) : '-';
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $klausul_ids = array_filter(explode(',', $row['klausul_ids'] ?? ''));
                                        $klausul_names = array_map(function($id) use ($clauses) {
                                            foreach ($clauses as $c) {
                                                if ($c['id'] == $id) {
                                                    return esc($c['nomor_klausul'] . ' - ' . $c['nama_klausul'] . ' (' . $c['nama_standar'] . ')');
                                                }
                                            }
                                            return null;
                                        }, $klausul_ids);
                                        $klausul_names = array_filter($klausul_names);
                                        echo !empty($klausul_names) ? implode(', ', $klausul_names) : '-';
                                        ?>
                                    </td>
                                    <td><?= esc($row['jenis_dokumen'] ?? '-') ?></td>
                                    <td>
                                        <?php if (!empty($row['kode_dokumen_kode']) && !empty($row['kode_dokumen_nama'])): ?>
                                            <div>
                                                <?= esc($row['kode_dokumen_kode']) ?> - <?= esc($row['kode_dokumen_nama']) ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($row['number'] ?? '-') ?></td>
                                    <td><?= esc($row['title'] ?? '-') ?></td>
                                    <td><?= esc($row['createdby'] ?? '-') ?></td>
                                    <td class="text-center">
                                        <?php if (!empty($row['filepath']) && file_exists(ROOTPATH . '..' . DIRECTORY_SEPARATOR . $row['filepath'])): ?>
                                            <a href="<?= base_url('document-list/serveFile?id=' . $row['id'] . '&action=download') ?>" 
                                               class="text-decoration-none" 
                                               title="Unduh <?= esc($row['filename'] ?? basename($row['filepath'])) ?>">
                                                <i class="bi bi-download text-success fs-5"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">
                                                <i class="bi bi-file-earmark-x"></i> Tidak ada file
                                            </span>
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
                                        <form action="<?= base_url('document-list/delete') ?>" method="post" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <a href="javascript:void(0);" class="text-danger btn-delete" data-id="<?= $row['id'] ?>" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Modal Edit Dokumen -->
                                <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $row['id'] ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <form action="<?= base_url('document-list/update') ?>" method="post" class="edit-form" enctype="multipart/form-data">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <div class="modal-content">
                                                <div class="modal-header border-0">
                                                    <h6 class="modal-title fw-semibold">Edit Dokumen</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                                </div>
                                                <div class="modal-body px-4 py-3">
                                                    <div class="row g-3">
                                                        <!-- Dropdown Standar (Multi-Select) -->
                                                        <div class="col-md-6">
                                                            <label class="form-label small">Standar</label>
                                                            <select name="standar[]" class="form-select form-select-sm custom-multi-select" multiple required>
                                                                <?php foreach ($standards as $s): ?>
                                                                    <option value="<?= $s['id'] ?>" <?= in_array($s['id'], array_filter(explode(',', $row['standar_ids'] ?? ''))) ? 'selected' : '' ?>>
                                                                        <?= $s['nama_standar'] ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                            <small class="text-muted">Tahan Ctrl (atau Cmd pada Mac) untuk memilih lebih dari satu.</small>
                                                        </div>
                                                        <!-- Dropdown Klausul (Multi-Select) -->
                                                        <div class="col-md-6">
                                                            <label class="form-label small">Klausul</label>
                                                            <select name="klausul[]" class="form-select form-select-sm custom-multi-select" multiple required>
                                                                <?php foreach ($clauses as $c): ?>
                                                                    <option value="<?= $c['id'] ?>" <?= in_array($c['id'], array_filter(explode(',', $row['klausul_ids'] ?? ''))) ? 'selected' : '' ?>>
                                                                        <?= $c['nomor_klausul'] ?> - <?= $c['nama_klausul'] ?> (<?= $c['nama_standar'] ?>)
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                            <small class="text-muted">Tahan Ctrl (atau Cmd pada Mac) untuk memilih lebih dari satu.</small>
                                                        </div>
                                                        <!-- Jenis Dokumen -->
                                                        <div class="col-md-6">
                                                            <label class="form-label small">Jenis Dokumen</label>
                                                            <select name="type" class="form-select form-select-sm" disabled>
                                                                <?php foreach ($kategori_dokumen as $kategori): ?>
                                                                    <option value="<?= $kategori['id'] ?>" <?= ($row['type'] == $kategori['id']) ? 'selected' : '' ?>>
                                                                        <?= $kategori['name'] ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <!-- Kode Jenis -->
                                                        <div class="col-md-6">
                                                            <label class="form-label small">Kode Jenis</label>
                                                            <input type="text" class="form-control form-control-sm" name="kode_jenis_dokumen" value="<?= esc($row['kode_jenis_dokumen']) ?>" readonly>
                                                        </div>
                                                        <!-- Nomor -->
                                                        <div class="col-md-6">
                                                            <label class="form-label small">Nomor</label>
                                                            <input type="text" class="form-control form-control-sm" name="number" value="<?= esc($row['number']) ?>" readonly>
                                                        </div>
                                                        <!-- Nama Dokumen -->
                                                        <div class="col-md-6">
                                                            <label class="form-label small">Nama Dokumen</label>
                                                            <input type="text" class="form-control form-control-sm" name="title" value="<?= esc($row['title']) ?>" readonly>
                                                        </div>
                                                        <!-- Pemilik Dokumen -->
                                                        <div class="col-md-6">
                                                            <label class="form-label small">Pemilik Dokumen</label>
                                                            <input type="text" class="form-control form-control-sm" name="createdby" value="<?= esc($row['createdby'] ?? '') ?>" readonly>
                                                        </div>
                                                        <!-- File Dokumen -->
                                                        <div class="col-md-6">
                                                            <label class="form-label small">File Dokumen</label>
                                                            <?php if (!empty($row['filepath']) && file_exists(ROOTPATH . '..' . DIRECTORY_SEPARATOR . $row['filepath'])): ?>
                                                                <small class="text-muted d-block mt-1">Saat ini: <?= esc($row['filename'] ?? $row['filepath']) ?></small>
                                                                <a href="<?= base_url('document-list/serveFile?id=' . $row['id'] . '&action=download') ?>" 
                                                                   class="btn btn-primary btn-sm mt-1" 
                                                                   title="Unduh <?= esc($row['filename'] ?? basename($row['filepath'])) ?>">
                                                                    <i class="bi bi-download"></i> Lihat File
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="text-muted">Tidak ada file</span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <!-- Revisi -->
                                                        <div class="col-md-3">
                                                            <label class="form-label small">Revisi</label>
                                                            <input type="text" class="form-control form-control-sm" name="revision" value="<?= esc($row['revision']) ?>" readonly>
                                                        </div>
                                                        <!-- Tanggal Efektif -->
                                                        <div class="col-md-3">
                                                            <label class="form-label small">Tanggal Efektif</label>
                                                            <input type="date" class="form-control form-control-sm" name="date_published" value="<?= esc($row['date_published']) ?>">
                                                        </div>
                                                        <!-- Disetujui Oleh -->
                                                        <div class="col-md-3">
                                                            <label class="form-label small">Disetujui Oleh</label>
                                                            <input type="hidden" name="approveby" value="<?= esc($row['approveby'] ?? '') ?>">
                                                            <input type="text" class="form-control form-control-sm" value="<?= esc($row['approved_by_name'] ?? '') ?>" readonly>
                                                        </div>
                                                        <!-- Tanggal Disetujui -->
                                                        <div class="col-md-3">
                                                            <label class="form-label small">Tanggal Disetujui</label>
                                                            <input type="datetime-local" class="form-control form-control-sm" name="approvedate" value="<?= esc(date('Y-m-d\TH:i', strtotime($row['approvedate'] ?? 'now'))) ?>" readonly>
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
                    <!-- Container untuk pesan "Tidak ada data" -->
                    <div class="no-data-message" style="display: none;">Tidak ada data</div>
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
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

<!-- Custom JS -->
<script>
$(document).ready(function() {
    // Initialize Choices.js for filter selects (kept for consistency with filter section)
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
            lengthMenu: "Tampilkan _MENU_ entri",
            paginate: {
                previous: "Sebelumnya",
                next: "Berikutnya"
            },
            zeroRecords: "" // Kosongkan pesan default DataTables
        },
        columnDefs: [
            { orderable: false, targets: [-1] }, // Kolom Aksi tidak bisa diurutkan
            { searchable: true, targets: [0, 1, 2, 3, 4, 5, 6, 9, 10, 11] } // Aktifkan pencarian hanya pada kolom tertentu (opsional)
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
            <label class="me-2 mb-0">Tampilkan:</label>
            <select class="form-select form-select-sm" id="customLength" style="width: 80px;">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
            <label class="ms-2 mb-0">entri</label>
        </div>
    `);
    
    // Create custom search box
    const searchHtml = `
        <div class="d-flex align-items-center">
            <label class="me-2 mb-0">Cari:</label>
            <input type="search" class="form-control form-control-sm" id="customSearch" style="width: 200px;" placeholder="Cari dokumen...">
        </div>
    `;
    $('.dt-search-container').html(searchHtml);
    
    // Connect custom search with DataTables
    $('#customSearch').on('keyup', function() {
        const searchTerm = this.value.trim();
        table.search(searchTerm).draw();

        // Tampilkan pesan "Tidak ada data" jika tidak ada hasil pencarian
        const visibleRows = table.rows({ filter: 'applied' }).data().length;
        if (visibleRows === 0) {
            $('.no-data-message').show();
            $('#dokumenTable').hide();
        } else {
            $('.no-data-message').hide();
            $('#dokumenTable').show();
        }
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

        // Tampilkan pesan "Tidak ada data" jika tidak ada baris yang sesuai
        const visibleRows = table.rows({ filter: 'applied' }).data().length;
        if (visibleRows === 0) {
            $('.no-data-message').show();
            $('#dokumenTable').hide();
        } else {
            $('.no-data-message').hide();
            $('#dokumenTable').show();
        }
    });

    // Handle form submission for edit modal with SweetAlert2
    $('.edit-form').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const modalId = '#editModal' + formData.get('id');
        console.log('Form Data:', Object.fromEntries(formData)); // Debug form data

        $.ajax({
            url: '<?= base_url('document-list/update') ?>',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            beforeSend: function() {
                console.log('Sending AJAX request to document-list/update...');
            },
            success: function(response) {
                console.log('AJAX Success Response:', response);
                $(modalId).modal('hide'); // Close the modal
                Swal.fire({
                    icon: response.swal?.icon || 'error',
                    title: response.swal?.title || 'Gagal!',
                    text: response.swal?.text || response.message || 'Terjadi kesalahan.',
                    confirmButtonColor: response.status === 'success' ? '#6f42c1' : (response.status === 'warning' ? '#ffc107' : '#dc3545')
                }).then(() => {
                    if (response.status === 'success') {
                        setTimeout(() => {
                            location.reload();
                        }, 500);
                    }
                });
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr, status, error);
                $(modalId).modal('hide'); // Close the modal
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Terjadi kesalahan: ' + (xhr.responseJSON?.message || xhr.statusText || 'Unknown error'),
                    confirmButtonColor: '#dc3545'
                });
            }
        });
    });
});
</script>

<script>
    $(document).on('click', '.btn-delete', function() {
        const id = $(this).data('id');

        Swal.fire({
            title: 'Yakin ingin menghapus dokumen ini?',
            text: 'Tindakan ini tidak dapat dibatalkan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = $('<form>', {
                    method: 'POST',
                    action: '<?= base_url('document-list/delete') ?>'
                });
                form.append($('<input>', {
                    type: 'hidden',
                    name: '<?= csrf_token() ?>',
                    value: '<?= csrf_hash() ?>'
                }));
                form.append($('<input>', {
                    type: 'hidden',
                    name: 'id',
                    value: id
                }));
                $('body').append(form);
                form.submit();
            }
        });
    });
</script>

<?php if (session()->getFlashdata('success')): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '<?= esc(session()->getFlashdata('success')) ?>',
        confirmButtonColor: '#6f42c1'
    });
</script>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: '<?= esc(session()->getFlashdata('error')) ?>',
        confirmButtonColor: '#dc3545'
    });
</script>
<?php endif; ?>

<?= $this->endSection() ?>
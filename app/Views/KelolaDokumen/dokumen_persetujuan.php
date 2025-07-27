<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="container-fluid persetujuan-container">
    <h4 class="mb-4">Persetujuan Dokumen</h4>

    <div class="card persetujuan-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="export-buttons-container"></div>
                <div class="search-container"></div>
            </div>

            <div class="table-scroll-container">
                <table id="persetujuanTable" class="table table-bordered table-striped persetujuan-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Fakultas/Direktorat</th>
                            <th>Bagian/Unit/Prodi</th>
                            <th>Nama Dokumen</th>
                            <th>Revisi</th>
                            <th>Jenis Dokumen</th>
                            <th>Kode & Nama Dokumen</th>
                            <th>File</th>
                            <th>Remark</th>
                            <th class="text-center noExport">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documents as $i => $doc): ?>
                        <tr>
                            <td class="text-center"><?= $i + 1 ?></td>
                            <td><?= esc($doc['parent_name'] ?? '-') ?></td>
                            <td><?= esc($doc['unit_name'] ?? '-') ?></td>
                            <td>
                                <span class="text-truncate-custom" title="<?= esc($doc['title']) ?>">
                                    <?= esc($doc['title']) ?>
                                </span>
                            </td>
                            <td class="text-center"><?= esc($doc['revision']) ?></td>
                            <td><?= esc($doc['jenis_dokumen']) ?></td>
                            <td>
                                <span class="text-truncate-custom" title="<?= esc($doc['kode_nama_dokumen'] ?? '-') ?>">
                                    <?= esc($doc['kode_nama_dokumen'] ?? '-') ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($doc['filepath'])): ?>
                                    <a href="<?= base_url('uploads/' . $doc['filepath']) ?>" target="_blank" class="file-link">
                                        <i class="bi bi-file-earmark-text"></i> 
                                        <span class="file-text-truncate" title="<?= esc($doc['filename'] ?? $doc['filepath']) ?>">
                                            <?= esc($doc['filename'] ?? $doc['filepath']) ?>
                                        </span>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">
                                        <i class="bi bi-file-earmark-x"></i> Tidak ada file
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="text-truncate-custom" title="<?= esc($doc['remark']) ?>">
                                    <?= esc($doc['remark']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="action-buttons">
                                    <button class="btn btn-sm btn-outline-primary btn-action" data-bs-toggle="modal" data-bs-target="#editModal<?= $doc['id'] ?>" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <form method="post" action="<?= base_url('document-approval/delete') ?>" class="d-inline-block delete-form">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="document_id" value="<?= $doc['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-action btn-delete" data-id="<?= $doc['id'] ?>" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal Edit -->
                        <div class="modal fade" id="editModal<?= $doc['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="post" action="<?= base_url('document-approval/update') ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="document_id" value="<?= $doc['id'] ?>">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Dokumen</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Judul</label>
                                                <input type="text" name="title" class="form-control" value="<?= esc($doc['title']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Revisi</label>
                                                <input type="text" name="revision" class="form-control" value="<?= esc($doc['revision']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Remark</label>
                                                <textarea name="remark" class="form-control" rows="3"><?= esc($doc['remark']) ?></textarea>
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
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="pagination-container">
                <div class="dataTables_length"></div>
                <div class="dataTables_paginate"></div>
            </div>
        </div>
    </div>
</div>

<!-- Styles -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="<?= base_url('assets/css/datatables-custom.css') ?>" rel="stylesheet">
<link href="<?= base_url('assets/css/persetujuan.css') ?>" rel="stylesheet">

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- DataTables Logic -->
<script>
$(document).ready(function () {
    const table = $('#persetujuanTable').DataTable({
        dom: 't', 
        pageLength: 10,
        order: [],
        columnDefs: [
            { orderable: false, targets: 9 },
            { className: 'text-center', targets: [0, 4, 9] }
        ],
        buttons: [
            {
                extend: 'excel',
                className: 'btn btn-outline-success btn-sm',
                title: 'Data_Users',
                exportOptions: { 
                columns: [0, 1, 2, 3, 4, 5, 6] 
                }
            },
            {
                extend: 'pdfHtml5',
                text: 'PDF',
                className: 'btn',
                title: 'Persetujuan Dokumen',
                filename: 'data_users',
                exportOptions: { 
                columns: [0, 1, 2, 3, 4, 5, 6] 
                },
                orientation: 'potrait', 
                pageSize: 'A4',
                customize: function (doc) {
                const now = new Date();
                const waktuCetak = now.toLocaleString('id-ID', {
                    day: '2-digit', month: '2-digit', year: 'numeric',
                    hour: '2-digit', minute: '2-digit'
                });

                if (doc.content[0] && doc.content[0].text === 'Persetujuan Dokumen') {
                    doc.content.splice(0, 1);
                }

                doc.content.unshift({
                    text: 'Persetujuan Dokumen',
                    alignment: 'center',
                    bold: true,
                    fontSize: 16,
                    margin: [0, 0, 0, 15]
                });

                doc.styles.tableHeader = {
                    fillColor: '#eaeaea',
                    color: '#000',
                    alignment: 'center',
                    bold: true,
                    fontSize: 10
                };

                doc.styles.tableBodyEven = { fillColor: '#f8f9fa' };
                doc.styles.tableBodyOdd = { fillColor: '#ffffff' };
                doc.defaultStyle.fontSize = 9;
                doc.styles.tableBody = { 
                    alignment: 'center', 
                    fontSize: 9 
                };
                // Footer
                doc.footer = function (currentPage, pageCount) {
                    return {
                    columns: [
                        { 
                        text: 'Dicetak: ' + waktuCetak, 
                        alignment: 'left', 
                        margin: [40, 0] 
                        },
                        { 
                        text: '© 2025 Telkom University – Document Management System', 
                        alignment: 'center' 
                        },
                        { 
                        text: 'Halaman ' + currentPage.toString() + ' dari ' + pageCount, 
                        alignment: 'right', 
                        margin: [0, 0, 40, 0] 
                        }
                    ],
                    fontSize: 8,
                    margin: [0, 10, 0, 0]
                    };
                };

                doc.pageMargins = [40, 60, 40, 60];

                if (doc.content[1] && doc.content[1].table) {
                    doc.content[1].table.widths = ['8%', '15%', '20%', '20%', '15%', '15%', '7%'];
                    doc.content[1].margin = [0, 0, 0, 0];
                    doc.content[1].layout = {
                    hLineWidth: function () { return 0.5; },
                    vLineWidth: function () { return 0.5; },
                    hLineColor: function () { return '#000000'; },
                    vLineColor: function () { return '#000000'; },
                    paddingLeft: function () { return 5; },
                    paddingRight: function () { return 5; },
                    paddingTop: function () { return 3; },
                    paddingBottom: function () { return 3; }
                    };
                }

                doc.content.push({
                    text: '* Dokumen ini berisi daftar pengguna aktif dalam sistem.',
                    alignment: 'left',
                    italics: true,
                    fontSize: 8,
                    margin: [0, 15, 0, 0]
                });
                }
            }
        ],
        lengthMenu: [10, 25, 50, 100],
        language: {
            lengthMenu: "Tampilkan _MENU_ entri",
            paginate: {
                previous: "Previous",
                next: "Next"
            },
            info: "",
            infoEmpty: "",
            infoFiltered: ""
        }
    });

    // Setup export buttons
    table.buttons().container().appendTo('.export-buttons-container');

    // Setup custom search
    $('.search-container').html(`
        <div class="d-flex align-items-center gap-2">
            <label class="mb-0">Search:</label>
            <input type="search" class="form-control form-control-sm" id="customSearch" placeholder="Cari dokumen...">
        </div>
    `);

    $('#customSearch').on('keyup', function() {
        table.search(this.value).draw();
    });

    $('.dataTables_length').html(`
        <div class="d-flex align-items-center gap-2">
            <label class="mb-0">Show:</label>
            <select class="form-select form-select-sm" id="customLength" style="width: auto;">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
            <span class="mb-0">entries</span>
        </div>
    `);

    $('#customLength').on('change', function() {
        table.page.len(parseInt(this.value)).draw();
    });

    // Setup custom pagination
    function updatePagination() {
        const info = table.page.info();
        let paginationHtml = '<nav><ul class="pagination justify-content-center mb-0">';
        
        // Previous button
        if (info.page > 0) {
            paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${info.page - 1}">Previous</a></li>`;
        } else {
            paginationHtml += '<li class="page-item disabled"><span class="page-link">Previous</span></li>';
        }
        
        // Page numbers
        const startPage = Math.max(0, info.page - 2);
        const endPage = Math.min(info.pages - 1, info.page + 2);
        
        for (let i = startPage; i <= endPage; i++) {
            if (i === info.page) {
                paginationHtml += `<li class="page-item active"><span class="page-link">${i + 1}</span></li>`;
            } else {
                paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${i}">${i + 1}</a></li>`;
            }
        }
        
        // Next button
        if (info.page < info.pages - 1) {
            paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${info.page + 1}">Next</a></li>`;
        } else {
            paginationHtml += '<li class="page-item disabled"><span class="page-link">Next</span></li>';
        }
        
        paginationHtml += '</ul></nav>';
        
        $('.dataTables_paginate').html(paginationHtml);
    }

    table.on('draw', function() {
        updatePagination();
    });

    // Handle klik pagination
    $(document).on('click', '.dataTables_paginate .page-link[data-page]', function(e) {
        e.preventDefault();
        const page = parseInt($(this).data('page'));
        table.page(page).draw(false);
    });

    updatePagination();

    // Konfirmasi delete dengan SweetAlert2
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        const form = $(this).closest('form');
        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: 'Apakah Anda yakin ingin menghapus dokumen ini? Tindakan ini tidak dapat dibatalkan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<?php if (session()->getFlashdata('success')): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '<?= esc(session()->getFlashdata('success')) ?>',
        confirmButtonColor: '#198754',
        timer: 3000,
        timerProgressBar: true
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
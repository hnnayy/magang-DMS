<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="container-fluid px-4 py-4">
    <h4 class="mb-4">Persetujuan Dokumen</h4>

    <div class="card">
        <div class="card-body">
            <!-- Container untuk tombol export dan search -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="dt-buttons-container gap-2"></div>
                <div class="dt-search-container"></div>
            </div>

            <div class="table-responsive">
                <table id="persetujuanTable" class="table table-bordered table-striped">
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
                            <td><?= $i + 1 ?></td>
                            <td><?= esc($doc['parent_name'] ?? '-') ?></td>
                            <td><?= esc($doc['unit_name'] ?? '-') ?></td>
                            <td><?= esc($doc['title']) ?></td>
                            <td><?= esc($doc['revision']) ?></td>
                            <td><?= esc($doc['jenis_dokumen']) ?></td>
                            <td><?= esc($doc['kode_nama_dokumen'] ?? '-') ?></td>
                            <td>
                                <?php if (!empty($doc['filepath'])): ?>
                                    <a href="<?= base_url('uploads/' . $doc['filepath']) ?>" target="_blank" class="text-decoration-none">
                                        <i class="bi bi-file-earmark-text text-primary"></i> 
                                        <span class="text-truncate d-inline-block" style="max-width: 100px;">
                                            <?= esc($doc['filename'] ?? $doc['filepath']) ?>
                                        </span>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">
                                        <i class="bi bi-file-earmark-x"></i> Tidak ada file
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td><?= esc($doc['remark']) ?></td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $doc['id'] ?>">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <form method="post" action="<?= base_url('kelola-dokumen/persetujuan/delete') ?>" class="d-inline-block delete-form">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="document_id" value="<?= $doc['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-delete" data-id="<?= $doc['id'] ?>">
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
                                    <form method="post" action="<?= base_url('kelola-dokumen/persetujuan/update') ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="document_id" value="<?= $doc['id'] ?>">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Dokumen</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-2">
                                                <label class="form-label">Judul</label>
                                                <input type="text" name="title" class="form-control" value="<?= esc($doc['title']) ?>">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label">Revisi</label>
                                                <input type="text" name="revision" class="form-control" value="<?= esc($doc['revision']) ?>">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label">Remark</label>
                                                <textarea name="remark" class="form-control"><?= esc($doc['remark']) ?></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer d-flex justify-content-between gap-2">
                                            <button type="button" class="btn btn-danger flex-grow-1" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary flex-grow-1">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Styles -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="<?= base_url('assets/css/datatables-custom.css') ?>" rel="stylesheet">

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
        dom: 'rt<"d-flex justify-content-between align-items-center mt-3"<"d-flex align-items-center"l>p>',
        pageLength: 10,
        order: [],
        columnDefs: [
            { orderable: false, targets: 9 },
            { className: 'text-center', targets: 9 }
        ],
        buttons: [
            {
                extend: 'excel',
                className: 'btn btn-outline-success btn-sm',
                title: 'Data_Persetujuan_Dokumen',
                exportOptions: { columns: [0,1,2,3,4,5,6,7,8] }
            },
            {
                extend: 'pdfHtml5',
                text: 'PDF',
                className: 'btn btn-outline-secondary btn-sm',
                title: 'Data Persetujuan Dokumen',
                filename: 'data_persetujuan_dokumen',
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: { columns: [0,1,2,3,4,5,6,7,8] },
                customize: function (doc) {
                    const now = new Date();
                    const waktuCetak = now.toLocaleString('id-ID', {
                        day: '2-digit', month: '2-digit', year: 'numeric',
                        hour: '2-digit', minute: '2-digit'
                    });
                    if (doc.content[0]?.text === 'Data Persetujuan Dokumen') {
                        doc.content.splice(0, 1);
                    }
                    doc.content.unshift({
                        text: 'Data Persetujuan Dokumen',
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
                        fontSize: 9
                    };
                    doc.defaultStyle.fontSize = 8;
                    doc.styles.tableBody = { alignment: 'center', fontSize: 8 };
                    doc.footer = function (currentPage, pageCount) {
                        return {
                            columns: [
                                { text: 'Dicetak: ' + waktuCetak, alignment: 'left', margin: [40, 0] },
                                { text: '© 2025 Telkom University – Document Management System', alignment: 'center' },
                                { text: 'Halaman ' + currentPage + ' dari ' + pageCount, alignment: 'right', margin: [0, 0, 40, 0] }
                            ],
                            fontSize: 8,
                            margin: [0, 10, 0, 0]
                        };
                    };
                    doc.pageMargins = [40, 60, 40, 60];
                    if (doc.content[1]?.table) {
                        doc.content[1].table.widths = ['5%', '12%', '12%', '18%', '8%', '12%', '15%', '10%', '8%'];
                        doc.content[1].layout = {
                            hLineWidth: () => 0.5,
                            vLineWidth: () => 0.5,
                            hLineColor: () => '#000000',
                            vLineColor: () => '#000000',
                            paddingLeft: () => 4,
                            paddingRight: () => 4,
                            paddingTop: () => 3,
                            paddingBottom: () => 3
                        };
                    }
                    doc.content.push({
                        text: '* Dokumen ini berisi daftar dokumen yang memerlukan persetujuan dalam sistem.',
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
                previous: "Sebelumnya",
                next: "Berikutnya"
            }
        }
    });

    // Custom tombol export dan search
    table.buttons().container().appendTo('.dt-buttons-container');

    $('.dt-search-container').html(`
        <div class="d-flex align-items-center">
            <label class="me-2 mb-0">Search:</label>
            <input type="search" class="form-control form-control-sm" id="customSearch" style="width: 200px;">
        </div>
    `);

    $('#customSearch').on('keyup', function() {
        table.search(this.value).draw();
    });

    // Konfirmasi delete
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        const form = $(this).closest('form');
        Swal.fire({
            title: 'Yakin ingin menghapus?',
            text: 'Dokumen akan dihapus secara permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>

<?php if (session()->getFlashdata('success')): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: '<?= esc(session()->getFlashdata('success')) ?>',
        confirmButtonColor: '#3085d6'
    });
</script>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: '<?= esc(session()->getFlashdata('error')) ?>',
        confirmButtonColor: '#d33'
    });
</script>
<?php endif; ?>

<?= $this->endSection() ?>

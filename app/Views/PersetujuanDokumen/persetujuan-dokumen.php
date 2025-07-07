<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="px-4 py-3 w-100">
    <h4>Persetujuan Dokumen</h4>
    <hr>

    <!-- Table -->
    <div class="table-responsive shadow-sm rounded bg-white p-3">
        <table class="table table-bordered table-hover align-middle" id="documentTable">
            <thead class="table-light">
                <tr>
                    <th class="text-center">No</th>
                    <th>Fakultas/Direktorat</th>
                    <th>Bagian/Unit/Prodi</th>
                    <th>Nama Dokumen</th>
                    <th class="text-center">Revisi</th>
                    <th>Jenis Dokumen</th>
                    <th>Kode & Nama Dokumen</th>
                    <th>File</th>
                    <th>Keterangan</th>
                    <th class="text-center noExport">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $sampleData = [
                    ['FSAL', 'Yan CeLOE', 'Prosedur perubahan data', '00', 'Intruksi kerja dan prosedur', 'IK', 'Perubahan Data', 'file.pdf', 'Keterangan 1'],
                    ['FSAL', 'Yan CeLOE', 'Prosedur perubahan data', '00', 'Intruksi kerja dan prosedur', 'IK', 'Revisi SOP', 'file.pdf', 'Keterangan 2'],
                    ['FSAL', 'Yan CeLOE', 'Prosedur perubahan data', '00', 'Intruksi kerja dan prosedur', 'IK', 'Validasi Data', 'file.pdf', 'Keterangan 3'],
                    ['FSAL', 'Yan CeLOE', 'Prosedur perubahan data', '00', 'Intruksi kerja dan prosedur', 'IK', 'Formulir Baru', 'file.pdf', 'Keterangan 4'],
                ];
                foreach ($sampleData as $i => $data):
                ?>
                <tr>
                    <td class="text-center"><?= $i + 1 ?></td>
                    <td><?= $data[0] ?></td>
                    <td><?= $data[1] ?></td>
                    <td><?= $data[2] ?></td>
                    <td class="text-center"><?= $data[3] ?></td>
                    <td><?= $data[4] ?></td>
                    <td><?= $data[5] . ' - ' . $data[6] ?></td>
                    <td><?= $data[7] ?></td>
                    <td><?= $data[8] ?></td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-2">
                            <a href="#" class="text-danger" title="Delete"><i class="bi bi-trash"></i></a>
                            <a href="<?= base_url('dokumen/edit') ?>" class="text-primary" title="Edit"><i class="bi bi-pencil-square"></i></a>
                            <a href="#" class="text-success" title="Approve"><i class="bi bi-check-lg"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('style') ?>
<style>
    .export-buttons .btn {
        background-color: #b41616;
        color: white;
        border-radius: 8px;
        padding: 6px 14px;
    }

    .export-buttons .btn:hover {
        background-color: #921212;
    }

    .search-container .search-btn {
        background-color: #b41616;
        color: white;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.3em 0.8em;
        margin: 0 2px;
        border-radius: 4px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #b41414 !important;
        color: white !important;
        border: none !important;
    }

    .dataTables_length {
        margin-bottom: 1rem;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>

<script>
$(document).ready(function () {
    const table = $('#documentTable').DataTable({
        dom: '<"row mb-3"<"col-md-6 export-buttons d-flex gap-2"B><"col-md-6 text-end"f>>rt<"row mt-3"<"col-md-6"l><"col-md-6 text-end"p>>',
        pageLength: 5,
        order: [],
        columnDefs: [
            { orderable: false, targets: 9 },
            { className: 'text-center', targets: 9 }
        ],
        buttons: [
            { extend: 'copyHtml5', text: 'Copy', className: 'btn' },
            { extend: 'csvHtml5', text: 'CSV', className: 'btn' },
            { extend: 'excelHtml5', text: 'Excel', className: 'btn' },
            {
                extend: 'pdfHtml5',
                text: 'PDF',
                className: 'btn',
                exportOptions: { columns: [0,1,2,3,4,5,6,7,8] },
                customize: function (doc) {
                    const now = new Date().toLocaleString('en-GB');
                    doc.pageMargins = [0, 30, 0, 30];
                    doc.content.splice(0, 1);
                    doc.content.unshift({
                        text: 'Daftar Dokumen',
                        alignment: 'center',
                        fontSize: 14,
                        bold: true,
                        margin: [0, 0, 0, 10]
                    });
                    doc.styles.tableHeader = {
                        fillColor: '#e8e4e4',
                        color: '#000',
                        alignment: 'center',
                        bold: true
                    };
                    doc.content[doc.content.length - 1].layout = {
                        hLineWidth: () => 0.5,
                        vLineWidth: () => 0.5,
                        hLineColor: () => '#000',
                        vLineColor: () => '#000',
                        paddingLeft: () => 4,
                        paddingRight: () => 4
                    };
                    doc.footer = (currentPage, pageCount) => ({
                        columns: [
                            { text: now, alignment: 'left', margin: [30, 0] },
                            { text: '© 2025 Telkom University – Document Management System', alignment: 'center' },
                            { text: currentPage + '/' + pageCount, alignment: 'right', margin: [0, 0, 30] }
                        ],
                        fontSize: 9
                    });
                    doc.content.push({
                        text: '* Dokumen ini berisi daftar dokumen yang perlu persetujuan.',
                        italics: true,
                        fontSize: 9,
                        margin: [0, 10, 0, 0]
                    });
                }
            },
            {
                extend: 'print',
                text: 'Print',
                className: 'btn',
                exportOptions: { columns: [0,1,2,3,4,5,6,7,8] },
                customize: function (win) {
                    const now = new Date().toLocaleString('en-GB');
                    $(win.document.body).css('font-size', '10px').css('margin', '20px');
                    $(win.document.body).append(` 
                        <p style="font-style: italic; margin-top: 20px;">* Dokumen ini berisi daftar dokumen yang perlu persetujuan.</p>
                        <div style="position: fixed; bottom: 20px; width: 100%; text-align: center; font-size: 10px;">
                            © 2025 Telkom University – Document Management System
                        </div>
                    `);
                    const table = $(win.document.body).find('table');
                    table.css({
                        'border-collapse': 'collapse',
                        'width': '100%'
                    });
                    table.find('th').css({
                        'background-color': '#e8e4e4',
                        'border': '1px solid #000',
                        'padding': '6px',
                        'text-align': 'center'
                    });
                    table.find('td').css({
                        'border': '1px solid #000',
                        'padding': '6px',
                        'vertical-align': 'top'
                    });
                }
            }
        ]
    });
});


</script>
<?= $this->endSection() ?>

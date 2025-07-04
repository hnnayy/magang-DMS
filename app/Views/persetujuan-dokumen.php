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

<script>
$(function () {
    const dt = $('#documentTable').DataTable({
        dom: '<"row mb-3"<"col-md-6 d-flex gap-2 export-buttons"B><"col-md-6 text-end"f>>' +
             'rt' +
             '<"row mt-3"<"col-md-6"l><"col-md-6 text-end"p>>',
        pageLength: 5,
        order: [],
        columnDefs: [{ orderable: false, targets: 9 }],
        buttons: [
            {
                extend: 'copyHtml5',
                text: 'Copy',
                className: 'btn'
            },
            {
                extend: 'csvHtml5',
                text: 'CSV',
                className: 'btn'
            },
            {
                extend: 'excelHtml5',
                text: 'Excel',
                className: 'btn'
            },
            {
                extend: 'pdfHtml5',
                text: 'PDF',
                className: 'btn'
            },
            {
                extend: 'print',
                text: 'Print',
                className: 'btn'
            }
        ]
    });

    dt.buttons().container().appendTo('.export-buttons');

    $('#searchInput').on('keyup', function () {
        dt.search(this.value).draw();
    });

    $('#searchBtn').on('click', function () {
        dt.search($('#searchInput').val()).draw();
    });
});
</script>
<?= $this->endSection() ?>

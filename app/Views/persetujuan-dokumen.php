<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="px-4 py-3 w-100">
    <!-- Menambahkan Judul di atas Tabel -->
    <h2>Persetujuan Dokumen</h2>

    <!-- Export Buttons and Search Section -->
    <div class="row mb-3 align-items-center">
        <div class="col-md-6">
            <div class="export-buttons"></div>
        </div>
        <div class="col-md-6">
            <div class="input-group search-container">
                <input type="text" class="form-control search-input" id="searchInput" placeholder="Search">
                <button class="btn search-btn" type="button" id="searchBtn">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="table-responsive shadow-sm rounded bg-white p-3">
        <table class="table table-bordered align-middle" id="documentTable">
            <thead class="table-light">
                <tr>
                    <th class="text-center">No</th>
                    <th>Fakultas/ Direktorat</th>
                    <th>Bagian/Unit/ Program Studi</th>
                    <th>Nama Dokumen</th>
                    <th class="text-center">Revisi</th>
                    <th>Jenis Dokumen</th>
                    <th>Kode dan Nama Dokumen</th>
                    <th>File Dokumen</th>
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

        <!-- Menambahkan tulisan miring di bawah tabel -->
        <p class="mt-4 fst-italic" style="text-align: left;">
            * Daftar dokumen yang telah diajukan untuk persetujuan
        </p>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('style') ?>
<style>
    /* Menata Header Tabel */
    #documentTable thead th {
        background-color: #4CAF50;  /* Warna latar header */
        color: white;
        font-weight: bold;
        text-align: center;
        padding: 12px;
    }

    /* Menata body tabel */
    #documentTable tbody td {
        text-align: center;
        padding: 12px;
        border-bottom: 1px solid #ddd;  /* Garis bawah untuk setiap baris */
    }

    /* Menambahkan garis batas antar kolom */
    #documentTable td, #documentTable th {
        border: 1px solid #ddd;
    }

    /* Menata seluruh tabel dengan padding */
    #documentTable {
        margin-top: 20px;
        font-size: 12px;
        border-collapse: collapse;
    }

    /* Menambahkan hover efek pada baris */
    #documentTable tbody tr:hover {
        background-color: #f4f4f4;
    }

    /* Menambah jarak antara header dan body */
    #documentTable thead {
        margin-bottom: 5px;
    }

    /* Menambah jarak bawah untuk footer */
    .fst-italic {
        font-size: 10px;
        margin-top: 20px;
    }

    /* Lebar kolom agar tidak terpotong */
    #documentTable td:nth-child(1), #documentTable th:nth-child(1) {
        width: 5%;
    }

    #documentTable td:nth-child(2), #documentTable th:nth-child(2) {
        width: 15%;
    }

    #documentTable td:nth-child(3), #documentTable th:nth-child(3) {
        width: 20%;
    }

    #documentTable td:nth-child(4), #documentTable th:nth-child(4) {
        width: 20%;
    }

    #documentTable td:nth-child(5), #documentTable th:nth-child(5) {
        width: 10%;
    }

    #documentTable td:nth-child(6), #documentTable th:nth-child(6) {
        width: 15%;
    }

    #documentTable td:nth-child(7), #documentTable th:nth-child(7) {
        width: 20%;
    }

    #documentTable td:nth-child(8), #documentTable th:nth-child(8) {
        width: 10%;
    }

    #documentTable td:nth-child(9), #documentTable th:nth-child(9) {
        width: 15%;
    }

    /* HIDE CLIPBOARD NOTIFICATIONS */
    .dt-button-info, .dt-button-background {
        display: none !important;
    }
    
    /* Hide toast notifications */
    .toast, .notification, .alert-info {
        display: none !important;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('script') ?>
<!-- jQuery dan DataTables -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<!-- DataTables Export Buttons -->
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script>
    $(document).ready(function () {
        const table = $('#documentTable').DataTable({
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'copyHtml5',
                    text: 'Copy',
                    className: 'btn btn-purple border me-2',
                    title: null,
                    messageTop: null,
                    messageBottom: null,
                    exportOptions: { columns: ':not(.noExport)' },
                    action: function (e, dt, button, config) {
                        var data = dt.buttons.exportData(config.exportOptions);
                        var text = '';
                        for (var i = 0; i < data.header.length; i++) {
                            text += data.header[i] + '\t';
                        }
                        text += '\n';
                        for (var i = 0; i < data.body.length; i++) {
                            for (var j = 0; j < data.body[i].length; j++) {
                                text += data.body[i][j] + '\t';
                            }
                            text += '\n';
                        }
                        navigator.clipboard.writeText(text);
                    }
                },
                {
                    extend: 'csvHtml5',
                    text: 'CSV',
                    className: 'btn btn-purple border me-2',
                    title: 'Persetujuan Dokumen',
                    exportOptions: { columns: ':not(.noExport)' }
                },
                {
                    extend: 'excelHtml5',
                    text: 'Excel',
                    className: 'btn btn-purple border me-2',
                    title: 'Persetujuan Dokumen',
                    exportOptions: { columns: ':not(.noExport)' }
                },
                {
                    extend: 'pdfHtml5',
                    text: 'PDF',
                    className: 'btn btn-purple border me-2',
                    exportOptions: { columns: ':not(.noExport)' },
                    customize: function (doc) {
                        // Mengganti judul menjadi "Persetujuan Dokumen"
                        doc.content.splice(0, 1, {
                            text: 'Persetujuan Dokumen',
                            fontSize: 16,
                            alignment: 'center',
                            bold: true,
                            margin: [0, 0, 0, 12]
                        });

                        // Menyesuaikan lebar kolom agar sesuai dengan tampilan print
                        doc.content[1].table.widths = ['5%', '12%', '15%', '15%', '8%', '12%', '15%', '10%', '8%'];

                        // Menghapus warna latar belakang dan membuat tabel tanpa warna
                        doc.styles.tableHeader = {
                            fillColor: '#ffffff',
                            color: '#000000',
                            alignment: 'center',
                            bold: true,
                            fontSize: 8
                        };

                        doc.styles.tableBodyEven = { fillColor: '#ffffff' };
                        doc.styles.tableBodyOdd = { fillColor: '#ffffff' };

                        // Menambahkan footer dengan informasi waktu cetak dan nomor halaman
                        const now = new Date();
                        const waktuCetak = now.toLocaleTimeString('en-US', {
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: true,
                            day: '2-digit',
                            month: '2-digit',
                            year: 'numeric'
                        });

                        doc.footer = function (currentPage, pageCount) {
                            return {
                                columns: [
                                    { text: `${waktuCetak}`, alignment: 'left', margin: [30, 0] },
                                    { text: '© 2025 Telkom University – Document Management System', alignment: 'center' },
                                    { text: currentPage.toString() + '/' + pageCount, alignment: 'right', margin: [0, 0, 30] }
                                ],
                                fontSize: 9
                            };
                        };

                        // Menyesuaikan layout dan margin halaman agar tabel pas di halaman A4
                        doc.pageSize = 'A4';
                        doc.pageMargins = [20, 20, 20, 20];

                        // Menambahkan garis batas antar kolom dan baris
                        doc.content[doc.content.length - 1].layout = {
                            hLineWidth: function () { return 0.5; },
                            vLineWidth: function () { return 0.5; },
                            hLineColor: function () { return '#000'; },
                            vLineColor: function () { return '#000'; },
                            paddingLeft: function () { return 4; },
                            paddingRight: function () { return 4; }
                        };

                        // Menambahkan teks miring di bawah tabel
                        doc.content.push({
                            text: '* Daftar dokumen yang telah diajukan untuk persetujuan',
                            italics: true,
                            fontSize: 10,
                            margin: [0, 10],
                            alignment: 'left'
                        });
                    }
                },
                {
                    extend: 'print',
                    text: 'Print',
                    className: 'btn btn-purple border',
                    title: 'Persetujuan Dokumen',
                    exportOptions: { columns: ':not(.noExport)' },
                    customize: function (win) {
                        $(win.document.body).append(
                            '<p style="font-style: italic; margin-top: 20px;">* Daftar dokumen yang telah diajukan untuk persetujuan</p>'
                        );

                        // Set ukuran halaman menjadi A4
                        $(win.document.body).css('width', '210mm');
                        $(win.document.body).css('height', '297mm');
                        $(win.document.body).css('margin', '20mm');
                    }
                }
            ],
            paging: true,  // Enable pagination
            pageLength: 5,  // Adjust the number of rows per page
            info: false,
            searching: false
        });

        // Export Buttons Position
        table.buttons().container().appendTo('.export-buttons');

        // Manual Search
        $('#searchInput').on('keyup', function () {
            table.search(this.value).draw();
        });
    });
</script>
<?= $this->endSection() ?>

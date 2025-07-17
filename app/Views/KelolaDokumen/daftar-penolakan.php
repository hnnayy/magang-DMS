<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="container-fluid px-4 py-4">
    <h4 class="mb-4">Dokumen Ditolak</h4>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="dt-buttons-container gap-2"></div>
                <div class="dt-search-container"></div>
            </div>

            <div class="table-responsive">
                <table id="penolakanTable" class="table table-bordered table-striped">
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
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Fakultas Teknik</td>
                            <td>Teknik Informatika</td>
                            <td>Surat Rapat</td>
                            <td>1</td>
                            <td>Formulir</td>
                            <td>FT-001 / Surat Rapat</td>
                            <td><a href="#">izin_rapat.pdf</a></td>
                            <td>remark</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Direktorat</td>
                            <td>Celoe</td>
                            <td>Pendanaan</td>
                            <td>2</td>
                            <td>Surat</td>
                            <td>DA-003 / Permohonan ACC</td>
                            <td><a href="#">permohonan_acc.pdf</a></td>
                            <td>remark</td>
                        </tr>
                        <!-- Tambah baris lainnya sesuai kebutuhan -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

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

<script>
    $(document).ready(function () {
        const table = $('#penolakanTable').DataTable({
            dom: '<"d-flex justify-content-between align-items-center mb-3"B>rt<"d-flex justify-content-between align-items-center mt-3"<"d-flex align-items-center"l>p>',
            buttons: [
                {
                    extend: 'excel',
                    className: 'btn btn-outline-success btn-sm',
                    title: 'Dokumen Ditolak',
                    filename: 'dokumen_ditolak'
                },
                {
                    extend: 'pdfHtml5',
                    text: 'PDF',
                    className: 'btn btn-outline-danger btn-sm',
                    title: 'Dokumen Ditolak',
                    filename: 'dokumen_ditolak',
                    orientation: 'potrait',
                    pageSize: 'A4',
                    customize: function (doc) {
                        const now = new Date();
                        const waktuCetak = now.toLocaleString('id-ID', {
                            day: '2-digit', month: '2-digit', year: 'numeric',
                            hour: '2-digit', minute: '2-digit'
                        });

                        if (doc.content[0].text === 'Dokumen Ditolak') {
                            doc.content.splice(0, 1);
                        }

                        doc.content.unshift({
                            text: 'Dokumen Ditolak',
                            alignment: 'center',
                            bold: true,
                            fontSize: 16,
                            margin: [0, 0, 0, 10]
                        });

                        // Header tetap warna abu-abu
                        doc.styles.tableHeader = {
                            fillColor: '#eaeaea',  // Warna abu-abu untuk header
                            color: '#000',
                            alignment: 'center',
                            bold: true,
                            fontSize: 9
                        };

                        // Mengubah warna body/isi tabel menjadi putih
                        doc.styles.tableBodyEven = {
                            fillColor: '#ffffff'  // Warna putih untuk baris genap
                        };
                        doc.styles.tableBodyOdd = {
                            fillColor: '#ffffff'  // Warna putih untuk baris ganjil
                        };

                        doc.defaultStyle.fontSize = 8;

                        doc.footer = function (currentPage, pageCount) {
                            return {
                                columns: [
                                    { text: waktuCetak, alignment: 'left', margin: [30, 0] },
                                    { text: '© 2025 Telkom University – Document Management System', alignment: 'center' },
                                    { text: currentPage.toString() + '/' + pageCount, alignment: 'right', margin: [0, 0, 30, 0] }
                                ],
                                fontSize: 9
                            };
                        };

                        doc.pageMargins = [30, 40, 30, 40];

                        if (doc.content[1] && doc.content[1].table) {
                            doc.content[1].table.widths = [
                                '4%', '14%', '14%', '12%', '6%', '10%', '18%', '12%', '10%'
                            ];
                            doc.content[1].layout = {
                                hLineWidth: () => 0.5,
                                vLineWidth: () => 0.5,
                                hLineColor: () => '#000000',
                                vLineColor: () => '#000000',
                                paddingLeft: () => 3,
                                paddingRight: () => 3,
                                paddingTop: () => 2,
                                paddingBottom: () => 2
                            };
                        }

                        doc.content.push({
                            text: '* Dokumen ini adalah daftar dokumen yang ditolak oleh sistem.',
                            alignment: 'left',
                            italics: true,
                            fontSize: 8,
                            margin: [0, 12, 0, 0]
                        });
                    }
                }
            ]
        });

        table.buttons().container().appendTo('.dt-buttons-container');

        const searchHtml = `
            <div class="d-flex align-items-center">
                <label class="me-2 mb-0">Search:</label>
                <input type="search" class="form-control form-control-sm" id="customSearch" style="width: 200px;">
            </div>
        `;
        $('.dt-search-container').html(searchHtml);

        $('#customSearch').on('keyup', function () {
            table.search(this.value).draw();
        });
    });
</script>

<?= $this->endSection() ?>
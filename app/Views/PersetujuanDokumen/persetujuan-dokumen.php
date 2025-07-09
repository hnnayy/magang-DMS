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
                            <a href="#" class="text-danger btn-delete" title="Delete"><i class="bi bi-trash"></i></a>
                            <a href="#" class="text-primary btn-edit" title="Edit"><i class="bi bi-pencil-square"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Dokumen</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editForm">
          <div class="row mb-3">
            <div class="col">
              <label>Fakultas/Direktorat</label>
              <input type="text" class="form-control" name="fakultas">
            </div>
            <div class="col">
              <label>Bagian/Unit/Prodi</label>
              <input type="text" class="form-control" name="bagian">
            </div>
          </div>
          <div class="mb-3">
            <label>Nama Dokumen</label>
            <input type="text" class="form-control" name="nama_dokumen">
          </div>
          <div class="row mb-3">
            <div class="col">
              <label>Revisi</label>
              <input type="text" class="form-control" name="revisi">
            </div>
            <div class="col">
              <label>Jenis Dokumen</label>
              <input type="text" class="form-control" name="jenis_dokumen">
            </div>
          </div>
          <div class="mb-3">
            <label>Kode & Nama Dokumen</label>
            <input type="text" class="form-control" name="kode_nama_dokumen">
          </div>
          <div class="mb-3">
            <label>Keterangan</label>
            <textarea class="form-control" name="keterangan"></textarea>
          </div>
          <div class="mb-3">
            <label>Unggah File (Opsional)</label>
            <input type="file" class="form-control" name="file">
            <small class="text-muted">File yang didukung: PDF, DOC, DOCX. Kosongkan jika tidak ingin mengubah file..</small>
          </div>
          <input type="hidden" name="rowIndex">
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="saveEdit">Simpan Perubahan</button>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('style') ?>
<!-- Style sama seperti sebelumnya -->
<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function () {
    const table = $('#documentTable').DataTable({
        dom: '<"row mb-3"<"col-md-6 export-buttons d-flex gap-2"B><"col-md-6 text-end"f>>rt<"row mt-3"<"col-md-6"l><"col-md-6 text-end"p>>',
        pageLength: 5,
        order: [],
        columnDefs: [
            { orderable: false, targets: 9 },
            { className: 'text-center', targets: [0, 4, 9] }
        ],
        buttons: [
            { 
                extend: 'excelHtml5', 
                text: 'Excel', 
                className: 'btn btn-success',
                title: 'Persetujuan Dokumen',             
                filename: 'persetujuan_dokumen',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] } 
            },
            {
                extend: 'pdfHtml5',
                text: 'PDF',
                className: 'btn btn-danger',
                title: 'Persetujuan Dokumen', 
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] },
                customize: function (doc) {
                    const now = new Date();
                    const waktuCetak = now.toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true,
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric'
                    });

                    if (typeof doc.content[0].text === 'string' && doc.content[0].text === 'Persetujuan Dokumen') {
                        doc.content.splice(0, 1);
                    }

                    doc.content.splice(0, 0,
                        {
                            text: 'Persetujuan Dokumen',
                            alignment: 'center',
                            bold: true,
                            fontSize: 16,
                            margin: [0, 0, 0, 10]
                        }
                    );

                    doc.styles.tableHeader = {
                        fillColor: '#ececec',
                        color: '#000000',
                        alignment: 'center',
                        bold: true,
                        fontSize: 9
                    };

                    doc.styles.tableBodyEven = { fillColor: '#ffffff' };
                    doc.styles.tableBodyOdd = { fillColor: '#ffffff' };

                    // Footer
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

                    doc.content[doc.content.length - 1].layout = {
                        hLineWidth: function () { return 0.5; },
                        vLineWidth: function () { return 0.5; },
                        hLineColor: function () { return '#000'; },
                        vLineColor: function () { return '#000'; },
                        paddingLeft: function () { return 4; },
                        paddingRight: function () { return 4; }
                    };

                    doc.content.push({
                        text: '* Dokumen ini berisi daftar dokumen yang sudah disetujui.',
                        alignment: 'left',
                        italics: true,
                        fontSize: 9,
                        margin: [0, 10, 0, 0]
                    });
                }
            }
        ]
    });

    function updateRowNumbers() {
        $('#documentTable tbody tr').each(function (index) {
            $(this).find('td').eq(0).html(index + 1);
        });
    }

    // Delete
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        const row = $(this).closest('tr');

        Swal.fire({
            icon: 'warning',
            title: 'Hapus Data?',
            text: 'Apakah anda yakin ingin menghapus data ini?',
            showCancelButton: true,
            confirmButtonColor: '#d63031',
            cancelButtonColor: '#636e72',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                table.row(row).remove().draw();
                updateRowNumbers();

                Swal.fire({
                    icon: 'success',
                    title: 'Terhapus!',
                    text: 'Data berhasil dihapus.',
                    confirmButtonColor: '#6c5ce7'
                });
            }
        });
    });

    // Edit: Show modal & fill data
    $(document).on('click', '.btn-edit', function (e) {
        e.preventDefault();
        const row = $(this).closest('tr');
        const rowIndex = table.row(row).index();
        const cells = row.find('td');

        $('#editForm [name="fakultas"]').val(cells.eq(1).text());
        $('#editForm [name="bagian"]').val(cells.eq(2).text());
        $('#editForm [name="nama_dokumen"]').val(cells.eq(3).text());
        $('#editForm [name="revisi"]').val(cells.eq(4).text());
        $('#editForm [name="jenis_dokumen"]').val(cells.eq(5).text());
        $('#editForm [name="kode_nama_dokumen"]').val(cells.eq(6).text());
        $('#editForm [name="keterangan"]').val(cells.eq(8).text());
        $('#editForm [name="rowIndex"]').val(rowIndex);

        $('#editModal').modal('show');
    });

    // Save Edit
    $('#saveEdit').on('click', function () {
        const formData = $('#editForm').serializeArray();
        const data = {};
        formData.forEach(item => data[item.name] = item.value);

        const row = table.row(data.rowIndex).nodes().to$().find('td');
        row.eq(1).text(data.fakultas);
        row.eq(2).text(data.bagian);
        row.eq(3).text(data.nama_dokumen);
        row.eq(4).text(data.revisi);
        row.eq(5).text(data.jenis_dokumen);
        row.eq(6).text(data.kode_nama_dokumen);
        row.eq(8).text(data.keterangan);

        $('#editModal').modal('hide');
        Swal.fire('Berhasil!', 'Data berhasil diperbarui.', 'success');
    });
});
</script>
<?= $this->endSection() ?>
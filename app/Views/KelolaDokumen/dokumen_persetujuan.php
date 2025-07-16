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
                            <th>Keterangan</th>
                            <th>Aksi</th>
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
                                    <?php if ($doc['filepath']): ?>
                                        <a href="<?= base_url('uploads/' . $doc['filepath']) ?>" target="_blank"><?= esc($doc['filename']) ?></a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($doc['remark']) ?></td>
                                <td class="text-nowrap">
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $doc['id'] ?>">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <form method="post" action="<?= base_url('kelola-dokumen/persetujuan/delete') ?>">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="document_id" value="<?= $doc['id'] ?>">
                                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus dokumen ini?')">
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
                                        <label class="form-label">Keterangan</label>
                                        <textarea name="remark" class="form-control"><?= esc($doc['remark']) ?></textarea>
                                      </div>
                                    </div>
                                    <div class="modal-footer">
                                      <button type="submit" class="btn btn-primary">Simpan</button>
                                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
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

<!-- DataTables + Export Scripts -->
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
        const table = $('#persetujuanTable').DataTable({
            dom: 'rt<"d-flex justify-content-between align-items-center mt-3"<"d-flex align-items-center"l>p>', // Custom DOM tanpa info (i)
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: 'Excel',
                    className: 'btn btn-outline-secondary',
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: 'PDF',
                    className: 'btn btn-outline-secondary',
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                }
            ],
            pageLength: 10, // Default entries per page
            lengthMenu: [10, 25, 50, 100], // Options for show entries
            language: {
                lengthMenu: "Show _MENU_ entries",
                paginate: {
                    previous: "Previous",
                    next: "Next"
                }
            }
        });

        // Memindahkan tombol export ke container kiri
        table.buttons().container().appendTo('.dt-buttons-container');
        
        // Membuat custom search box dan menempatkannya di container kanan
        const searchHtml = `
            <div class="d-flex align-items-center">
                <label class="me-2 mb-0">Search:</label>
                <input type="search" class="form-control form-control-sm" id="customSearch" style="width: 200px;">
            </div>
        `;
        $('.dt-search-container').html(searchHtml);
        
        // Menghubungkan custom search dengan DataTables
        $('#customSearch').on('keyup', function() {
            table.search(this.value).draw();
        });
    });
</script>

<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<!-- Custom DataTables CSS -->
<link href="<?= base_url('assets/css/datatables-custom.css') ?>" rel="stylesheet">

<?= $this->endSection() ?>


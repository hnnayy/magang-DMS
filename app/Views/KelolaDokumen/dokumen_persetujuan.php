<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="container-fluid px-4 py-4">
    <h4 class="mb-4">Persetujuan Dokumen</h4>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-start mb-3 dt-buttons-container gap-2"></div>

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
                                <td><?= esc($doc['kode_nama_dokumen'] ?? $doc['kode_dokumen_id']) ?></td>
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
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>

<script>
    $(document).ready(function () {
        const table = $('#persetujuanTable').DataTable({
            dom: 'Bfrtip',
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
            ]
        });

        table.buttons().container().appendTo('.dt-buttons-container');
    });
</script>

<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<?= $this->endSection() ?>

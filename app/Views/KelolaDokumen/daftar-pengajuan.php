<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="px-4 py-3">
        <h4 class="mb-4">Daftar Pengajuan Dokumen</h4>

        <!-- Flash message -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php elseif (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <!-- Filter card -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="searchInput" class="form-label">Cari Dokumen</label>
                        <input type="text" class="form-control" placeholder="Cari dokumen..." id="searchInput">
                    </div>
                    <div class="col-md-3">
                        <label for="filterFakultas" class="form-label">Filter Fakultas</label>
                        <select class="form-select" id="filterFakultas">
                            <option value="">Semua Fakultas</option>
                            <?php 
                            $fakultas_list = [];
                            foreach ($documents as $doc) {
                                $fid = $doc['unit_parent_id'] ?? null;
                                $fname = $doc['parent_name'] ?? '-';
                                if ($fid && !in_array($fname, $fakultas_list) && $fname !== '-') {
                                    $fakultas_list[$fid] = $fname;
                                }
                            }
                            ksort($fakultas_list);
                            foreach ($fakultas_list as $id => $fakultas): ?>
                                <option value="<?= esc($id) ?>"><?= esc($fakultas) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filterJenis" class="form-label">Filter Jenis</label>
                        <select class="form-select" id="filterJenis">
                            <option value="">Semua Jenis</option>
                            <?php foreach ($kategori_dokumen as $kategori): ?>
                                <option value="<?= esc($kategori['id']) ?>"><?= esc($kategori['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button class="btn btn-outline-secondary w-100 d-block" onclick="resetFilters()">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="table-responsive shadow-sm rounded bg-white p-3">
            <table class="table table-bordered table-hover align-middle" id="documentsTable">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width:5%;">No</th>
                        <th style="width:12%;">Fakultas</th>
                        <th style="width:10%;">Bagian</th>
                        <th style="width:15%;">Nama Dokumen</th>
                        <th style="width:10%;">No Dokumen</th>
                        <th class="text-center" style="width:8%;">Revisi</th>
                        <th style="width:10%;">Jenis</th>
                        <th style="width:12%;">Kode & Nama</th>
                        <th style="width:10%;">File</th>
                        <th style="width:10%;">Keterangan</th>
                        <th class="text-center" style="width:8%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($documents)): ?>
                        <?php foreach ($documents as $doc): ?>
                                <?php if ($doc['createdby'] != 0): ?>

                            <tr>
                                <td class="text-center"></td>
                                <td data-fakultas="<?= esc($doc['unit_parent_id'] ?? '') ?>">
                                    <?= esc($doc['parent_name'] ?? '-') ?>
                                </td>
                                <td><?= esc($doc['unit_name'] ?? '-') ?></td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;" title="<?= esc($doc['title']) ?>">
                                        <?= esc($doc['title']) ?>
                                    </div>
                                </td>
                                <td><?= esc($doc['number']) ?></td>
                                <td class="text-center"><?= esc($doc['revision']) ?></td>
                                <td data-jenis="<?= esc($doc['type']) ?>">
                                    <?php
                                        $jenis_dokumen = $doc['jenis_dokumen'] ?? '-';
                                        $badgeClass = 'bg-secondary';
                                        if (str_contains(strtolower($jenis_dokumen), 'internal')) $badgeClass = 'bg-primary';
                                        elseif (str_contains(strtolower($jenis_dokumen), 'eksternal')) $badgeClass = 'bg-success';
                                        elseif ($jenis_dokumen !== '-') $badgeClass = 'bg-info';
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= esc($jenis_dokumen) ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($doc['kode_dokumen_id'])): ?>
                                        <div>
                                            <strong><?= esc($doc['kode_dokumen_id']) ?> - <?= esc($doc['kode_dokumen_id']) ?></strong>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($doc['filepath'])): ?>
                                        <a href="<?= base_url('uploads/' . $doc['filepath']) ?>" target="_blank">
                                            <i class="bi bi-file-earmark-text"></i> <?= esc($doc['filename'] ?? $doc['filepath']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Tidak ada file</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 150px;" title="<?= esc($doc['description']) ?>">
                                        <?= esc($doc['description']) ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <button class="btn btn-sm btn-outline-primary edit-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editModal"
                                            data-id="<?= $doc['id'] ?>"
                                            data-fakultas="-"
                                            data-bagian="-"
                                            data-nama="<?= esc($doc['title']) ?>"
                                            data-nomor="<?= esc($doc['number']) ?>"
                                            data-revisi="<?= esc($doc['revision']) ?>"
                                            data-jenis="<?= esc($doc['type']) ?>"
                                            data-keterangan="<?= esc($doc['description']) ?>"
                                            data-nama-kode="<?= esc($doc['kode_dokumen_id']) ?>"
                                            data-filepath="<?= esc($doc['filepath']) ?>"
                                            title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>

                                        <button class="btn btn-sm btn-outline-success approve-btn"
                                            data-id="<?= $doc['id'] ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#approveModal"
                                            title="Approve">
                                            <i class="bi bi-check-circle"></i>
                                        </button>

                                        <form action="<?= base_url('kelola-dokumen/deletepengajuan') ?>" method="post" onsubmit="return confirm('Yakin ingin menghapus dokumen ini?')">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="document_id" value="<?= $doc['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                                <?php endif; ?>

                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                Belum ada dokumen pengajuan.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-sm">
      <form action="<?= base_url('kelola-dokumen/updatepengajuan') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="document_id" id="editDocumentId">

        <div class="modal-header border-bottom-0 pb-2">
          <h5 class="modal-title fw-bold" id="editModalLabel">Edit Dokumen</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>

        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label for="editFakultas" class="form-label">Fakultas</label>
              <select class="form-select" id="editFakultas" name="fakultas">
                <option value="">-</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="editBagian" class="form-label">Bagian</label>
              <select class="form-select" id="editBagian" name="bagian">
                <option value="">-</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="editNama" class="form-label">Nama Dokumen</label>
              <input type="text" class="form-control" name="nama" id="editNama" required>
            </div>
            <div class="col-md-3">
              <label for="editNomor" class="form-label">No Dokumen</label>
              <input type="text" class="form-control" name="nomor" id="editNomor" required>
            </div>
            <div class="col-md-3">
              <label for="editRevisi" class="form-label">Revisi</label>
              <input type="text" class="form-control" name="revisi" id="editRevisi">
            </div>
            <div class="col-md-6">
              <label for="editJenis" class="form-label">Jenis Dokumen</label>
              <input type="text" class="form-control" id="editJenis" name="jenis">
            </div>
            <div class="col-md-6">
              <label for="editNamaKode" class="form-label">Kode - Nama Dokumen</label>
              <input type="text" class="form-control" id="editNamaKode" name="kode_dokumen">
            </div>
            <div class="col-12">
              <label for="editKeterangan" class="form-label">Keterangan</label>
              <textarea class="form-control" name="keterangan" id="editKeterangan" rows="2"></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">File Dokumen</label>
              <input type="file" class="form-control" name="file_dokumen">
              <small id="currentFileName" class="text-muted d-block mt-1">File saat ini: -</small>
            </div>
          </div>
        </div>

        <div class="modal-footer border-top-0 pt-3">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i>Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>



<!-- Modal Approve -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-sm">
      <form action="<?= base_url('kelola-dokumen/approvepengajuan') ?>" method="post">
        <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
        <input type="hidden" name="document_id" id="approveDocumentId">
        <div class="modal-header border-bottom-0 pb-2">
          <h5 class="modal-title fw-bold">Persetujuan Dokumen</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="approved_by" class="form-label">Nama Pihak yang Menyetujui</label>
            <input type="text" class="form-control" name="approved_by" id="approved_by" required>
          </div>
          <div class="mb-3">
            <label for="approval_date" class="form-label">Tanggal Persetujuan</label>
            <input type="date" class="form-control" name="approval_date" id="approval_date" required>
          </div>
          <div class="mb-3">
            <label for="remarks" class="form-label">Catatan Tambahan</label>
            <textarea class="form-control" name="remarks" id="remarks" rows="3"></textarea>
          </div>
        </div>
        <!-- Modal footer -->
<div class="modal-footer border-top-0 pt-0">
  <div class="row w-100">
    <div class="col-6 pe-1">
      <button type="submit" name="action" value="disapprove" class="btn w-100 text-white" style="background-color: #b41616;">
        <i class="bi bi-x-lg me-2"></i>Not Approve
      </button>
    </div>
    <div class="col-6 ps-1">
      <button type="submit" name="action" value="approve" class="btn btn-success w-100">
        <i class="bi bi-check-lg me-2"></i>Approve
      </button>
    </div>
  </div>
</div>

      </form>
    </div>
  </div>
</div>


<!-- DataTables and Script -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    var table = $('#documentsTable').DataTable({
        pageLength: 5,
        language: {
            lengthMenu: "Show _MENU_ Entries",
            zeroRecords: "Tidak ada data yang ditemukan",
            search: "Cari:",
        },
        columnDefs: [{
            targets: 0,
            searchable: false,
            orderable: false,
            render: function(data, type, row, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
            }
        }],
        responsive: true,
        autoWidth: false
    });

    $('.dataTables_filter').hide();

    $('#searchInput').on('keyup', function() {
        table.search(this.value).draw();
    });

    $('#filterFakultas').on('change', function() {
        var val = this.value;
        table.column(1).search(val ? '^' + val + '$' : '', true, false).draw();
    });

    $('#filterJenis').on('change', function() {
        var val = this.value;
        table.column(6).search(val ? val : '', true, false).draw();
    });

   $('.edit-btn').on('click', function () {
    $('#editDocumentId').val($(this).data('id'));
    $('#editFakultas').val('-');
    $('#editBagian').val('-');
    $('#editNama').val($(this).data('nama'));
    $('#editNomor').val($(this).data('nomor'));
    $('#editRevisi').val($(this).data('revisi'));
    $('#editJenis').val($(this).data('jenis'));
    $('#editKeterangan').val($(this).data('keterangan'));
    $('#editNamaKode').val($(this).data('nama-kode'));
    
    const file = $(this).data('filepath');
    $('#currentFileName').text(file ? 'File saat ini: ' + file : 'File saat ini: -');
});



    $('.approve-button').on('click', function () {
    const id = $(this).data('id');
    $('#approveDocumentId').val(id);
});

});

function resetFilters() {
    $('#searchInput').val('');
    $('#filterFakultas').val('');
    $('#filterJenis').val('');
    var table = $('#documentsTable').DataTable();
    table.search('').columns().search('').draw();
}
</script>

<?= $this->endSection() ?>

<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pengajuan Dokumen</title>
    <link href="assets/css/daftar_pengajuan.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="px-4 py-3 w-90">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">Daftar Pengajuan Dokumen</h4>
            </div>

            <div id="flashMessages">
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        <?= session()->getFlashdata('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <?= session()->getFlashdata('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
            </div>

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
                                    $fakultas_name = $doc['parent_name'] ?? $doc['unit_name'] ?? '-';
                                    if (!in_array($fakultas_name, $fakultas_list) && $fakultas_name !== '-') {
                                        $fakultas_list[] = $fakultas_name;
                                    }
                                }
                                sort($fakultas_list);
                                foreach ($fakultas_list as $fakultas): ?>
                                    <option value="<?= esc($fakultas) ?>"><?= esc($fakultas) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filterJenis" class="form-label">Filter Jenis</label>
                            <select class="form-select" id="filterJenis">
                                <option value="">Semua Jenis</option>
                                <?php foreach ($kategori_dokumen as $kategori): ?>
                                    <option value="<?= esc($kategori['nama']) ?>"><?= esc($kategori['nama']) ?></option>
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

            <div class="table-responsive shadow-sm rounded bg-white p-3">
                <table class="table table-bordered table-hover align-middle" id="documentsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 5%;">No</th>
                            <th style="width: 12%;">Fakultas</th>
                            <th style="width: 10%;">Bagian</th>
                            <th style="width: 15%;">Nama Dokumen</th>
                            <th style="width: 10%;">No Dokumen</th>
                            <th class="text-center" style="width: 8%;">Revisi</th>
                            <th style="width: 10%;">Jenis</th>
                            <th style="width: 12%;">Kode & Nama</th>
                            <th style="width: 10%;">File</th>
                            <th style="width: 10%;">Keterangan</th>
                            <th class="text-center" style="width: 8%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($documents)): ?>
                            <?php foreach ($documents as $doc): ?>
                                <tr>
                                    <td class="text-center"></td> <!-- Will be handled by DataTables -->
                                    <td data-fakultas="<?= esc($doc['parent_name'] ?? $doc['unit_name'] ?? '-') ?>">
                                        <?= esc($doc['parent_name'] ?? $doc['unit_name'] ?? '-') ?>
                                    </td>
                                    <td><?= esc($doc['unit_name'] ?? '-') ?></td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 200px;" title="<?= esc($doc['title']) ?>">
                                            <?= esc($doc['title']) ?>
                                        </div>
                                    </td>
                                    <td><?= esc($doc['number']) ?></td>
                                    <td class="text-center"><?= esc($doc['revision']) ?></td>
                                    <td data-jenis="<?= esc($doc['jenis_dokumen'] ?? '-') ?>">
                                        <?php
                                            $jenis_dokumen = $doc['jenis_dokumen'] ?? '-';
                                            $badgeClass = 'bg-secondary';
                                            
                                            // Determine badge class based on document type
                                            if (str_contains(strtolower($jenis_dokumen), 'internal')) {
                                                $badgeClass = 'bg-primary';
                                            } elseif (str_contains(strtolower($jenis_dokumen), 'eksternal')) {
                                                $badgeClass = 'bg-success';
                                            } elseif ($jenis_dokumen !== '-') {
                                                $badgeClass = 'bg-info';
                                            }
                                        ?>
                                        <span class="badge <?= $badgeClass ?>"><?= esc($jenis_dokumen) ?></span>
                                    </td>
                                    <td>
                                        <?php if (!empty($doc['kode_dokumen']) && !empty($doc['nama_kode_dokumen'])): ?>
                                            <div>
                                                <span class="fw-bold d-block"><?= esc($doc['kode_dokumen']) ?></span>
                                                <small class="text-muted"><?= esc($doc['nama_kode_dokumen']) ?></small>
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
                                                data-fakultas="<?= esc($doc['unit_id']) ?>"
                                                data-bagian="<?= esc($doc['unit_name']) ?>"
                                                data-nama="<?= esc($doc['title']) ?>"
                                                data-nomor="<?= esc($doc['number']) ?>"
                                                data-revisi="<?= esc($doc['revision']) ?>"
                                                data-jenis="<?= esc($doc['type']) ?>"
                                                data-keterangan="<?= esc($doc['description']) ?>"
                                                data-kode="<?= esc($doc['kode_dokumen']) ?>"
                                                data-nama-kode="<?= esc($doc['nama_kode_dokumen']) ?>"
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
                                            <button class="btn btn-sm btn-outline-danger" 
                                                onclick="deleteDocument(<?= $doc['id'] ?>)"
                                                title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
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
    <!-- Modal Approve Dokumen -->
    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-sm">
                <form onsubmit="handleApproveSubmit(event)" class="p-3">
                    <input type="hidden" name="document_id" id="approveDocumentId">
                    <!-- Header -->
                    <div class="modal-header border-bottom-0 pb-2">
                        <h5 class="modal-title fw-bold" id="approveModalLabel">Persetujuan Dokumen</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <!-- Body -->
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="approved_by" class="form-label">Nama Pihak yang Menyetujui</label>
                            <input type="text" class="form-control" name="approved_by" id="approved_by" placeholder="Masukkan nama lengkap" required>
                        </div>
                        <div class="mb-3">
                            <label for="approval_date" class="form-label">Tanggal Persetujuan</label>
                            <input type="date" class="form-control" name="approval_date" id="approval_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="remarks" class="form-label">Catatan Tambahan</label>
                            <textarea class="form-control" name="remarks" id="remarks" rows="3" placeholder="Masukkan catatan tambahan jika ada..."></textarea>
                        </div>
                    </div>
                    <!-- Footer -->
                    <div class="modal-footer border-top-0 pt-0">
                        <div class="row w-100">
                            <div class="col-6 pe-1">
                                <button type="button" class="btn w-100 text-white" style="background-color: #b41616;" data-bs-dismiss="modal">
                                    Not Approve
                                </button>
                            </div>
                            <div class="col-6 ps-1">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-check-lg me-2"></i>Approve
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
   <!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h5 class="modal-title" id="editModalLabel">Edit Dokumen</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form method="POST" action="<?= base_url('kelola-dokumen/edit') ?>" enctype="multipart/form-data">
        <div class="modal-body py-2">
          <input type="hidden" name="document_id" id="editDocumentId">

          <div class="row">
            <div class="col-md-6 mb-2">
              <label for="editFakultas" class="form-label">Fakultas</label>
              <select class="form-select form-select-sm" name="fakultas" id="editFakultas" required>
                <option value="">Pilih Fakultas</option>
                <?php foreach ($fakultas_list as $fakultas): ?>
                  <option value="<?= esc($fakultas) ?>"><?= esc($fakultas) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6 mb-2">
              <label for="editBagian" class="form-label">Bagian</label>
              <input type="text" class="form-control form-control-sm" name="bagian" id="editBagian" required>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-2">
              <label for="editNama" class="form-label">Nama Dokumen</label>
              <input type="text" class="form-control form-control-sm" name="nama" id="editNama" required>
            </div>
            <div class="col-md-3 mb-2">
              <label for="editNomor" class="form-label">No Dokumen</label>
              <input type="text" class="form-control form-control-sm" name="nomor" id="editNomor" required>
            </div>
            <div class="col-md-3 mb-2">
              <label for="editRevisi" class="form-label">Revisi</label>
              <input type="text" class="form-control form-control-sm" name="revisi" id="editRevisi" required>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-2">
              <label for="editJenis" class="form-label">Jenis Dokumen</label>
              <select class="form-select form-select-sm" name="jenis" id="editJenis" required>
                <option value="">Pilih Jenis</option>
                <?php foreach ($kategori_dokumen as $kategori): ?>
                  <option value="<?= $kategori['id'] ?>"><?= esc($kategori['nama']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6 mb-2">
              <label for="editkodeNamadok" class="form-label">Kode - Nama Dokumen</label>
              <input type="text" class="form-control form-control-sm" name="nama" id="editkodeNamadok" required>
            </div>
          </div>

          <div class="mb-2">
            <label for="editKeterangan" class="form-label">Keterangan</label>
            <textarea class="form-control form-control-sm" name="keterangan" id="editKeterangan" rows="2"></textarea>
          </div>

          <div class="mb-2">
            <label for="editFile" class="form-label">File Dokumen</label>
            <input type="file" class="form-control form-control-sm" name="file" id="editFile" accept=".pdf,.doc,.docx,.xls,.xlsx">
            <small class="form-text text-muted"><span id="currentFileName"></span></small>
          </div>
        </div>

        <div class="modal-footer py-2">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary btn-sm">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

    
    <!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-sm">
      <form method="POST" action="<?= base_url('kelola-dokumen/edit') ?>" enctype="multipart/form-data" class="p-3">
        <input type="hidden" name="document_id" id="editDocumentId">

        <!-- Header -->
        <div class="modal-header border-bottom-0 pb-2">
          <h5 class="modal-title fw-bold" id="editModalLabel">Edit Dokumen</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>

        <!-- Body -->
        <div class="modal-body pt-1">
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="editFakultas" class="form-label">Fakultas</label>
              <select class="form-select form-select-sm" name="fakultas" id="editFakultas" required>
                <option value="">Pilih Fakultas</option>
                <?php foreach ($fakultas_list as $fakultas): ?>
                  <option value="<?= esc($fakultas) ?>"><?= esc($fakultas) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label for="editBagian" class="form-label">Bagian</label>
              <input type="text" class="form-control form-control-sm" name="bagian" id="editBagian" required>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label for="editNama" class="form-label">Nama Dokumen</label>
              <input type="text" class="form-control form-control-sm" name="nama" id="editNama" required>
            </div>
            <div class="col-md-3">
              <label for="editNomor" class="form-label">No Dokumen</label>
              <input type="text" class="form-control form-control-sm" name="nomor" id="editNomor" required>
            </div>
            <div class="col-md-3">
              <label for="editRevisi" class="form-label">Revisi</label>
              <input type="text" class="form-control form-control-sm" name="revisi" id="editRevisi" required>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label for="editJenis" class="form-label">Jenis Dokumen</label>
              <select class="form-select form-select-sm" name="jenis" id="editJenis" required>
                <option value="">Pilih Jenis</option>
                <?php foreach ($kategori_dokumen as $kategori): ?>
                  <option value="<?= $kategori['id'] ?>"><?= esc($kategori['nama']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label for="editkodeNamadok" class="form-label">Kode - Nama Dokumen</label>
              <input type="text" class="form-control form-control-sm" name="nama" id="editkodeNamadok" required>
            </div>
          </div>

          <div class="mb-3">
            <label for="editKeterangan" class="form-label">Keterangan</label>
            <textarea class="form-control form-control-sm" name="keterangan" id="editKeterangan" rows="2"></textarea>
          </div>

          <div class="mb-3">
            <label for="editFile" class="form-label">File Dokumen</label>
            <input type="file" class="form-control form-control-sm" name="file" id="editFile" accept=".pdf,.doc,.docx,.xls,.xlsx">
            <small class="form-text text-muted"><span id="currentFileName"></span></small>
          </div>
        </div>

        <!-- Footer -->
        <div class="modal-footer border-top-0 pt-0">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary btn-sm">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>


<script>
$(document).ready(function() {
    // Initialize DataTables with proper configuration
    var table = $('#documentsTable').DataTable({
        "pageLength": 10,
        "language": {
            "lengthMenu": "Show _MENU_ Entries",
            "zeroRecords": "Tidak ada data yang ditemukan",
            "search": "Cari:",
        },
        "columnDefs": [
            {
                "targets": 0,
                "searchable": false,
                "orderable": false,
                "render": function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            {
                "targets": -1, // Last column (Actions)
                "orderable": false,
                "searchable": false
            }
        ],
        "responsive": true,
        "autoWidth": false,
        "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
               '<"row"<"col-sm-12"tr>>' +
               '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        "drawCallback": function(settings) {
           
        }
    });

    // Hide default DataTables search box since we have custom filters
    $('.dataTables_filter').hide();

    // Custom search functionality
    $('#searchInput').on('keyup', function() {
        table.search(this.value).draw();
    });

    // Custom filtering for Fakultas
    $('#filterFakultas').on('change', function() {
        var val = this.value;
        table.column(1).search(val ? '^' + val + '$' : '', true, false).draw();
    });

    // Custom filtering for Jenis
    $('#filterJenis').on('change', function() {
        var val = this.value;
        table.column(6).search(val ? val : '', true, false).draw();
    });

    // Edit button functionality
    $('.edit-btn').on('click', function() {
        $('#editDocumentId').val($(this).data('id'));
        $('#editFakultas').val($(this).data('fakultas'));
        $('#editBagian').val($(this).data('bagian'));
        $('#editNama').val($(this).data('nama'));
        $('#editNomor').val($(this).data('nomor'));
        $('#editRevisi').val($(this).data('revisi'));
        $('#editJenis').val($(this).data('jenis'));
        $('#editKodeDokumen').val($(this).data('kode'));
        $('#editNamaKodeDokumen').val($(this).data('nama-kode'));
        $('#editKeterangan').val($(this).data('keterangan'));
        
        // Display current file name
        var currentFile = $(this).data('filepath');
        if (currentFile) {
            $('#currentFileName').text('File saat ini: ' + currentFile);
        } else {
            $('#currentFileName').text('Tidak ada file');
        }
    });

    // Approve button functionality
    $('.approve-btn').on('click', function() {
        $('#approveDocumentId').val($(this).data('id'));
        // Set today's date as default
        $('#approval_date').val(new Date().toISOString().split('T')[0]);
    });

    // Auto-hide flash messages after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});

function resetFilters() {
    $('#searchInput').val('');
    $('#filterFakultas').val('');
    $('#filterJenis').val('');
    
    // Reset DataTables filters
    var table = $('#documentsTable').DataTable();
    table.search('').columns().search('').draw();
}

function handleApproveSubmit(event) {
    event.preventDefault();
    
    var formData = new FormData(event.target);
    
    $.ajax({
        url: '<?= base_url('kelola-dokumen/approve') ?>',
        method: 'POST',
        data: {
            document_id: formData.get('document_id'),
            approved_by: formData.get('approved_by'),
            approval_date: formData.get('approval_date'),
            remarks: formData.get('remarks')
        },
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        success: function(response) {
            if (response.success) {
                alert('Dokumen berhasil disetujui.');
                $('#approveModal').modal('hide');
                location.reload();
            } else {
                alert('Gagal menyetujui dokumen: ' + (response.message || 'Unknown error'));
            }
        },
        error: function(xhr) {
            console.error('Error:', xhr);
            alert('Terjadi kesalahan saat menyetujui dokumen.');
        }
    });
}

function deleteDocument(id) {
    if (!confirm('Yakin ingin menghapus dokumen ini?')) return;
    
    $.ajax({
        url: '<?= base_url('kelola-dokumen/delete/') ?>' + id,
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        success: function(response) {
            if (response.success) {
                alert(response.message);
                location.reload();
            } else {
                alert('Gagal menghapus dokumen: ' + (response.message || 'Unknown error'));
            }
        },
        error: function(xhr) {
            console.error('Error:', xhr);
            alert('Terjadi kesalahan saat menghapus dokumen.');
        }
    });
}
</script>

</body>
</html>

<?= $this->endSection() ?>
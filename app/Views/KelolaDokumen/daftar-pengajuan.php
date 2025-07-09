<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pengajuan Dokumen</title>
    <link href="assets/css/daftar_pengajuan.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="px-4 py-3 w-90">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">
                    Daftar Pengajuan Dokumen
                </h4>
            </div>

            <!-- Flash Messages -->
            <div id="flashMessages">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    Dokumen berhasil disetujui dan dipindahkan ke daftar dokumen.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" placeholder="Cari dokumen..." id="searchInput">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="filterFakultas">
                                <option value="">Semua Fakultas</option>
                                <option value="FT">Fakultas Teknik</option>
                                <option value="FE">Fakultas Ekonomi</option>
                                <option value="FMIPA">Fakultas MIPA</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="filterJenis">
                                <option value="">Semua Jenis</option>
                                <option value="internal">Internal</option>
                                <option value="eksternal">Eksternal</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-outline-secondary w-100" onclick="resetFilters()">
                                <i class="bi bi-arrow-counterclockwise"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documents Table -->
            <div class="table-responsive shadow-sm rounded bg-white p-3">
                <table class="table table-bordered table-hover align-middle" id="documentsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">No</th>
                            <th>Fakultas</th>
                            <th>Bagian</th>
                            <th>Nama Dokumen</th>
                            <th>No Dokumen</th>
                            <th class="text-center">Revisi</th>
                            <th>Jenis</th>
                            <th>Kode & Nama</th>
                            <th>File</th>
                            <th>Keterangan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
<?php if (!empty($documents)): ?>
    <?php $no = 1; foreach ($documents as $doc): ?>
    <tr>
        <td class="text-center"><?= $no++ ?></td>
        <td><?= esc($doc['fakultas_name'] ?? '-') ?></td>
<td><?= esc($doc['unit_name'] ?? '-') ?></td>

        <td><?= esc($doc['title']) ?></td>
        <td><?= esc($doc['number']) ?></td>
        <td class="text-center"><?= esc($doc['revision']) ?></td>
       <td>
   <?php
$jenis_dokumen = '-';
$badgeClass = 'bg-secondary';

foreach ($kategori_dokumen as $kategori) {
    if ($kategori['id'] == $doc['type']) {
        $jenis_dokumen = esc($kategori['nama']);

        if (str_contains(strtolower($kategori['nama']), 'internal')) {
            $badgeClass = 'bg-primary';
        } elseif (str_contains(strtolower($kategori['nama']), 'eksternal')) {
            $badgeClass = 'bg-success';
        } else {
            $badgeClass = 'bg-info';
        }
        break;
    }
}
?>
<span class="badge <?= $badgeClass ?>">
    <?= $jenis_dokumen ?>
</span>

</td>


<td>
    <?php if (!empty($doc['kode_dokumen']) && !empty($doc['nama_kode_dokumen'])): ?>
        <span class="d-block fw-bold"><?= esc($doc['kode_dokumen']) ?></span>
        <span class="text-muted"><?= esc($doc['nama_kode_dokumen']) ?></span>
    <?php else: ?>
        <span class="text-muted">-</span>
    <?php endif; ?>
</td>

        <td>
            <?php if (!empty($doc['filepath'])): ?>
                <a href="<?= base_url('uploads/' . $doc['filepath']) ?>" target="_blank">
                    <i class="bi bi-file-earmark-text"></i> <?= esc($doc['filepath']) ?>
                </a>
            <?php else: ?>
                <span class="text-muted">Tidak ada file</span>
            <?php endif; ?>
        </td>
        <td><?= esc($doc['description']) ?></td>
        <td class="text-center">
            <div class="d-flex justify-content-center gap-2">
                <button class="btn btn-sm btn-outline-primary edit-btn" 
                        data-bs-toggle="modal" 
                        data-bs-target="#editModal" 
                        data-id="<?= $doc['id'] ?>"
                        data-fakultas="<?= esc($doc['unit_id']) ?>"
                        data-nama="<?= esc($doc['title']) ?>"
                        data-nomor="<?= esc($doc['number']) ?>"
                        data-revisi="<?= esc($doc['revision']) ?>"
                        data-jenis="<?= esc($doc['type']) ?>"


                        data-keterangan="<?= esc($doc['description']) ?>"
                        data-kode="-"  <!-- Placeholder -->
                        
                    <i class="bi bi-pencil-square"></i>
                </button>
                <button class="btn btn-sm btn-outline-success approve-btn" 
                        data-bs-toggle="modal" 
                        data-bs-target="#approveModal"
                        data-id="<?= $doc['id'] ?>"
                        title="Approve">
                    <i class="bi bi-check-lg"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" 
                        title="Hapus" 
                        onclick="deleteDocument(<?= $doc['id'] ?>)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </td>
    </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="11" class="text-center text-muted">Belum ada dokumen pengajuan.</td>
    </tr>
<?php endif; ?>
</tbody>

                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Menampilkan 1-10 dari 15 dokumen
                </div>
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1">Previous</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Modal Edit Dokumen -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <form action="<?= base_url('/kelola-dokumen/edit') ?>" method="post" enctype="multipart/form-data">

                    <input type="hidden" name="document_id" id="editDocumentId">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">
                            <i class="bi bi-pencil-square me-2"></i>Edit Dokumen
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Fakultas/Direktorat</label>
                                <input type="text" class="form-control" name="fakultas" id="editFakultas" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Bagian/Unit</label>
                                <input type="text" class="form-control" name="bagian" id="editBagian" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nama Dokumen</label>
                                <input type="text" class="form-control" name="nama" id="editNama" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nomor Dokumen</label>
                                <input type="text" class="form-control" name="nomor" id="editNomor" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Revisi</label>
                                <input type="text" class="form-control" name="revisi" id="editRevisi" required>
                            </div>
                            <div class="col-md-6">
    <label class="form-label">Jenis Dokumen</label>
    <select class="form-select" name="jenis" id="editJenis" required>
    <option value="">-- Pilih Jenis --</option>
    <?php foreach ($kategori_dokumen as $kategori): ?>
        <option value="<?= esc($kategori['id']) ?>">
            <?= esc($kategori['nama']) ?>
        </option>
    <?php endforeach; ?>
</select>
</div>
                            <div class="col-md-6">
                                <label class="form-label">Kode & Nama Dokumen</label>
                                <input type="text" class="form-control" name="kode_nama" id="editKode" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Keterangan</label>
                                <textarea class="form-control" name="keterangan" id="editKeterangan" rows="3"></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Unggah File (Opsional)</label>
                                <input type="file" class="form-control" name="file" id="editFile" accept=".pdf,.doc,.docx">
                                <div class="form-text">File yang didukung: PDF, DOC, DOCX. Kosongkan jika tidak ingin mengubah file.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="row w-100">
                            <div class="col-6 pe-2">
                    <button type="button" class="btn w-100 text-white" style="background-color: #b41616;" data-bs-dismiss="modal">
                        Batal
                    </button>
                </div>

                            <div class="col-6 ps-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-check-lg me-2"></i>Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </div>


                </form>
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


    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/daftar_pengajuan.js"></script>
</body>
</html>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle edit button click
    const editButtons = document.querySelectorAll('.edit-btn');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const fakultas = this.getAttribute('data-fakultas'); // unit_id
            const nama = this.getAttribute('data-nama');
            const nomor = this.getAttribute('data-nomor');
            const revisi = this.getAttribute('data-revisi');
            const jenis = this.getAttribute('data-jenis'); // document_type_id
            const keterangan = this.getAttribute('data-keterangan');
            const kode = this.getAttribute('data-kode');

            // Fill modal fields
            document.getElementById('editDocumentId').value = id;
            document.getElementById('editFakultas').value = fakultas;
            document.getElementById('editBagian').value = ''; // Adjust if bagian is available
            document.getElementById('editNama').value = nama;
            document.getElementById('editNomor').value = nomor;
            document.getElementById('editRevisi').value = revisi;
            document.getElementById('editJenis').value = jenis;
            document.getElementById('editKeterangan').value = keterangan;
            document.getElementById('editKode').value = kode;
        });
    });
});
</script>

<?= $this->endSection() ?>


<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<!-- Bootstrap CSS & Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<div class="px-4 py-3 w-100">
    <h4>Daftar Pengajuan</h4>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="table-responsive shadow-sm rounded bg-white p-3">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th class="text-center">No</th>
                    <th>Fakultas</th>
                    <th>Bagian</th>
                    <th>Nama Dokumen</th>
                    <th class="text-center">Revisi</th>
                    <th>Jenis</th>
                    <th>Kode & Nama</th>
                    <th>File</th>
                    <th>Keterangan</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($documents as $i => $doc): ?>
                <tr>
                    <td class="text-center"><?= $i + 1 ?></td>
                    <td><?= esc($doc['fakultas']) ?></td>
                    <td><?= esc($doc['bagian']) ?></td>
                    <td><?= esc($doc['nama']) ?></td>
                    <td class="text-center"><?= esc($doc['revisi']) ?></td>
                    <td><?= esc($doc['jenis']) ?></td>
                    <td><?= esc($doc['kode_nama']) ?></td>
                    <td><?= esc($doc['file']) ?></td>
                    <td><?= esc($doc['keterangan']) ?></td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-2">
                            
                            <!-- Tombol Edit (modal) -->
                            <a href="#" class="text-primary edit-btn" 
                               data-bs-toggle="modal" 
                               data-bs-target="#editModal" 
                               data-id="<?= $doc['id'] ?>"
                               data-fakultas="<?= esc($doc['fakultas']) ?>"
                               data-bagian="<?= esc($doc['bagian']) ?>"
                               data-nama="<?= esc($doc['nama']) ?>"
                               data-revisi="<?= esc($doc['revisi']) ?>"
                               data-jenis="<?= esc($doc['jenis']) ?>"
                               data-kode="<?= esc($doc['kode_nama']) ?>"
                               data-keterangan="<?= esc($doc['keterangan']) ?>"
                               title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </a>

                            <!-- Tombol Approve -->
                            <a href="#" class="text-success approve-btn" 
                               data-bs-toggle="modal" 
                               data-bs-target="#approveModal"
                               data-id="<?= $doc['id'] ?>"
                               title="Approve">
                                <i class="bi bi-check-lg"></i>
                            </a>

                            <!-- Tombol Delete -->
                            <a href="<?= base_url('dokumen/delete/' . $doc['id']) ?>" 
                               class="text-danger" 
                               title="Hapus" 
                               onclick="return confirm('Yakin ingin menghapus dokumen ini?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Edit Dokumen -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <form id="editDocumentForm" action="<?= base_url('dokumen/edit') ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="document_id" id="editDocumentId">
        <div class="modal-header">
          <h5 class="modal-title" id="editModalLabel">Edit Dokumen</h5>
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
              <input type="text" class="form-control" name="nomor" id="editnomor" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Revisi</label>
              <input type="text" class="form-control" name="revisi" id="editRevisi" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Jenis Dokumen</label>
              <select class="form-select" name="jenis" id="editJenis" required>
                <option value="">-- Pilih Jenis --</option>
                <option value="internal">Internal</option>
                <option value="eksternal">Eksternal</option>
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
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Approve Dokumen -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <form action="<?= base_url('dokumen/approve') ?>" method="post">
        <input type="hidden" name="document_id" id="approveDocumentId">
        <div class="modal-header">
          <h5 class="modal-title">Konfirmasi Approval</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="remark" class="form-label">Catatan/Remark</label>
            <textarea 
                class="form-control" 
                name="remark" 
                id="remark" 
                rows="3" 
                placeholder="Tulis catatan persetujuan (opsional)..."
            ></textarea>
            </div>

            <div class="mb-3">
            <label for="approved_by" class="form-label">Disetujui Oleh</label>
            <input 
                type="text" 
                class="form-control" 
                name="approved_by" 
                id="approved_by" 
                placeholder="Tulis nama pihak yang menyetujui"
            >
            </div>

          <div class="alert alert-info small">
            <i class="bi bi-info-circle"></i>
            Setelah disetujui, dokumen akan dipindahkan ke menu <strong>Daftar Dokumen</strong> dan tidak bisa diedit lagi.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success">
            <i class="bi bi-check-lg"></i> Setujui
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle Edit Button Click
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const fakultas = this.getAttribute('data-fakultas');
            const bagian = this.getAttribute('data-bagian');
            const nama = this.getAttribute('data-nama');
            const revisi = this.getAttribute('data-revisi');
            const jenis = this.getAttribute('data-jenis');
            const kode = this.getAttribute('data-kode');
            const keterangan = this.getAttribute('data-keterangan');

            // Populate form fields
            document.getElementById('editDocumentId').value = id;
            document.getElementById('editFakultas').value = fakultas;
            document.getElementById('editBagian').value = bagian;
            document.getElementById('editNama').value = nama;
            document.getElementById('editRevisi').value = revisi;
            document.getElementById('editJenis').value = jenis;
            document.getElementById('editKode').value = kode;
            document.getElementById('editKeterangan').value = keterangan;
        });
    });

    // Handle Approve Button Click
    document.querySelectorAll('.approve-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const documentId = this.getAttribute('data-id');
            document.getElementById('approveDocumentId').value = documentId;
        });
    });

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});
</script>

<?= $this->endSection() ?>
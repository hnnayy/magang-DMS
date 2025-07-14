<!-- Modal Edit Dokumen -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h5 class="modal-title" id="editModalLabel">Edit Dokumen</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="<?= base_url('kelola-dokumen/updatepengajuan') ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
        <input type="hidden" name="document_id" id="editDocumentId">

        <div class="modal-body py-2">
          <div class="row">
            <div class="col-md-6 mb-2">
              <label for="editFakultas" class="form-label">Fakultas</label>
              <select class="form-select" name="fakultas" id="editFakultas" required>
                <option value="">Pilih Fakultas</option>
                <?php foreach ($unit_parents as $parent): ?>
                  <option value="<?= $parent['id'] ?>"><?= esc($parent['name']) ?></option>
                <?php endforeach ?>
              </select>
            </div>
            <div class="col-md-6 mb-2">
              <label for="editBagian" class="form-label">Bagian</label>
              <select class="form-select" name="bagian" id="editBagian" required>
                <option value="">Pilih Bagian</option>
                <?php foreach ($units as $unit): ?>
                  <option value="<?= $unit['id'] ?>"><?= esc($unit['name']) ?></option>
                <?php endforeach ?>
              </select>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-2">
              <label for="editNama" class="form-label">Nama Dokumen</label>
              <input type="text" class="form-control" name="nama" id="editNama" required>
            </div>
            <div class="col-md-3 mb-2">
              <label for="editNomor" class="form-label">No Dokumen</label>
              <input type="text" class="form-control" name="nomor" id="editNomor" required>
            </div>
            <div class="col-md-3 mb-2">
              <label for="editRevisi" class="form-label">Revisi</label>
              <input type="text" class="form-control" name="revisi" id="editRevisi" required>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-2">
              <label for="editJenis" class="form-label">Jenis Dokumen</label>
              <select class="form-select" name="jenis" id="editJenis" required>
                <option value="">Pilih Jenis</option>
                <?php foreach ($kategori_dokumen as $kategori): ?>
                  <option value="<?= $kategori['id'] ?>"><?= esc($kategori['nama']) ?></option>
                <?php endforeach ?>
              </select>
            </div>
            <div class="col-md-6 mb-2">
              <label for="editKodeNama" class="form-label">Kode - Nama Dokumen</label>
              <input type="text" class="form-control" name="kode_nama" id="editKodeNama" required>
            </div>
          </div>
          <div class="mb-2">
            <label for="editKeterangan" class="form-label">Keterangan</label>
            <textarea class="form-control" name="keterangan" id="editKeterangan" rows="2"></textarea>
          </div>
          <div class="mb-2">
            <label for="editFile" class="form-label">File Dokumen</label>
            <input type="file" class="form-control" name="file" id="editFile" accept=".pdf,.doc,.docx,.xls,.xlsx">
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

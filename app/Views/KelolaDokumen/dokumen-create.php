<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="form-section">
            <div class="form-section-divider">
                <h2>Tambah Dokumen</h2>
            </div

        <form id="addDocumentForm">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="fakultas-direktorat">Fakultas/Direktorat</label>
                    <input type="text" id="fakultas-direktorat" name="fakultas-direktorat" class="form-input" placeholder="Tulis Fakultas disini..." required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="bagian">Bagian/Unit/Program Studi</label>
                    <input type="text" id="bagian" name="bagian" class="form-input" placeholder="Tulis Bagian disini..." required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="nama-dokumen">Nama Dokumen</label>
                <input type="text" id="nama-dokumen" name="nama-dokumen" class="form-input" placeholder="Tulis Nama Dokumen disini..." required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="jenis-dokumen">Jenis Dokumen</label>
                    <select id="jenis-dokumen" name="jenis-dokumen" class="form-input" onchange="updateKodeOptions()" required>
                        <option value="">-- Pilih Jenis --</option>
                        <option value="internal">Internal</option>
                        <option value="eksternal">Eksternal</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="kode-dokumen">Kode & Nama Dokumen</label>
                    <select id="kode-dokumen" name="kode-dokumen" class="form-input" required>
                        <option value="">-- Pilih Dokumen --</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="revisi-dokumen">Revisi Dokumen</label>
                <input type="text" id="revisi-dokumen" name="revisi-dokumen" class="form-input" placeholder="Tulis Revisi Dokumen disini..." required>
            </div>

            <div class="form-group">
                <label class="form-label" for="keterangan">Keterangan</label>
                <textarea id="keterangan" name="keterangan" class="form-input" rows="1" placeholder="Tulis Keterangan disini..." required></textarea>
            </div>

            <div class="form-group">
                <label class="form-label" for="file-upload">Unggah Berkas</label>

                <div class="upload-area" id="uploadArea">
                    <div class="upload-icon"></div>
                    <div class="upload-text">
                        <button type="button" class="choose-file-btn" id="chooseFileBtn">Choose File</button>
                        <span class="no-file-text" id="noFileText">No file chosen</span>
                    </div>
                    <p style="font-size: 12px; color: #a0aec0; margin-top: 6px;">
                        atau seret dan lepas file di sini
                    </p>
                </div>

                <input type="file" id="fileInput" class="file-input" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xlsx" hidden>

                <div class="file-info" id="fileInfo">
                    <div class="file-details">
                        <div class="file-icon" id="fileIcon"></div>
                        <div class="file-text-info">
                            <div class="file-name" id="fileName"></div>
                            <div class="file-size" id="fileSize"></div>
                        </div>
                        <button type="button" class="remove-btn" id="removeBtn" title="Hapus file">×</button>
                    </div>
                </div>

                <div class="error-message" id="errorMessage"></div>
                <div class="success-message" id="successMessage"></div>

                <div class="file-requirements">
                    <div class="requirements-title"></div>
                    <div class="requirements-text">
                        File Upload .doc, .docx, .xlsx, .pdf, .jpg, .png
                    </div>
                </div>
            </div>

            <button type="submit" class="submit-btn">Submit</button>
        </form>
    </div>

    <div class="illustration-section">
        <img src="<?= base_url('assets/images/profil/Logo_Telkom_University.png') ?>" alt="User Illustration" class="illustration-img">
    </div>
</div>



<?= $this->endSection() ?>
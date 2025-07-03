<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="form-section">
        <div class="form-section-divider">
            <h2>Tambah Dokumen</h2>
        </div>

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
                    <select id="jenis-dokumen" name="jenis-dokumen" class="form-input" onchange="handleJenisChange()" required>
                        <option value="">-- Pilih Jenis --</option>
                        <?php foreach ($kategori_dokumen as $kategori): ?>
                        <option value="<?= $kategori['kode'] ?>" data-use-predefined="<?= $kategori['use_predefined_codes'] ? 'true' : 'false' ?>">
                            <?= $kategori['nama'] ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Untuk jenis dengan predefined codes -->
                <div class="form-group" id="kode-dokumen-group">
                    <label class="form-label" for="kode-dokumen">Kode- Nama Dokumen</label>
                    <select id="kode-dokumen" name="kode-dokumen" class="form-input">
                        <option value="">-- Pilih Dokumen --</option>
                    </select>
                </div>

                <!-- Untuk jenis tanpa predefined codes -->
                <div class="form-group" id="kode-dokumen-custom-group" style="display: none;">
                    <label class="form-label" for="kode-dokumen-custom">Kode & Nama Dokumen</label>
                    <input type="text" id="kode-dokumen-custom" name="kode-dokumen-custom" class="form-input" placeholder="Masukkan kode dan nama dokumen...">
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

<script>
function handleJenisChange() {
    const jenisSelect = document.getElementById('jenis-dokumen');
    const selectedOption = jenisSelect.options[jenisSelect.selectedIndex];
    const usePredefined = selectedOption.getAttribute('data-use-predefined') === 'true';
    
    const kodeGroup = document.getElementById('kode-dokumen-group');
    const kodeCustomGroup = document.getElementById('kode-dokumen-custom-group');
    const kodeSelect = document.getElementById('kode-dokumen');
    const kodeCustomInput = document.getElementById('kode-dokumen-custom');
    
    if (usePredefined) {
        // Show predefined codes dropdown
        kodeGroup.style.display = 'block';
        kodeCustomGroup.style.display = 'none';
        kodeSelect.required = true;
        kodeCustomInput.required = false;
        
        // Load kode dokumen options
        loadKodeDokumen(jenisSelect.value);
    } else {
        // Show custom input
        kodeGroup.style.display = 'none';
        kodeCustomGroup.style.display = 'block';
        kodeSelect.required = false;
        kodeCustomInput.required = true;
        
        // Clear predefined options
        kodeSelect.innerHTML = '<option value="">-- Pilih Dokumen --</option>';
    }
}

function loadKodeDokumen(jenis) {
    const kodeSelect = document.getElementById('kode-dokumen');
    
    // Clear existing options
    kodeSelect.innerHTML = '<option value="">-- Pilih Dokumen --</option>';
    
    // AJAX call to get kode dokumen
    fetch('<?= base_url('dokumen/get-kode-dokumen') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'jenis=' + encodeURIComponent(jenis)
    })
    .then(response => response.json())
    .then(data => {
        data.forEach(item => {
            const option = document.createElement('option');
            option.value = item.kode + ' - ' + item.nama;
            option.textContent = item.kode + ' - ' + item.nama;
            kodeSelect.appendChild(option);
        });
    })
    .catch(error => {
        console.error('Error loading kode dokumen:', error);
    });
}

// Initialize form on page load
document.addEventListener('DOMContentLoaded', function() {
    // Hide both kode groups initially
    document.getElementById('kode-dokumen-group').style.display = 'none';
    document.getElementById('kode-dokumen-custom-group').style.display = 'none';
});
</script>

<?= $this->endSection() ?>
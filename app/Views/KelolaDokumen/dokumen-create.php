<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<!-- Bootstrap CDN untuk bagian upload -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container">
    <div class="form-section">
        <div class="form-section-divider">
            <h2>Tambah Dokumen</h2>
        </div>
    
        <form id="addDocumentForm" class="needs-validation" novalidate action="<?= base_url('create-document/store') ?>" method="post" enctype="multipart/form-data">

            <?= csrf_field() ?>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="fakultas-direktorat">Fakultas/Direktorat</label>
                    <input type="text" id="fakultas-direktorat" class="form-input" value="<?= $unit['parent_name'] ?? '' ?>" readonly>
                    <input type="hidden" name="fakultas" value="<?= $unit['parent_name'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="bagian">Bagian/Unit/Program Studi</label>
                    <input type="text" id="bagian" class="form-input" value="<?= $unit['name'] ?? '-' ?>" readonly>
                    <input type="hidden" name="unit_id" value="<?= $unit['id'] ?? '' ?>" required>
                </div>
            </div>
            
            <!-- Nama Dokumen -->
            <div class="form-group">
                <label class="form-label" for="nama-dokumen">Nama Dokumen</label>
                <input type="text" id="nama-dokumen" name="nama-dokumen" class="form-input" 
                    required pattern="^[a-zA-Z0-9 ]+$" placeholder="Nama Dokumen">
                <div class="invalid-feedback">
                    Nama Dokumen hanya boleh berisi huruf, angka, dan spasi.
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="jenis-dokumen">Jenis Dokumen</label>
                    <select id="jenis-dokumen" name="jenis" class="form-input" onchange="handleJenisChange()" required>
                        <option value="" disabled selected hidden>-- Pilih Jenis --</option>
                        <?php foreach ($kategori_dokumen as $kategori): ?>
                        <option value="<?= $kategori['id'] ?>" data-kode="<?= $kategori['kode'] ?>" data-use-predefined="<?= $kategori['use_predefined_codes'] ? 'true' : 'false' ?>">
                            <?= $kategori['nama'] ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Untuk predefined codes -->
                <div class="form-group" id="kode-dokumen-group">
                    <label class="form-label" for="kode-dokumen">Kode-Nama Dokumen</label>
                    <select id="kode-dokumen" name="kode_dokumen_id" class="form-input">
                        <option value="">-- Pilih Dokumen --</option>
                    </select>
                </div>
            </div>

            <!-- Untuk non-predefined codes - pisah menjadi 2 input terpisah -->
            <div class="form-row" id="kode-dokumen-custom-group" style="display: none;">
                <div class="form-group">
                    <label class="form-label" for="kode-dokumen-custom">Kode Dokumen</label>
                    <input type="text" id="kode-dokumen-custom" name="kode-dokumen-custom" class="form-input" 
                           placeholder="Masukkan kode dokumen..." 
                           oninput="this.value = this.value.toUpperCase()">
                    <div class="invalid-feedback">
                        Kode dokumen wajib diisi.
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="nama-dokumen-custom">Nama Dokumen</label>
                    <input type="text" id="nama-dokumen-custom" name="nama-dokumen-custom" class="form-input" 
                           placeholder="Masukkan nama dokumen...">
                    <div class="invalid-feedback">
                        Nama dokumen wajib diisi.
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="date-published">Tanggal Terbit</label>
                <input type="date" id="date-published" name="date_published" class="form-input" required>
            </div>
            
            <!-- Nomor Dokumen -->
            <div class="form-group">
                <label class="form-label" for="no-dokumen">Nomor Dokumen</label>
                <input type="text" id="no-dokumen" name="no-dokumen" class="form-input"
                    required pattern="^[^\s]+$"
                    oninput="this.value=this.value.replace(/\s/g,'')"
                    placeholder="Tulis Nomor Dokumen di sini...">
                <div class="invalid-feedback">
                    Nomor Dokumen wajib diisi, tidak boleh mengandung spasi, dan boleh huruf, angka, atau simbol.
                </div>
            </div>
            
            <!-- Revisi -->
            <div class="form-group">
                <label class="form-label" for="revisi">Revisi</label>
                <input type="text" id="revisi" name="revisi" class="form-input"
                    required pattern="^[0-9]+$"
                    placeholder="Misal: 00">
                <div class="invalid-feedback">
                    Revisi wajib diisi dan hanya boleh angka.
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="keterangan">Keterangan</label>
                <textarea id="keterangan" name="keterangan" class="form-input" rows="1" placeholder="Tulis Keterangan disini..." required></textarea>
                <div class="invalid-feedback">
                    Keterangan wajib diisi.
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="file-upload">Unggah Berkas</label>
                <div id="uploadArea" class="border border-2 rounded-3 p-4 text-center mb-3 bg-light position-relative" style="cursor: pointer; border-style: dashed; border-color: #b41616;" onmouseover="this.style.borderColor='#b41616';" onmouseout="this.style.borderColor='#b41616';">
                    <div class="d-flex flex-column align-items-center">
                        <i class="bi bi-cloud-arrow-up-fill" style="font-size: 2rem; color: #b41616;"></i>
                        <strong class="mt-2">Seret dan lepas file di sini</strong>
                        <span class="text-muted" id="noFileText" style="font-size: 0.85rem;">Belum ada file dipilih</span>
                        <button type="button" class="btn mt-3" id="chooseFileBtn" style="border: 1px solid #b41616; color: #b41616; background-color: transparent;" onmouseover="this.style.backgroundColor='#b41616'; this.style.color='white';" onmouseout="this.style.backgroundColor='transparent'; this.style.color='#b41616';">
                            Pilih File
                        </button>
                    </div>
                </div>

                <input type="file" id="fileInput" name="file" accept=".pdf,.doc,.docx,.xls,.xlsx" hidden required>

                <div id="fileInfo" class="alert alert-secondary border d-flex justify-content-between align-items-center d-none">
                    <div>
                        <strong id="fileName">NamaFile.pdf</strong><br>
                        <small class="text-muted" id="fileSize">Ukuran: 123 KB</small>
                    </div>
                    <button type="button" class="btn-close" id="removeBtn"></button>
                </div>

                <div class="file-requirements mt-1">
                    <div class="requirements-text text-primary" style="font-size: 13px;">
                        File Upload .pdf, .doc, .docx, .xls, .xlsx
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

<!-- Pass data dari PHP ke JavaScript -->
<script>
    // Data kode dokumen berdasarkan type dari PHP
    const kodeDokumenByType = <?= json_encode($kode_dokumen_by_type ?? []) ?>;
    
    // Bootstrap validation
    (() => {
        'use strict';
        const forms = document.querySelectorAll('.needs-validation');
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();
</script>

<!-- Script Jenis & Kode Dokumen -->
<script>
function handleJenisChange() {
    const jenisSelect = document.getElementById('jenis-dokumen');
    const selectedOption = jenisSelect.options[jenisSelect.selectedIndex];
    const usePredefined = selectedOption.getAttribute('data-use-predefined') === 'true';
    const jenisId = selectedOption.value;

    const kodeGroup = document.getElementById('kode-dokumen-group');
    const kodeCustomGroup = document.getElementById('kode-dokumen-custom-group');
    const kodeSelect = document.getElementById('kode-dokumen');
    const kodeCustomInput = document.getElementById('kode-dokumen-custom');
    const namaCustomInput = document.getElementById('nama-dokumen-custom');

    if (usePredefined) {
        // Show predefined dropdown, hide custom inputs
        kodeGroup.style.display = 'block';
        kodeCustomGroup.style.display = 'none';
        
        // Set required attributes
        kodeSelect.required = true;
        kodeCustomInput.required = false;
        namaCustomInput.required = false;
        
        // Clear custom inputs
        kodeCustomInput.value = '';
        namaCustomInput.value = '';
        
        // Load predefined codes
        loadKodeDokumen(jenisId);
    } else {
        // Hide predefined dropdown, show custom inputs
        kodeGroup.style.display = 'none';
        kodeCustomGroup.style.display = 'block';
        
        // Set required attributes
        kodeSelect.required = false;
        kodeCustomInput.required = true;
        namaCustomInput.required = true;
        
        // Clear predefined dropdown
        kodeSelect.innerHTML = '<option value="">-- Pilih Dokumen --</option>';
        kodeSelect.value = '';
    }
}

function loadKodeDokumen(jenisId) {
    const kodeSelect = document.getElementById('kode-dokumen');
    kodeSelect.innerHTML = '<option value="">-- Pilih Dokumen --</option>';

    // Menggunakan data yang sudah disiapkan dari PHP
    if (kodeDokumenByType[jenisId]) {
        kodeDokumenByType[jenisId].forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.kode + ' - ' + item.nama;
            kodeSelect.appendChild(option);
        });
    } else {
        // Fallback jika tidak ada data di kodeDokumenByType
        console.log('No kode dokumen found for jenisId:', jenisId);
    }
}

// Validasi tipe file yang diizinkan
function validateFileType(file) {
    const allowedTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ];
    
    const allowedExtensions = ['.pdf', '.doc', '.docx', '.xls', '.xlsx'];
    const fileName = file.name.toLowerCase();
    const fileType = file.type;
    
    // Cek berdasarkan MIME type
    if (allowedTypes.includes(fileType)) {
        return true;
    }
    
    // Cek berdasarkan ekstensi file sebagai fallback
    return allowedExtensions.some(ext => fileName.endsWith(ext));
}

document.addEventListener('DOMContentLoaded', function () {
    // Hide both groups initially
    document.getElementById('kode-dokumen-group').style.display = 'none';
    document.getElementById('kode-dokumen-custom-group').style.display = 'none';
});

// File upload handlers
document.getElementById('chooseFileBtn').addEventListener('click', function () {
    document.getElementById('fileInput').click();
});

document.getElementById('fileInput').addEventListener('change', function () {
    const file = this.files[0];
    if (file) {
        // Validasi tipe file
        if (!validateFileType(file)) {
            Swal.fire({
                icon: 'error',
                title: 'File Tidak Valid',
                text: 'Hanya file PDF, Word (.doc, .docx), dan Excel (.xls, .xlsx) yang diizinkan!',
                confirmButtonText: 'Okay'
            });
            this.value = ''; // Reset input file
            return;
        }
        
        // Validasi ukuran file (maksimal 10MB)
        const maxSize = 10 * 1024 * 1024; // 10MB dalam bytes
        if (file.size > maxSize) {
            Swal.fire({
                icon: 'error',
                title: 'File Terlalu Besar',
                text: 'Ukuran file maksimal 10MB!',
                confirmButtonText: 'Okay'
            });
            this.value = ''; // Reset input file
            return;
        }
        
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileSize').textContent = 'Ukuran: ' + (file.size / 1024).toFixed(1) + ' KB';
        document.getElementById('fileInfo').classList.remove('d-none');
        document.getElementById('uploadArea').classList.add('d-none');
    }
});

document.getElementById('removeBtn').addEventListener('click', function () {
    const fileInput = document.getElementById('fileInput');
    fileInput.value = '';
    document.getElementById('fileName').textContent = '';
    document.getElementById('fileSize').textContent = '';
    document.getElementById('fileInfo').classList.add('d-none');
    document.getElementById('uploadArea').classList.remove('d-none');
});

// Drag and drop functionality dengan validasi
const uploadArea = document.getElementById('uploadArea');

uploadArea.addEventListener('dragover', function(e) {
    e.preventDefault();
    this.style.backgroundColor = '#f8f9fa';
    this.style.borderColor = '#b41616';
});

uploadArea.addEventListener('dragleave', function(e) {
    e.preventDefault();
    this.style.backgroundColor = '';
    this.style.borderColor = '#b41616';
});

uploadArea.addEventListener('drop', function(e) {
    e.preventDefault();
    this.style.backgroundColor = '';
    this.style.borderColor = '#b41616';
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        const file = files[0];
        
        // Validasi tipe file
        if (!validateFileType(file)) {
            Swal.fire({
                icon: 'error',
                title: 'File Tidak Valid',
                text: 'Hanya file PDF, Word (.doc, .docx), dan Excel (.xls, .xlsx) yang diizinkan!',
                confirmButtonText: 'Okay'
            });
            return;
        }
        
        // Validasi ukuran file (maksimal 10MB)
        const maxSize = 10 * 1024 * 1024; // 10MB dalam bytes
        if (file.size > maxSize) {
            Swal.fire({
                icon: 'error',
                title: 'File Terlalu Besar',
                text: 'Ukuran file maksimal 10MB!',
                confirmButtonText: 'Okay'
            });
            return;
        }
        
        // Set file ke input
        const fileInput = document.getElementById('fileInput');
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        fileInput.files = dataTransfer.files;
        
        // Update UI
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileSize').textContent = 'Ukuran: ' + (file.size / 1024).toFixed(1) + ' KB';
        document.getElementById('fileInfo').classList.remove('d-none');
        document.getElementById('uploadArea').classList.add('d-none');
    }
});
</script>

<!-- SweetAlert2 untuk notifikasi -->
<script>
<?php if (session()->getFlashdata('success')): ?>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: '<?= session()->getFlashdata('success') ?>',
        confirmButtonText: 'Okay'
    });
<?php elseif (session()->getFlashdata('error')): ?>
    Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: '<?= session()->getFlashdata('error') ?>',
        confirmButtonText: 'Okay'
    });
<?php endif; ?>
</script>

<?= $this->endSection() ?>
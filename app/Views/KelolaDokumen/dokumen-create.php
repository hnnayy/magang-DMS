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
            <h2>Create Document</h2>
        </div>

        <form id="addDocumentForm" class="needs-validation" novalidate action="<?= base_url('create-document/store') ?>" method="post" enctype="multipart/form-data">

            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label" for="fakultas-direktorat">Faculty/Directorate</label>
                <input type="text" id="fakultas-direktorat" class="form-input" value="<?= $unit['parent_name'] ?? '' ?>" readonly>
                <input type="hidden" name="fakultas" value="<?= $unit['parent_name'] ?? '' ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="bagian">Division/Unit/Study Program</label>
                <input type="text" id="bagian" class="form-input" value="<?= $unit['name'] ?? '-' ?>" readonly>
                <input type="hidden" name="unit_id" value="<?= $unit['id'] ?? '' ?>" required>
            </div>

            <!-- Nama Dokumen -->
            <div class="form-group">
                <label class="form-label" for="nama-dokumen">Document Name</label>
                <input type="text" id="nama-dokumen" name="nama-dokumen" class="form-input" 
                    required pattern="^[a-zA-Z0-9 ]+$" placeholder="Enter Document Name...">
                <div class="invalid-feedback">
                    The document name may only contain letters, numbers, and spaces.
                </div>
            </div>

            <!-- Document Type - Full Width -->
            <div class="form-group">
                <label class="form-label" for="jenis-dokumen">Document Type</label>
                <select id="jenis-dokumen" name="jenis" class="form-input" onchange="handleJenisChange()" required>
                    <option value="" disabled selected hidden>-- Select Type --</option>
                    <?php foreach ($kategori_dokumen as $kategori): ?>
                    <option value="<?= $kategori['id'] ?>" data-kode="<?= $kategori['kode'] ?>" data-use-predefined="<?= $kategori['use_predefined_codes'] ? 'true' : 'false' ?>">
                        <?= $kategori['nama'] ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">
                    Please select a document type.
                </div>
            </div>

            <!-- Untuk predefined codes - Full Width -->
            <div class="form-group" id="kode-dokumen-group">
                <label class="form-label" for="kode-dokumen">Code-Document Name</label>
                <select id="kode-dokumen" name="kode_dokumen_id" class="form-input">
                    <option value="">-- Select Document --</option>
                </select>
                <div class="invalid-feedback">
                    Please select a document code.
                </div>
            </div>

            <!-- Untuk non-predefined codes - Full Width, Stacked Vertically -->
            <div id="kode-dokumen-custom-group" style="display: none;">
                <div class="form-group">
                    <label class="form-label" for="kode-dokumen-custom">Document Type Code</label>
                    <input type="text" id="kode-dokumen-custom" name="kode-dokumen-custom" class="form-input" 
                           placeholder="Enter document code..." 
                           oninput="this.value = this.value.toUpperCase()">
                    <div class="invalid-feedback">
                        Document code is required.
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="nama-dokumen-custom">Document Type Name</label>
                    <input type="text" id="nama-dokumen-custom" name="nama-dokumen-custom" class="form-input" 
                           placeholder="Enter document name..."
                           oninput="this.value = this.value.toUpperCase()">
                    <div class="invalid-feedback">
                        Document name is required.
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="date-published">Publication Date</label>
                <input type="date" id="date-published" name="date_published" class="form-input" required>
                <div class="invalid-feedback">
                    Publication date is required.
                </div>
            </div>

            <!-- Nomor Dokumen -->
            <div class="form-group">
                <label class="form-label" for="no-dokumen">Document Number</label>
                <input type="text" id="no-dokumen" name="no-dokumen" class="form-input"
                    required pattern="^[^\s]+$"
                    oninput="this.value=this.value.replace(/\s/g,'')"
                    placeholder="Enter Document Number here...">
                <div class="invalid-feedback">
                    Document number is required, must not contain spaces, and may include letters, numbers, or symbols.
                </div>
            </div>

            <!-- Revisi -->
            <div class="form-group">
                <label class="form-label" for="revisi">Revision</label>
                <input type="text" id="revisi" name="revisi" class="form-input"
                    required pattern="^[0-9]+$"
                    placeholder="Example: 00">
                <div class="invalid-feedback">
                    Revision is required and must contain numbers only.
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="keterangan">Description</label>
                <textarea id="keterangan" name="keterangan" class="form-input" rows="1" placeholder="Enter description here..." required></textarea>
                <div class="invalid-feedback">
                    Description is required.
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="file-upload">Upload File</label>
                <div id="uploadArea" class="border border-2 rounded-3 p-4 text-center mb-3 bg-light position-relative" style="cursor: pointer; border-style: dashed; border-color: #b41616;" onmouseover="this.style.borderColor='#b41616';" onmouseout="this.style.borderColor='#b41616';">
                    <div class="d-flex flex-column align-items-center">
                        <i class="bi bi-cloud-arrow-up-fill" style="font-size: 2rem; color: #b41616;"></i>
                        <strong class="mt-2">Drag and drop files here</strong>
                        <span class="text-muted" id="noFileText" style="font-size: 0.85rem;">No file selected yet</span>
                        <button type="button" class="btn mt-3" id="chooseFileBtn" style="border: 1px solid #b41616; color: #b41616; background-color: transparent;" onmouseover="this.style.backgroundColor='#b41616'; this.style.color='white';" onmouseout="this.style.backgroundColor='transparent'; this.style.color='#b41616';">
                            Select File
                        </button>
                    </div>
                </div>

                <input type="file" id="fileInput" name="file" accept=".pdf,.doc,.docx,.xls,.xlsx" hidden required>

                <div id="fileInfo" class="alert alert-secondary border d-flex justify-content-between align-items-center d-none">
                    <div>
                        <strong id="fileName">FileName.pdf</strong><br>
                        <small class="text-muted" id="fileSize">Size: 123 KB</small>
                    </div>
                    <button type="button" class="btn-close" id="removeBtn"></button>
                </div>

                <div class="invalid-feedback">
                    Please select a file to upload.
                </div>

                <div class="file-requirements mt-1">
                    <div class="requirements-text text-primary" style="font-size: 13px;">
                        Upload File .pdf, .doc, .docx, .xls, .xlsx
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
            kodeGroup.style.display = 'block';
            kodeCustomGroup.style.display = 'none';
            kodeSelect.required = true;
            kodeCustomInput.required = false;
            namaCustomInput.required = false;
            kodeCustomInput.value = '';
            namaCustomInput.value = '';
            loadKodeDokumen(jenisId);
        } else {
            kodeGroup.style.display = 'none';
            kodeCustomGroup.style.display = 'block';
            kodeSelect.required = false;
            kodeCustomInput.required = true;
            namaCustomInput.required = true;
            kodeSelect.innerHTML = '<option value="">-- Select Document --</option>';
            kodeSelect.value = '';
        }
    }

    function loadKodeDokumen(jenisId) {
        const kodeSelect = document.getElementById('kode-dokumen');
        kodeSelect.innerHTML = '<option value="">-- Select Document --</option>';

        if (kodeDokumenByType[jenisId]) {
            kodeDokumenByType[jenisId].forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.kode + ' - ' + item.nama;
                kodeSelect.appendChild(option);
            });
        } else {
            console.log('No kode dokumen found for jenisId:', jenisId);
        }
    }

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

        if (allowedTypes.includes(fileType)) {
            return true;
        }
        return allowedExtensions.some(ext => fileName.endsWith(ext));
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Check for success message from controller and show SweetAlert
        <?php if (session()->getFlashdata('added_message')): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '<?= session()->getFlashdata('added_message') ?>',
                confirmButtonText: 'OK',
                confirmButtonColor: '#600c8c',
                showConfirmButton: true,
                allowOutsideClick: false
            });
        <?php endif; ?>

        // Hide form groups on initial load
        document.getElementById('kode-dokumen-group').style.display = 'none';
        document.getElementById('kode-dokumen-custom-group').style.display = 'none';

        // Refresh notifikasi setelah dokumen berhasil dibuat
        <?php if (session()->getFlashdata('refresh_notif')): ?>
            fetch('<?= base_url('notification/fetch') ?>', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).then(response => response.json()).then(data => {
                if (data.status === 'success' && data.notifikasi) {
                    console.log('Refreshed notifications:', data.notifikasi);
                    // Anda bisa menambahkan logika tambahan untuk memperbarui UI di sini jika diperlukan
                }
            }).catch(error => console.error('Error refreshing notifications:', error));
        <?php endif; ?>
    });

    document.getElementById('chooseFileBtn').addEventListener('click', function () {
        document.getElementById('fileInput').click();
    });

    document.getElementById('fileInput').addEventListener('change', function () {
        const file = this.files[0];
        if (file) {
            if (!validateFileType(file)) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Tidak Valid',
                    text: 'Hanya file PDF, Word (.doc, .docx), dan Excel (.xls, .xlsx) yang diizinkan!',
                    confirmButtonText: 'Okay'
                });
                this.value = '';
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
            if (!validateFileType(file)) {
                Swal.fire({
                    icon: 'error',
                    title: 'File is Invalid',
                    text: 'Only PDF, Word (.doc, .docx), and Excel (.xls, .xlsx) files are allowed!',
                    confirmButtonText: 'Okay'
                });
                return;
            }
            const fileInput = document.getElementById('fileInput');
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            fileInput.files = dataTransfer.files;

            document.getElementById('fileName').textContent = file.name;
            document.getElementById('fileSize').textContent = 'Ukuran: ' + (file.size / 1024).toFixed(1) + ' KB';
            document.getElementById('fileInfo').classList.remove('d-none');
            document.getElementById('uploadArea').classList.add('d-none');
        }
    });
</script>

<?= $this->endSection() ?>
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
    
        <form id="addDocumentForm" class="needs-validation" novalidate action="<?= base_url('kelola-dokumen/tambah') ?>" method="post" enctype="multipart/form-data">

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
    <input type="text" id="nama-dokumen" name="nama-dokumen" class="form-control" 
           required pattern="^[a-zA-Z0-9 ]+$" placeholder="Nama Dokumen">
    <div class="invalid-feedback">
        Nama Dokumen hanya boleh berisi huruf, angka, dan spasi.
    </div>
</div>



            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="jenis-dokumen">Jenis Dokumen</label>
                    <select id="jenis-dokumen" name="jenis" class="form-input" onchange="handleJenisChange()" required>
                        <option value="">-- Pilih Jenis --</option>
                        <?php foreach ($kategori_dokumen as $kategori): ?>
                        <option value="<?= $kategori['id'] ?>" data-kode="<?= $kategori['kode'] ?>" data-use-predefined="<?= $kategori['use_predefined_codes'] ? 'true' : 'false' ?>">
                            <?= $kategori['nama'] ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" id="kode-dokumen-group">
                    <label class="form-label" for="kode-dokumen">Kode-Nama Dokumen</label>
                    <select id="kode-dokumen" name="kode_dokumen_id" class="form-input">
                        <option value="">-- Pilih Dokumen --</option>
                    </select>
                </div>

                <div class="form-group" id="kode-dokumen-custom-group" style="display: none;">
                    <label class="form-label" for="kode-dokumen-custom">Kode & Nama Dokumen</label>
                    <input type="text" id="kode-dokumen-custom" name="kode-dokumen-custom" class="form-input" placeholder="Masukkan kode dan nama dokumen...">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="date-published">Tanggal Terbit</label>
                <input type="date" id="date-published" name="date_published" class="form-input" required>
            </div>


<!-- Nomor Dokumen -->
<div class="form-group">
    <label class="form-label" for="no-dokumen">Nomor Dokumen</label>
    <input type="text" id="no-dokumen" name="no-dokumen" class="form-control"
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
    <input type="text" id="revisi" name="revisi" class="form-control"
           required pattern="^[0-9]+$"
           placeholder="Misal: 0">
    <div class="invalid-feedback">
        Revisi wajib diisi dan hanya boleh angka (misal: 0, 1, 2).
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

                <input type="file" id="fileInput" name="file" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xlsx" hidden>

                <div id="fileInfo" class="alert alert-secondary border d-flex justify-content-between align-items-center d-none">
                    <div>
                        <strong id="fileName">NamaFile.pdf</strong><br>
                        <small class="text-muted" id="fileSize">Ukuran: 123 KB</small>
                    </div>
                    <button type="button" class="btn-close" id="removeBtn"></button>
                </div>

                <div class="file-requirements mt-1">
                    <div class="requirements-text text-primary" style="font-size: 13px;">
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
    const kodeJenis = selectedOption.getAttribute('data-kode');

    const kodeGroup = document.getElementById('kode-dokumen-group');
    const kodeCustomGroup = document.getElementById('kode-dokumen-custom-group');
    const kodeSelect = document.getElementById('kode-dokumen');
    const kodeCustomInput = document.getElementById('kode-dokumen-custom');

    if (usePredefined) {
        kodeGroup.style.display = 'block';
        kodeCustomGroup.style.display = 'none';
        kodeSelect.required = true;
        kodeCustomInput.required = false;
        loadKodeDokumen(kodeJenis);
    } else {
        kodeGroup.style.display = 'none';
        kodeCustomGroup.style.display = 'block';
        kodeSelect.required = false;
        kodeCustomInput.required = true;
        kodeSelect.innerHTML = '<option value="">-- Pilih Dokumen --</option>';
    }
}

function loadKodeDokumen(jenis) {
    const kodeSelect = document.getElementById('kode-dokumen');
    kodeSelect.innerHTML = '<option value="">-- Pilih Dokumen --</option>';

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
            option.value = item.id;
            option.textContent = item.kode + ' - ' + item.nama;
            kodeSelect.appendChild(option);
        });
    })
    .catch(error => {
        console.error('Error loading kode dokumen:', error);
    });
}

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('kode-dokumen-group').style.display = 'none';
    document.getElementById('kode-dokumen-custom-group').style.display = 'none';
});

document.getElementById('chooseFileBtn').addEventListener('click', function () {
    document.getElementById('fileInput').click();
});

document.getElementById('fileInput').addEventListener('change', function () {
    const file = this.files[0];
    if (file) {
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
</script>

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

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'Tambah User' ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><?= $title ?? 'Tambah User' ?></h5>
                </div>
                <div class="card-body">

                    <form id="createUserForm" method="post" action="<?= base_url('auth/store') ?>" class="needs-validation" novalidate>
                        <?= csrf_field() ?>

                        <!-- Fakultas -->
                        <div class="mb-3">
                            <label for="fakultas" class="form-label">Fakultas/Direktorat</label>
                            <select id="fakultas" name="fakultas" class="form-select" required onchange="updateProdiOptions()">
                                <option value="" disabled selected hidden>Pilih Fakultas...</option>
                                <?php foreach ($unitParents as $parent): ?>
                                    <option value="<?= $parent['id'] ?>"><?= esc($parent['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Pilih fakultas terlebih dahulu.</div>
                        </div>

                        <!-- Unit -->
                        <div class="mb-3">
                            <label for="prodi" class="form-label">Unit/Bagian/Prodi</label>
                            <select id="prodi" name="unit" class="form-select" required>
                                <option value="" disabled selected hidden>Pilih Unit...</option>
                            </select>
                            <div class="invalid-feedback">Pilih unit terlebih dahulu.</div>
                        </div>

                        <!-- Username -->
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" id="username" name="username" class="form-control"
                                   pattern="^[a-z]+$" required placeholder="Contoh: johndoe">
                            <div class="invalid-feedback">Username hanya boleh huruf kecil (a-z).</div>
                        </div>

                        <!-- Fullname -->
                        <div class="mb-3">
                            <label for="fullname" class="form-label">Nama Lengkap</label>
                            <input type="text" id="fullname" name="fullname" class="form-control"
                                   pattern="^[A-Za-zÀ-ÿ]+(?:\s+[A-Za-zÀ-ÿ]+)+$" required placeholder="Contoh: Budi Santoso">
                            <div class="invalid-feedback">Nama lengkap harus terdiri dari minimal dua kata.</div>
                        </div>

                        <!-- Role -->
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select id="role" name="role" class="form-select" required>
                                <option value="" disabled selected hidden>Pilih Role...</option>
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?= strtolower($r['name']) ?>"><?= esc($r['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Pilih role terlebih dahulu.</div>
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label class="form-label d-block">Status</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status" id="active" value="1" required checked>
                                <label class="form-check-label" for="active">Active</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status" id="inactive" value="2" required>
                                <label class="form-check-label" for="inactive">Inactive</label>
                            </div>
                            <div class="invalid-feedback d-block">Pilih status user.</div>
                        </div>

                        <!-- Submit -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
// Load unit/prodi saat fakultas dipilih
function updateProdiOptions() {
    const fakultas = document.getElementById('fakultas').value;
    const prodi = document.getElementById('prodi');
    prodi.innerHTML = '<option disabled selected hidden>Loading...</option>';

    fetch('<?= base_url('auth/getUnits/') ?>' + fakultas)
        .then(res => res.json())
        .then(data => {
            prodi.innerHTML = '<option disabled selected hidden>Pilih Unit...</option>';
            data.forEach(unit => {
                const opt = document.createElement('option');
                opt.value = unit.id;
                opt.textContent = unit.name;
                prodi.appendChild(opt);
            });
        })
        .catch(err => {
            prodi.innerHTML = '<option disabled selected hidden>Gagal memuat data</option>';
            console.error(err);
        });
}
</script>

<script>
// SweetAlert feedback dari session flashdata
<?php if (session()->getFlashdata('success')): ?>
Swal.fire({ icon: 'success', title: 'Sukses!', text: '<?= session()->getFlashdata('success') ?>' });
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
Swal.fire({ icon: 'error', title: 'Gagal!', text: '<?= session()->getFlashdata('error') ?>' });
<?php endif; ?>
</script>

<script>
// Validasi Bootstrap
(() => {
    'use strict';
    const form = document.getElementById('createUserForm');
    form.addEventListener('submit', event => {
        form.querySelector('#username').value = form.querySelector('#username').value.toLowerCase();
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    }, false);
})();
</script>

</body>
</html>

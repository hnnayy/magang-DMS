<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="container">
    <div class="form-section">
        <div class="form-section-divider">
            <h2><?= $title ?? 'Tambah User' ?></h2>
        </div>

        <form id="createUserForm" method="post" action="<?= base_url('CreateUser/store') ?>" class="needs-validation" novalidate>
            <?= csrf_field() ?>

            <!-- Fakultas / Unit Parent -->
            <div class="form-group">
                <label class="form-label" for="fakultas">Fakultas/Direktorat</label>
                <select id="fakultas" name="fakultas" class="form-input" required onchange="updateProdiOptions()">
                    <option value="" disabled selected hidden>Pilih Fakultas...</option>
                    <?php foreach ($unitParents as $parent): ?>
                        <option value="<?= $parent['id'] ?>"><?= esc($parent['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Unit / Prodi -->
            <div class="form-group">
                <label class="form-label" for="prodi">Bagian/Unit/Program Studi</label>
                <select id="prodi" name="unit" class="form-input" required>
                    <option value="" disabled selected hidden>Pilih Bagian...</option>
                </select>
            </div>

            <!-- Username -->
            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Tulis username di sini..." pattern="^[a-z0-9]+$" title="Username hanya boleh huruf kecil (a-z)" required autocomplete="off">
                <div class="invalid-feedback">Username hanya boleh huruf kecil, angka dan tanpa spasi.</div>
            </div>

            <!-- Full Name -->
            <div class="form-group">
                <label class="form-label" for="fullname">Full Name</label>
                <input type="text" id="fullname" name="fullname" class="form-control" placeholder="Tulis nama lengkap di sini..." pattern="^[A-Za-zÀ-ÿ]+(?:\s+[A-Za-zÀ-ÿ]+)+$" title="Harus terdiri dari minimal dua kata (hanya huruf dan spasi)" required>
                <div class="invalid-feedback">Full Name harus terdiri dari minimal dua kata dan tidak boleh mengandung angka.</div>
            </div>

            <!-- Role -->
            <div class="form-group">
                <label class="form-label" for="role">Role</label>
                <select id="role" name="role" class="form-input" required>
                    <option value="" disabled selected hidden>Pilih Role...</option>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= strtolower($r['name']) ?>"><?= esc($r['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Status -->
            <div class="form-group">
                <label class="form-label d-block">Status</label>
                <label>
                    <input type="radio" name="status" value="1" checked required>
                    Active
                </label>
                <label style="margin-left: 15px;">
                    <input type="radio" name="status" value="2" required>
                    Inactive
                </label>
            </div>

            <button type="submit" class="submit-btn">Submit</button>
        </form>
    </div>

    <div class="illustration-section">
        <img src="<?= base_url('assets/images/profil/Logo_Telkom_University.png') ?>" alt="User Illustration" class="illustration-img">
    </div>
</div>

<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function updateProdiOptions() {
        const fakultasSelect = document.getElementById('fakultas');
        const parentId = fakultasSelect.value;
        const prodiSelect = document.getElementById('prodi');

        prodiSelect.innerHTML = '<option value="" disabled selected hidden>Loading...</option>';

        fetch('<?= base_url('CreateUser/getUnits/') ?>' + parentId)
            .then(response => response.json())
            .then(data => {
                prodiSelect.innerHTML = '<option value="" disabled selected hidden>Pilih Bagian...</option>';
                data.forEach(unit => {
                    const option = document.createElement('option');
                    option.value = unit.id;
                    option.textContent = unit.name;
                    prodiSelect.appendChild(option);
                });
            })
            .catch(err => {
                prodiSelect.innerHTML = '<option value="" disabled selected hidden>Gagal memuat data</option>';
                console.error('Gagal mengambil unit:', err);
            });
    }
    <?php if (session()->getFlashdata('success')): ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '<?= session()->getFlashdata('success') ?>',
            confirmButtonText: 'OK'
        });
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: '<?= session()->getFlashdata('error') ?>',
            confirmButtonText: 'OK'
        });
    <?php endif; ?>
</script>

<script>
(() => {
    'use strict';
    const form = document.getElementById('createUserForm');
    form.addEventListener('submit', e => {
        const statusInputs = form.querySelectorAll('input[name="status"]');
        let isStatusValid = false;
        statusInputs.forEach(input => {
            if (input.checked) isStatusValid = true;
            input.addEventListener('invalid', function() {
                this.classList.remove('is-valid', 'is-invalid');
            });
            input.addEventListener('change', function() {
                this.classList.remove('is-valid', 'is-invalid');
            });
        });
        if (!form.checkValidity() || !isStatusValid) {
            e.preventDefault();
            e.stopPropagation();
            if (!isStatusValid) {
                const statusGroup = document.querySelector('.form-group:has(#active)');
                let feedback = statusGroup.querySelector('.invalid-feedback');
                if (!feedback) {
                    feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback';
                    feedback.textContent = 'Please select a status.';
                    statusGroup.appendChild(feedback);
                }
                feedback.style.display = 'block';
            }
        }
        statusInputs.forEach(input => {
            input.classList.remove('is-valid', 'is-invalid');
        });
        form.classList.add('was-validated');
    }, false);
})();
</script>

<?= $this->endSection() ?>
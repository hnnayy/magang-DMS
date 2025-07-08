<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="container">
    <div class="form-section">
        <div class="form-section-divider">
            <h2><?= $title ?? 'Tambah User' ?></h2>
        </div>

        <form id="createUserForm" method="post" action="<?= base_url('CreateUser/store') ?>">
            <?= csrf_field() ?>

            <!-- Fakultas / Unit Parent -->
            <div class="form-group">
                <label class="form-label" for="fakultas">Fakultas/Direktorat</label>
                <select id="fakultas" name="fakultas" class="form-input" required onchange="updateProdiOptions()">
                    <option value="" disabled selected hidden>Pilih Fakultas...</option>
                    <?php foreach ($unitParents as $parent): ?>
                        <option value="<?= $parent['id'] ?>"><?= esc($parent['name']) ?></option>
                            <?= esc($parent['name']) ?>
                        </option>
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

            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <input type="text" id="username" name="username" class="form-input" placeholder="Tulis Username disini..." required>
            </div>

            <div class="form-group">
                <label class="form-label" for="fullname">Full Name</label>
                <input type="text" id="fullname" name="fullname" class="form-input" placeholder="Tulis Nama Lengkap disini..." required>
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
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio"
                        name="status" id="active" value="1" checked required>
                    <label class="form-check-label" for="active">Active</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio"
                        name="status" id="inactive" value="0">
                    <label class="form-check-label" for="inactive">Inactive</label>
                </div>
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
        const selectedOption = fakultasSelect.options[fakultasSelect.selectedIndex];
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
            title: 'Success!',
            text: '<?= session()->getFlashdata('success') ?>',
            confirmButtonText: 'OK'
        });
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: '<?= session()->getFlashdata('error') ?>',
            confirmButtonText: 'OK'
        });
    <?php endif; ?>
</script>

<?= $this->endSection() ?>

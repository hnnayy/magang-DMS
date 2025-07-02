<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="container">
    <div class="form-section">
        <h1 class="form-title">Create User</h1>
        <p class="form-subtitle">Tambah User</p>

        <id="createUserForm">
            <div class="form-group">
                <label class="form-label" for="fakultas">Faculty</label>
                <select id="fakultas" name="fakultas" class="form-select" required onchange="updateProdi()">
                    <option value="" disabled selected hidden>Please Select Your Faculty...</option>
                    <option value="FTE">Fakultas Teknik Elektro (FTE)</option>
                    <option value="FRI">Fakultas Rekayasa Industri (FRI)</option>
                    <option value="FIF">Fakultas Informatika (FIF)</option>
                    <option value="FEB">Fakultas Ekonomi dan Bisnis (FEB)</option>
                    <option value="FKS">Fakultas Komunikasi dan Ilmu Sosial (FKS)</option>
                    <option value="FIK">Fakultas Industri Kreatif (FIK)</option>
                    <option value="FIT">Fakultas Ilmu Terapan (FIT)</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="prodi">Program Study</label>
                <select id="prodi" name="prodi" class="form-select" required>
                    <option value="" disabled selected hidden>Please Select Program Study...</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <input type="text" id="username" name="username" class="form-input" placeholder="Tulis Username disini..." required>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-input" placeholder="Tulis Password disini..." required>
            </div>

            <div class="form-group">
                <label class="form-label" for="fulllname">Full Name</label>
                <input type="email" id="email" name="email" class="form-input" placeholder="Tulis Nama Lengkap disini..." required>
            </div>

        <id="statusForm">
            <div class="form-group">
                <label class="form-label d-block">Status</label>

                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="active" value="active" required>
                    <label class="form-check-label" for="active">Active</label>
                </div>

                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="inactive" value="inactive" required>
                    <label class="form-check-label" for="inactive">Inactive</label>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="jabatan">Jabatan</label>
                <input type="text" id="jabatan" name="jabatan" class="form-input" placeholder="Tulis jabatan disini..." required>
            </div>

            <div class="form-group">
                <label class="form-label" for="institusi">Institusi</label>
                <input type="text" id="institusi" name="institusi" class="form-input" placeholder="Tulis institusi disini..." required>
            </div>

            <button type="submit" class="submit-btn">Submit</button>
        </form>
    </div>

    <div class="illustration-section">
        <img src="<?= base_url('assets/images/profil/Logo_Telkom_University.png') ?>" alt="User Illustration" class="illustration-img">
    </div>
</div>

<script>
    // Daftar prodi berdasarkan fakultas
    const prodiOptions = {
        FTE: [
            'Electrical Energy Engineering',
            'Teknik Biomedis',
            'Teknik Telekomunikasi',
            'Teknik Elektro',
            'Smart Science and Technology (Teknik Fisika)',
            'Teknik Komputer',
            'Teknik Pangan'
        ],
        FRI: [
            'Teknik Industri',
            'Sistem Informasi',
            'Digital Supply Chain',
            'Manajemen Rekayasa Industri'
        ],
        FIF: [
            'Informatika',
            'Rekayasa Perangkat Lunak',
            'Cybersecurity',
            'Teknologi Informasi',
            'Sains Data'
        ],
        FEB: [
            'Akuntansi',
            'Manajemen',
            'Leisure Management',
            'Administrasi Bisnis',
            'Digital Business'
        ],
        FKS: [
            'Ilmu Komunikasi',
            'Digital Public Relation',
            'Digital Content Broadcasting',
            'Psikologi (Digital Psychology)'
        ],
        FIK: [
            'Visual Arts',
            'Desain Komunikasi Visual',
            'Desain Produk & Inovasi',
            'Desain Interior',
            'Kriya (Fashion & Textile Design)',
            'Film dan Animasi'
        ],
        FIT: [
            'Ilmu Komunikasi',
            'Digital Public Relation',
            'Digital Content Broadcasting',
            'Psikologi (Digital Psychology)'
        ]
    };

    // Fungsi untuk memperbarui dropdown prodi saat fakultas berubah
    function updateProdi() {
        const fakultas = document.getElementById('fakultas').value;
        const prodiSelect = document.getElementById('prodi');

        // Reset dropdown prodi
        prodiSelect.innerHTML = '<option value="" disabled selected hidden>Please Select Program Study...</option>';

        // Tampilkan opsi prodi sesuai fakultas
        if (fakultas && prodiOptions[fakultas]) {
            prodiOptions[fakultas].forEach(function (prodi) {
                const option = document.createElement('option');
                option.value = prodi;
                option.textContent = prodi;
                prodiSelect.appendChild(option);
            });
        }
    }
</script>


<?= $this->endSection() ?>

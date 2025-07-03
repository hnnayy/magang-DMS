<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="container">
    <div class="form-section">
        <div class="form-section-divider">
                <h2>Tambah User</h2>
            </div

<<<<<<< HEAD
        <form id="createUserForm" method="post" action="<?= base_url('CreateUser/store') ?>">
            <?= csrf_field() ?>

=======
        <form>
>>>>>>> 8a3a31be84a52a9674b4c649a5ebbadb4d36f9d6
            <div class="form-group">
                <label class="form-label" for="fakultas">Fakultas/Direktorat</label>
                <select id="fakultas" name="fakultas" class="form-input" required onchange="updateProdi()">
                    <option value="" disabled selected hidden>Pilih Fakultas...</option>
                    <option value="Fakultas Teknik Elektro (FTE)">Fakultas Teknik Elektro (FTE)</option>
                    <option value="Fakultas Rekayasa Industri (FRI)">Fakultas Rekayasa Industri (FRI)</option>
                    <option value="Fakultas Informatika (FIF)">Fakultas Informatika (FIF)</option>
                    <option value="Fakultas Ekonomi dan Bisnis (FEB)">Fakultas Ekonomi dan Bisnis (FEB)</option>
                    <option value="Fakultas Komunikasi dan Ilmu Sosial (FKS)">Fakultas Komunikasi dan Ilmu Sosial (FKS)</option>
                    <option value="Fakultas Industri Kreatif (FIK)">Fakultas Industri Kreatif (FIK)</option>
                    <option value="Fakultas Ilmu Terapan (FIT)">Fakultas Ilmu Terapan (FIT)</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="prodi">Bagian/Unit/Program Studi</label>
<<<<<<< HEAD
                <select id="prodi" name="prodi" class="form-select" required>
                    <option value="" disabled selected hidden>Pilih Bagian...</option>
=======
                <select id="prodi" name="prodi" class="form-input" required>
                    <option value="" disabled selected hidden>Pilih Bagian ...</option>
>>>>>>> 8a3a31be84a52a9674b4c649a5ebbadb4d36f9d6
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

<<<<<<< HEAD
            <div id="roleForm" class="form-group">
                <label class="form-label" for="role">Role</label>
                <select id="role" name="role" class="form-select" required>
=======
            <div class="form-group">
                <label class="form-label" for="role">Role</label>
                <select id="role" name="role" class="form-input" required>
>>>>>>> 8a3a31be84a52a9674b4c649a5ebbadb4d36f9d6
                    <option value="" disabled selected hidden>Pilih Role...</option>
                    <option value="admin">Admin</option>
                    <option value="kepalabagian">Kepala Bagian</option>
                    <option value="kepalaunit">Kepala Unit</option>
                    <option value="staff">Staff</option>
                </select>
            </div>

<<<<<<< HEAD
            <div id="statusForm" class="form-group">
=======
            <div class="form-group">
>>>>>>> 8a3a31be84a52a9674b4c649a5ebbadb4d36f9d6
                <label class="form-label d-block">Status</label>

                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="active" value="active" required>
                    <label class="form-check-label" for="active">Active</label>
                </div>

                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="inactive" value="inactive">
                    <label class="form-check-label" for="inactive">Inactive</label>
                </div>
            </div>

            <button type="submit" class="submit-btn">Submit</button>
        </form>

        <?php if (session()->getFlashdata('error')) : ?>
            <div class="error"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>
    </div>

    <div class="illustration-section">
        <img src="<?= base_url('assets/images/profil/Logo_Telkom_University.png') ?>" alt="User Illustration" class="illustration-img">
    </div>
</div>

<<<<<<< HEAD
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Daftar prodi berdasarkan fakultas
    const prodiOptions = {
        'Fakultas Teknik Elektro (FTE)': [
            'Electrical Energy Engineering',
            'Teknik Biomedis',
            'Teknik Telekomunikasi',
            'Teknik Elektro',
            'Smart Science and Technology (Teknik Fisika)',
            'Teknik Komputer',
            'Teknik Pangan'
        ],
        'Fakultas Rekayasa Industri (FRI)': [
            'Teknik Industri',
            'Sistem Informasi',
            'Digital Supply Chain',
            'Manajemen Rekayasa Industri'
        ],
        'Fakultas Informatika (FIF)': [
            'Informatika',
            'Rekayasa Perangkat Lunak',
            'Cybersecurity',
            'Teknologi Informasi',
            'Sains Data'
        ],
        'Fakultas Ekonomi dan Bisnis (FEB)': [
            'Akuntansi',
            'Manajemen',
            'Leisure Management',
            'Administrasi Bisnis',
            'Digital Business'
        ],
        'Fakultas Komunikasi dan Ilmu Sosial (FKS)': [
            'Ilmu Komunikasi',
            'Digital Public Relation',
            'Digital Content Broadcasting',
            'Psikologi (Digital Psychology)'
        ],
        'Fakultas Industri Kreatif (FIK)': [
            'Visual Arts',
            'Desain Komunikasi Visual',
            'Desain Produk & Inovasi',
            'Desain Interior',
            'Kriya (Fashion & Textile Design)',
            'Film dan Animasi'
        ],
        'Fakultas Ilmu Terapan (FIT)': [
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
        prodiSelect.innerHTML = '<option value="" disabled selected hidden>Pilih Bagian...</option>';

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

    // Tampilkan pop-up jika data berhasil disimpan
    <?php if (session()->getFlashdata('success') && session()->getFlashdata('showPopup')) : ?>
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: '<?= session()->getFlashdata('success') ?>',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '<?= base_url('CreateUser/create') ?>';
            }
        });
    <?php endif; ?>
</script>

=======

>>>>>>> 8a3a31be84a52a9674b4c649a5ebbadb4d36f9d6
<?= $this->endSection() ?>
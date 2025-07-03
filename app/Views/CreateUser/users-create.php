<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="container">
    <div class="form-section">
        <div class="form-section-divider">
                <h2>Tambah User</h2>
            </div

        <form>
            <div class="form-group">
                <label class="form-label" for="fakultas">Fakultas/Direktorat</label>
                <select id="fakultas" name="fakultas" class="form-input" required onchange="updateProdi()">
                    <option value="" disabled selected hidden>Pilih Fakultas...</option>
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
                <label class="form-label" for="prodi">Bagian/Unit/Program Studi</label>
                <select id="prodi" name="prodi" class="form-input" required>
                    <option value="" disabled selected hidden>Pilih Bagian ...</option>
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

            <div class="form-group">
                <label class="form-label" for="role">Role</label>
                <select id="role" name="role" class="form-input" required>
                    <option value="" disabled selected hidden>Pilih Role...</option>
                    <option value="admin">Admin</option>
                    <option value="kepalabagian">Kepala Bagian</option>
                    <option value="kepalaunit">Kepala Unit</option>
                    <option value="staff">Staff</option>
                </select>
            </div>

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

            <button type="submit" class="submit-btn">Submit</button>
        </form>
    </div>

    <div class="illustration-section">
        <img src="<?= base_url('assets/images/profil/Logo_Telkom_University.png') ?>" alt="User Illustration" class="illustration-img">
    </div>
</div>


<?= $this->endSection() ?>
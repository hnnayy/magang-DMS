<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="container">
    <div class="form-section">
        <div class="form-section-divider">
            <h2><?= $title ?? 'Tambah Fakultas' ?></h2>
        </div>

        <form id="createFakultasForm" method="post" action="<?= base_url('fakultas/store') ?>">
            <?= csrf_field() ?>

            <!-- Nama Fakultas -->
            <div class="form-group">
                <label class="form-label" for="name">Nama Fakultas/Directorate</label>
                <input type="text" id="name" name="name" class="form-input" placeholder="Tulis Nama Fakultas disini..." required>
            </div>

            <!-- Type (Level) -->
            <div class="form-group">
                <label class="form-label d-block">Level</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="type" id="type1" value="1">
                    <label class="form-check-label" for="type1">Directorate</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="type" id="type2" value="2" checked>
                    <label class="form-check-label" for="type2">Faculty</label>
                </div>
            </div>

            <!-- Status -->
            <div class="form-group">
                <label class="form-label d-block">Status</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="status1" value="1">
                    <label class="form-check-label" for="status1">Active</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="status2" value="2" checked>
                    <label class="form-check-label" for="status2">Inactive</label>
                </div>
            </div>

            <button type="submit" class="submit-btn">Submit</button>
        </form>
    </div>

    <div class="illustration-section">
        <img src="<?= base_url('assets/images/profil/Logo_Telkom_University.png') ?>" alt="Fakultas Illustration" class="illustration-img">
    </div>
</div>

<!-- SweetAlert (Notifikasi) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
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

<script>
    // Fungsi untuk kapitalisasi setiap kata
    function capitalizeWords(str) {
        return str.replace(/\b\w/g, function(char) {
            return char.toUpperCase();
        });
    }

    // Event saat user mengetik di field name
    document.getElementById('name').addEventListener('input', function () {
        const lower = this.value.toLowerCase();
        this.value = capitalizeWords(lower);
    });

    // Pastikan input terformat benar saat form disubmit
    document.getElementById('createFakultasForm').addEventListener('submit', function () {
        const input = document.getElementById('name');
        input.value = capitalizeWords(input.value.toLowerCase());
    });
</script>

<?= $this->endSection() ?>

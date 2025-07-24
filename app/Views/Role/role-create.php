<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="container">
    <div class="form-section">
        <div class="form-section-divider">
            <h2><?= $title ?? 'Add New Role' ?></h2>
        </div>

        <form id="createRoleForm" method="post" action="<?= base_url('role/store') ?>">
            <?= csrf_field() ?>

            <!-- Nama Role -->
            <div class="form-group">
                <label class="form-label" for="nama">Role Name</label>
                <input type="text" id="nama" name="nama" class="form-input"
                       placeholder="Enter Role Name here..."
                       value="<?= old('nama') ?>"
                       required>
            </div>

            <!-- Level -->
            <div class="form-group">
                <label class="form-label d-block">Level</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="level" id="level1" value="1"
                           <?= set_radio('level', '1', true) ?>>
                    <label class="form-check-label" for="level1">Directorate / Faculty</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="level" id="level2" value="2"
                           <?= set_radio('level', '2') ?>>
                    <label class="form-check-label" for="level2">Unit</label>
                </div>
            </div>

            <!-- Deskripsi -->
            <div class="form-group">
                <label class="form-label" for="desc">Description</label>
                <textarea id="desc" name="desc" class="form-input"
                          placeholder="Describe this role..."
                          rows="3"
                          required><?= old('desc') ?></textarea>
            </div>

            <!-- Status -->
            <div class="form-group">
                <label class="form-label d-block">Status</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="active" value="active"
                           <?= set_radio('status', 'active', true) ?>>
                    <label class="form-check-label" for="active">Active</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="inactive" value="inactive"
                           <?= set_radio('status', 'inactive') ?>>
                    <label class="form-check-label" for="inactive">Inactive</label>
                </div>
            </div>

            <button type="submit" class="submit-btn">Submit</button>
        </form>
    </div>

    <div class="illustration-section">
        <img src="<?= base_url('assets/images/profil/Logo_Telkom_University.png') ?>" alt="Role Illustration" class="illustration-img">
    </div>
</div>

<!-- SweetAlert (Notifikasi) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script>
    // Fungsi untuk kapitalisasi setiap kata
    function capitalizeWords(str) {
        return str.replace(/\b\w/g, function(char) {
            return char.toUpperCase();
        });
    }

    // Event saat user mengetik di field nama
    document.getElementById('nama').addEventListener('input', function () {
        const lower = this.value.toLowerCase();
        this.value = capitalizeWords(lower);
    });

    // Pastikan input terformat benar saat form disubmit
    document.getElementById('createRoleForm').addEventListener('submit', function () {
        const input = document.getElementById('nama');
        input.value = capitalizeWords(input.value.toLowerCase());
    });
</script>

<?= $this->endSection() ?>

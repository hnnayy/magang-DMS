<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="container">
    <div class="form-section">
        <div class="form-section-divider">
            <h2><?= $title ?? 'Add Submenu' ?></h2>
        </div>

        <?php if (session()->getFlashdata('success')) : ?>
            <div class="alert alert-success">
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <form id="createSubmenuForm" method="post" action="<?= base_url('submenu/store') ?>">
            <?= csrf_field() ?>

            <!-- Dropdown Menu -->
            <div class="form-group">
                <label class="form-label" for="menu">Menu</label>
                <select name="parent" class="form-select <?php echo isset($validation) && isset($validation['parent']) ? 'is-invalid' : ''; ?>" required>
                    <option value="">-- Choose Menu --</option>
                    <?php foreach ($menus as $menu): ?>
                        <option value="<?= $menu['id'] ?>" <?= old('parent') == $menu['id'] ? 'selected' : '' ?>>
                            <?= esc($menu['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($validation) && isset($validation['parent'])) : ?>
                    <div class="invalid-feedback">
                        <?php echo $validation['parent']; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Submenu -->
            <div class="form-group <?php echo isset($validation) && isset($validation['submenu']) ? 'has-error' : ''; ?>">
                <label for="editUnitName" class="form-label">Sub Menu</label>
                <input type="text" name="submenu" id="editUnitName" class="form-control <?php echo isset($validation) && isset($validation['submenu']) ? 'is-invalid' : ''; ?>"
                       placeholder="Enter Submenu here... "
                       value="<?php echo old('submenu'); ?>"
                       required>
                <?php if (isset($validation) && isset($validation['submenu'])) : ?>
                    <div class="invalid-feedback">
                        <?php echo $validation['submenu']; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Status -->
            <div class="form-group">
                <label class="form-label d-block">Status</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="active" value="1" checked required>
                    <label class="form-check-label" for="active">Active</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="inactive" value="2">
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

<!-- Hapus atau sesuaikan script validasi klien-side -->
<script>
document.getElementById('createSubmenuForm').addEventListener('submit', function (e) {
    const submenu = document.getElementById('editUnitName');
    const valid = submenu.value.trim().match(/^\S+\s+\S+/);
    if (!valid) {
        e.preventDefault();
        submenu.classList.add('is-invalid');
        let feedback = document.querySelector('#editUnitName + .invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            submenu.parentNode.appendChild(feedback);
        }
        feedback.textContent = 'Submenu harus terdiri dari minimal dua kata.';
    } else {
        submenu.classList.remove('is-invalid');
        let feedback = document.querySelector('#editUnitName + .invalid-feedback');
        if (feedback) feedback.remove();
    }
});
</script>

<!-- Tambah SweetAlert jika ada flashdata -->
<?php if ($swal = session()->getFlashdata('swal')) : ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    Swal.fire({
        icon: '<?= esc($swal['icon']) ?>',
        title: '<?= esc($swal['title']) ?>',
        text: '<?= esc($swal['text']) ?>',
        confirmButtonText: 'OK',
        confirmButtonColor: '#6868ff',
        allowOutsideClick: false,
        allowEscapeKey: false
    });
</script>
<?php endif; ?>



<?= $this->endSection() ?>
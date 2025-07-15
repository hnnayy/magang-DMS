<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<h4>Edit Submenu</h4>
<hr>

<form action="<?= base_url('submenu/update/' . $submenu['id']) ?>" method="post">
    <?= csrf_field() ?>

    <!-- Pilih Menu -->
    <div class="mb-3">
        <label for="menu" class="form-label">Menu</label>
        <select name="parent" id="menu" class="form-select" required>
            <option value="">-- Pilih Menu --</option>
            <?php foreach ($menus as $menu): ?>
                <option value="<?= $menu['id'] ?>" <?= $menu['id'] == $submenu['parent'] ? 'selected' : '' ?>>
                    <?= esc($menu['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Nama Submenu -->
    <div class="mb-3">
        <label for="submenu" class="form-label">Sub Menu</label>
        <input type="text" name="submenu" id="submenu" class="form-control"
            value="<?= esc($submenu['name']) ?>" required>
    </div>

    <!-- Status Radio -->
    <div class="mb-3">
        <label class="form-label d-block">Status</label>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="status" id="statusAktif" value="1" <?= $submenu['status'] == 1 ? 'checked' : '' ?>>
            <label class="form-check-label" for="statusAktif">Aktif</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="status" id="statusNonAktif" value="2" <?= $submenu['status'] == 2 ? 'checked' : '' ?>>
            <label class="form-check-label" for="statusNonAktif">Tidak Aktif</label>
        </div>
    </div>

    <!-- Tombol -->
    <button type="submit" class="btn btn-primary">Update</button>
    <a href="<?= base_url('submenu/lihat-submenu') ?>" class="btn btn-secondary">Kembali</a>
</form>

<?= $this->endSection() ?>

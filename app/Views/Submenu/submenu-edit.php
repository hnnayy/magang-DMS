<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<h4>Edit Submenu</h4>
<hr>

<form action="<?= base_url('submenu/update/' . $submenu['id']) ?>" method="post">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label for="menu" class="form-label">Menu</label>
        <select name="menu" id="menu" class="form-select" required>
            <option value="">-- Pilih Menu --</option>
            <?php foreach ($menus as $menu): ?>
                <option value="<?= $menu['id'] ?>" <?= $menu['id'] == $submenu['parent'] ? 'selected' : '' ?>>
                    <?= esc($menu['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-3">
        <label for="submenu" class="form-label">Sub Menu</label>
        <input type="text" name="submenu" id="submenu" class="form-control"
            value="<?= esc($submenu['name']) ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Status</label><br>
        <input type="radio" name="status" value="1" <?= $submenu['status'] == 1 ? 'checked' : '' ?>> Aktif
        <input type="radio" name="status" value="2" <?= $submenu['status'] == 2 ? 'checked' : '' ?>> Tidak Aktif
    </div>

    <button type="submit" class="btn btn-primary">Update</button>
    <a href="<?= base_url('submenu/lihat-submenu') ?>" class="btn btn-secondary">Kembali</a>
</form>

<?= $this->endSection() ?>

<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<h4>Edit Unit</h4>
<hr>

<form action="<?= site_url('data-master/unit/' . $unit['id'] . '/update') ?>" method="post">
    <?= csrf_field() ?>

    <!-- Dropdown Fakultas/Direktorat -->
    <div class="mb-3">
        <label class="form-label">Fakultas/Direktorat</label>
        <select name="parent_id" class="form-control" required>
            <option value="">-- Pilih Fakultas/Direktorat --</option>
            <?php foreach ($fakultas as $f): ?>
                <option value="<?= $f['id'] ?>" <?= $unit['parent_id'] == $f['id'] ? 'selected' : '' ?>>
                    <?= esc($f['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Input Unit -->
    <div class="mb-3">
        <label class="form-label">Unit</label>
        <input type="text" name="unit_name" class="form-control"
               value="<?= esc(old('unit_name', $unit['name'])) ?>" required>
    </div>

    <button class="btn btn-primary w-100">Simpan Perubahan</button>
</form>

<?= $this->endSection() ?>

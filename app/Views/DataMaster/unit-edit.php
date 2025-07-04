<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<h4>Edit Unit</h4>
<hr>

<form action="<?= site_url('data-master/unit/' . $unit['id'] . '/update') ?>" method="post">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label class="form-label">Fakultas/Direktorat</label>
        <input type="text" name="parent_name" class="form-control"
               value="<?= esc(old('parent_name', $parent['name'])) ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Unit</label>
        <input type="text" name="unit_name" class="form-control"
               value="<?= esc(old('unit_name', $unit['name'])) ?>" required>
    </div>

    <button class="btn btn-primary w-100">Simpan</button>
</form>

<?= $this->endSection() ?>

<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="container">
    <div class="form-section">
        <h1 class="form-title">Data Master</h1>
        <p class="form-subtitle">Tambah Unit</p>

        <!-- Flash message (opsional) -->
        <?php if (session('success')) : ?>
            <div class="alert alert-success"><?= session('success') ?></div>
        <?php endif; ?>
        <?php if (session('errors')) : ?>
            <div class="alert alert-danger">
                <?= implode('<br>', session('errors')) ?>
            </div>
        <?php endif; ?>

        <!-- FORM -->
        <form id="addDocumentForm"
              action="<?= site_url('data-master/store') ?>"
              method="post">
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label" for="fakultas-direktorat">Fakultas/Direktorat</label>
                <input type="text"
                       id="fakultas-direktorat"
                       name="parent_name"
                       class="form-input"
                       placeholder="Tulis Fakultas disini..."
                       value="<?= set_value('parent_name') ?>"
                       required>
            </div>

            <div class="form-group">
                <label class="form-label" for="unit">Unit</label>
                <input type="text"
                       id="unit"
                       name="unit_name"
                       class="form-input"
                       placeholder="Tulis Unit disini..."
                       value="<?= set_value('unit_name') ?>"
                       required>
            </div>

            <button type="submit" class="submit-btn">Submit</button>
        </form>
    </div>

    <div class="illustration-section">
        <img src="<?= base_url('assets/images/profil/Logo_Telkom_University.png') ?>"
             alt="User Illustration"
             class="illustration-img">
    </div>
</div>

<?= $this->endSection() ?>

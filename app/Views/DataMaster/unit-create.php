<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="container">
    <div class="form-section">
        <h1 class="form-title">Tambah Unit</h1>
        <hr>
        
        <!-- Flash message untuk sukses atau error -->
        <?php if (session('swal')) : ?>
            <script>
                Swal.fire({
                    icon: '<?= session('swal')['icon'] ?>',
                    title: '<?= session('swal')['title'] ?>',
                    text: '<?= session('swal')['text'] ?>'
                });
            </script>
        <?php endif; ?>

        <!-- FORM -->
        <form id="addDocumentForm" action="<?= site_url('data-master/unit/store') ?>" method="post">
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label" for="fakultas-direktorat">Fakultas/Direktorat</label>
                <select id="fakultas-direktorat" name="parent_id" class="form-input" required>
                    <option value="">-- Pilih Fakultas/Direktorat --</option>
                    <?php foreach ($fakultas as $f) : ?>
                        <option value="<?= $f['id'] ?>" <?= set_select('parent_id', $f['id']) ?>>
                            <?= esc($f['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
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

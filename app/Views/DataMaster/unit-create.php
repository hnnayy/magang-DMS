<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="container">
    <div class="form-section">
        <h1 class="form-title">Create Unit</h1>
        <hr>

        <?php if (session('swal')) : ?>
            <script>
                Swal.fire({
                    icon: '<?= session('swal')['icon'] ?>',
                    title: '<?= session('swal')['title'] ?>',
                    text: '<?= session('swal')['text'] ?>'
                });
            </script>
        <?php endif; ?>
        
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <form id="addDocumentForm" action="<?= site_url('create-unit/store') ?>" method="post">
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label" for="fakultas-direktorat">Faculty/Directorate</label>
                <select id="fakultas-direktorat" name="parent_id" class="form-input" required>
                    <option value="">-- Choose Faculty/Directorate --</option>
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
                       placeholder="Enter Unit here..."
                       value="<?= set_value('unit_name') ?>"
                       required>
            </div>

            <div class="form-group">
                <label class="form-label d-block">Status</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="statusActive" value="1" checked>
                    <label class="form-check-label" for="statusActive">Active</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="statusInactive" value="2">
                    <label class="form-check-label" for="statusInactive">Inactive</label>
                </div>
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

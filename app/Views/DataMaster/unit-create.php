<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="form-section">
        <h1 class="form-title">Data Master</h1>
        <p class="form-subtitle">Tambah Unit</p>
        
        <form id="addDocumentForm">
            <div class="form-group">
                <label class="form-label" for="fakultas-direktorat">Fakultas/Direktorat</label>
                <input type="text" id="fakultas-direktorat" name="fakultas-direktorat" class="form-input" placeholder="Tulis Fakultas disini..." required>
            </div>

            <div class="form-group">
                <label class="form-label" for="unit">Unit</label>
                <input type="text" id="unit" name="unit" class="form-input" placeholder="Tulis Unit disini..." required>
            </div>

            <button type="submit" class="submit-btn">Submit</button>
        </form>
    </div>

    <div class="illustration-section">
        <img src="<?= base_url('assets/images/profil/Logo_Telkom_University.png') ?>" alt="User Illustration" class="illustration-img">
    </div>
</div>

<?= $this->endSection() ?>
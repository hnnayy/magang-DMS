<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container">
    <div class="form-section">
        <div class="form-section-divider">
            <h2><?= $title ?? 'Tambah Sub Menu' ?></h2>
        </div>

        <form id="createSubmenuForm">
            <!-- Dropdown Menu -->
            <div class="form-group">
                <label class="form-label" for="menu">Menu</label>
                <select id="menu" name="menu" class="form-select" required>
                    <option value="">-- Pilih Menu --</option>
                    <option value="dashboard">Dashboard</option>
                    <option value="user">Create User</option>
                    <option value="dokumen">Master Data</option>
                    <option value="laporan">Document Management</option>
                    <option value="dokumen">Role</option>
                    <option value="dokumen">Privilege</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="submenu">Sub Menu</label>
                <input type="text" id="submenu" name="submenu" class="form-input" placeholder="Tulis Sub Menu disini..." required>
            </div>

            <!-- Status -->
            <div class="form-group">
                <label class="form-label d-block">Status</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio"
                        name="status" id="active" value="1" checked required>
                    <label class="form-check-label" for="active">Active</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio"
                        name="status" id="inactive" value="0">
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

<?= $this->endSection() ?>

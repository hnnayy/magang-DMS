<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="container">
    <div class="form-section">
        <h1 class="form-title">Create User</h1>
        <p class="form-subtitle">Tambah User</p>

        <form id="createUserForm">
            <div class="form-group">
                <label class="form-label" for="type">Type</label>
                <select id="type" name="type" class="form-select" required>
                    <option value="" disabled selected hidden>Please Select User Type</option>
                    <option value="type1">Type1</option>
                    <option value="type2">Type2</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <input type="text" id="username" name="username" class="form-input" placeholder="Tulis Username disini..." required>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-input" placeholder="Tulis Password disini..." required>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" class="form-input" placeholder="Tulis Email disini..." required>
            </div>

            <div class="form-group">
                <label class="form-label" for="jabatan">Jabatan</label>
                <input type="text" id="jabatan" name="jabatan" class="form-input" placeholder="Tulis jabatan disini..." required>
            </div>

            <div class="form-group">
                <label class="form-label" for="institusi">Institusi</label>
                <input type="text" id="institusi" name="institusi" class="form-input" placeholder="Tulis institusi disini..." required>
            </div>

            <button type="submit" class="submit-btn">Submit</button>
        </form>
    </div>

    <div class="illustration-section">
        <img src="<?= base_url('assets/images/profil/profil.jpg') ?>" alt="User Illustration" class="illustration-img">
    </div>
</div>

<?= $this->endSection() ?>

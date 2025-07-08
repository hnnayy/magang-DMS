<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('assets/css/privilege.css') ?>">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />

<div class="privilege-container">
    <h2 class="form-title">Tambah Privilege</h2>

    <div class="form-content">
        <form method="post" action="#" id="privilegeForm">
            <!-- Role -->
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="" disabled selected hidden>Pilih Role...</option>
                    <option value="admin">Admin</option>
                    <option value="staff">Staff</option>
                    <option value="kepalabagian">Kepala Bagian</option>
                    <option value="kepalaunit">Kepala Unit</option>
                </select>
            </div>

            <!-- Submenu -->
            <div class="form-group">
                <label for="submenu">Submenu</label>
                <select id="submenu" name="submenu[]" multiple="multiple" required>
                    <option value="User Management > User List">User Management > User List</option>
                    <option value="Role Management > Role Settings">Role Management > Role Settings</option>
                    <option value="Dashboard">Dashboard</option>
                    <option value="Dokumen">Dokumen</option>
                    <option value="Persetujuan">Persetujuan</option>
                    <option value="Reporting > Monthly Report">Reporting > Monthly Report</option>
                </select>
            </div>

            <!-- Privileges -->
            <div class="form-group">
                <label for="privileges">Privileges</label>
                <div class="privileges-options">
                    <label><input type="checkbox" name="privileges[]" value="create"> Create</label>
                    <label><input type="checkbox" name="privileges[]" value="update"> Update</label>
                    <label><input type="checkbox" name="privileges[]" value="delete"> Delete</label>
                    <label><input type="checkbox" name="privileges[]" value="read"> Read</label>
                </div>
            </div>

            <!-- Tombol -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Script Select2 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function () {
        $('#submenu').select2({
            placeholder: "Pilih Submenu...",
            width: '100%',
            allowClear: true,
            tags: false,
            createTag: function () { return null; },
            insertTag: function () { return null; }
        });

        $('.btn-primary').on('click', function (e) {
            e.preventDefault();
            alert('Save button clicked');
        });
    });
</script>

<?= $this->endSection() ?>

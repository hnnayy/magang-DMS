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
                    <option value="" hidden>Pilih Role...</option>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= $r['id']; ?>">
                            <?= esc($r['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Submenu -->
            <div class="form-group">
                <label for="submenu">Submenu</label>
                <select id="submenu" name="submenu[]" multiple="multiple" required>
                    <?php foreach ($submenus as $s): ?>
                        <option value="<?= $s['id']; ?>">
                            <?= esc($s['menu_name'] . ' > ' . $s['name']); ?>
                        </option>
                    <?php endforeach; ?>
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
$(function () {

    /* ───────── Inisialisasi Select2 ───────── */
    $('#submenu').select2({
        placeholder : 'Pilih Submenu...',
        width       : '100%',
        allowClear  : true,
        tags        : false,              // non‑editable
        createTag   : () => null,
        insertTag   : () => null
    });

    /* ───────── Submit Form Privilege ───────── */
    $('#privilegeForm').on('submit', function (e) {
        e.preventDefault();

        $.post('/create-user/privilege/store', $(this).serialize())
         .done(res  => Swal.fire({icon:'success', title:'Berhasil', text:res.message}))
         .fail(xhr => {
              const msg = xhr.responseJSON?.error ?? 'Gagal menyimpan privilege';
              Swal.fire({icon:'error', title:'Error', text: msg});
         });
    });

});
</script>

<?= $this->endSection() ?>

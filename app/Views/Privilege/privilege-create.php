<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
<link rel="stylesheet" href="<?= base_url('assets/css/privilege.css') ?>" />

<div class="privilege-container">
    <h2 class="form-title">Tambah Privilege</h2>

    <div class="form-content">
        <form method="post" action="<?= base_url('privilege/store') ?>" id="privilegeForm" class="needs-validation" novalidate>
            <!-- Role -->
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" class="form-control" required>
                    <option value="" hidden>Pilih Role...</option>
                    <?php
                    $uniqueRoles = [];
                    $seenRoleNames = [];

                    foreach ($roles as $r) {
                        $roleNameLower = strtolower($r['name']);
                        if (!in_array($roleNameLower, $seenRoleNames)) {
                            $seenRoleNames[] = $roleNameLower;
                            $uniqueRoles[] = $r;
                        }
                    }

                    foreach ($uniqueRoles as $r): ?>
                        <option value="<?= $r['id']; ?>"><?= esc($r['name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">Please select a role.</div>
            </div>

            <!-- Submenu -->
            <div class="form-group">
                <label for="submenu">Submenu</label>
                <select id="submenu" name="submenu[]" multiple="multiple" class="form-control" required>
                    <?php foreach ($submenus as $s): ?>
                        <option value="<?= $s['id']; ?>"><?= esc($s['menu_name'] . ' > ' . $s['name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">Please select at least one submenu.</div>
            </div>

            <!-- Privileges -->
            <div class="form-group">
                <label for="privileges">Privileges</label>
                <div class="privileges-options">
                    <label><input type="checkbox" name="privileges[]" value="create"> Create</label>
                    <label><input type="checkbox" name="privileges[]" value="update"> Update</label>
                    <label><input type="checkbox" name="privileges[]" value="delete"> Delete</label>
                    <label><input type="checkbox" name="privileges[]" value="approve"> Approve</label>
                </div>
                <div class="invalid-feedback" id="privileges-error" style="display: none;">Please select at least one privilege.</div>
            </div>

            <!-- Tombol -->
            <div class="form-actions text-center">
                <button type="submit" class="btn btn-primary w-100">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- jQuery & Select2 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(function () {
    $('#submenu').select2({
        placeholder: 'Pilih Submenu...',
        width: '100%',
        allowClear: true,
        tags: false,
        createTag: () => null,
        insertTag: () => null
    });

    $('#privilegeForm').on('submit', function (e) {
        e.preventDefault(); 
        
        const form = this;
        const privileges = $(form).find('input[name="privileges[]"]:checked').length;
        const privilegesError = $('#privileges-error');
        let isValid = true;

        privilegesError.hide();
        $(form).removeClass('was-validated');

        if (privileges === 0) {
            privilegesError.show();
            isValid = false;
        }

        form.classList.add('was-validated');

        if (!form.checkValidity()) {
            isValid = false;
        }

        if (isValid) {
            $.post('<?= base_url('privilege/store') ?>', $(this).serialize())
                .done(res => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = '<?= base_url('privilege/create') ?>';
                    });
                })
                .fail(xhr => {
                    const msg = xhr.responseJSON?.error ?? 'Gagal menyimpan privilege';
                    Swal.fire({ 
                        icon: 'error', 
                        title: 'Error', 
                        text: msg 
                    });
                });
        }
    });

    $('input[name="privileges[]"]').on('change', function() {
        const privileges = $('input[name="privileges[]"]:checked').length;
        if (privileges > 0) {
            $('#privileges-error').hide();
        }
    });
});
</script>

<?= $this->endSection() ?>
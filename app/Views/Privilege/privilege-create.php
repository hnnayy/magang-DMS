<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<!-- Select2 & CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
<link rel="stylesheet" href="<?= base_url('assets/css/privilege.css') ?>" />

<div class="privilege-container">
    <h2 class="form-title">Create Privilege</h2>

    <div class="form-content">
        <form method="post" id="privilegeForm" class="needs-validation" novalidate>
            <!-- CSRF Token -->
            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" id="csrf-token">

            <!-- Role -->
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" class="select" required>
                    <option value="" hidden>-- Select Role --</option>
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
                <select id="submenu" name="submenu[]" multiple="multiple" class="select" required>
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

<!-- Scripts -->
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
        const privilegesCount = $('input[name="privileges[]"]:checked').length;
        const privilegesError = $('#privileges-error');
        let isValid = true;

        privilegesError.hide();
        $(form).removeClass('was-validated');

        if (privilegesCount === 0) {
            privilegesError.show();
            isValid = false;
        }

        if (!form.checkValidity()) {
            form.classList.add("was-validated");
            isValid = false;
        }

        if (isValid) {
            let formData = $(form).serializeArray();
            formData.push({
                name: '<?= csrf_token() ?>',
                value: $('#csrf-token').val()
            });

            $.post('<?= base_url('create-privilege/store') ?>', $.param(formData))
                .done(function (res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        form.reset();
                        $('#submenu').val(null).trigger('change');
                    });
                })
                .fail(function (xhr) {
                    const msg = xhr.responseJSON?.error ?? 'Gagal menyimpan privilege';
                    Swal.fire({ icon: 'error', title: 'Error', text: msg });
                });
        }
    });

    $('input[name="privileges[]"]').on('change', function() {
        const checked = $('input[name="privileges[]"]:checked').length;
        if (checked > 0) {
            $('#privileges-error').hide();
        }
    });
});
</script>

<?= $this->endSection() ?>

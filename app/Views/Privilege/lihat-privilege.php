<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="px-4 py-3">
    <h4>Lihat Privilege</h4>
    <hr>

    <div class="table-responsive bg-white p-3 rounded shadow-sm">
        <table class="table table-bordered table-hover align-middle" id="privilegeTable">
            <thead class="table-light">
                <tr>
                    <th class="text-center">No</th>
                    <th>Role</th>
                    <th>Submenu</th>
                    <th class="text-center">Create</th>
                    <th class="text-center">Update</th>
                    <th class="text-center">Delete</th>
                    <th class="text-center">Approve</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($privileges as $i => $p): ?>
                    <tr>
                        <td class="text-center"><?= $i + 1 ?></td>
                        <td><?= esc($p['role']) ?></td>
                        <td><?= esc($p['submenu']) ?></td>

                        <?php
                            $allActions = ['create', 'update', 'delete', 'approve'];
                            $userActions = array_map('strtolower', $p['actions']);
                        ?>
                        <?php foreach ($allActions as $act): ?>
                            <td class="text-center">
                                <span class="badge <?= in_array($act, $userActions) ? 'bg-success' : 'bg-danger' ?>">
                                    <?= in_array($act, $userActions) ? 'Yes' : 'No' ?>
                                </span>
                            </td>
                        <?php endforeach ?>

                        <td class="text-center">
                            <button class="btn btn-sm btn-link text-primary p-0"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editModal"
                                    onclick='openEditModal(
                                        <?= json_encode($p['id']) ?>,
                                        <?= json_encode($p['role_id']) ?>,
                                        <?= json_encode($p['role']) ?>,
                                        <?= json_encode($p['submenu_ids']) ?>,
                                        <?= json_encode($p['actions']) ?>
                                    )'>
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button class="btn btn-sm btn-link text-danger p-0"
                                    onclick='confirmDelete(<?= json_encode($p['id']) ?>)'>
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <div class="modal-header">
                <h5 class="modal-title">Edit Privilege</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editPrivilegeForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" id="editRole" name="role" required>
                            <option value="">Pilih Role...</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['id'] ?>">
                                    <?= esc($role['name']) ?>
                                </option>
                            <?php endforeach ?>
                        </select>
                        <input type="hidden" id="editId" name="id">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sub Menu</label>
                        <select class="form-select" id="editSubmenu" name="submenu[]" multiple required>
                            <?php foreach ($submenus as $s): ?>
                                <option value="<?= $s['id'] ?>">
                                    <?= esc($s['menu_name'] . ' > ' . $s['name']) ?>
                                </option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Privilege</label>
                        <div class="d-flex flex-wrap gap-3 ps-2 privileges-options">
                            <label><input type="checkbox" name="privileges[]" value="create"> Create</label>
                            <label><input type="checkbox" name="privileges[]" value="update"> Update</label>
                            <label><input type="checkbox" name="privileges[]" value="delete"> Delete</label>
                            <label><input type="checkbox" name="privileges[]" value="approve"> Approve</label>
                        </div>
                        <div class="invalid-feedback" style="display: none;">
                            Minimal satu privilege harus dipilih.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Script JS Library -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Select2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Script JS Custom -->
<script>
    const csrfName = '<?= csrf_token() ?>';
    const csrfHash = '<?= csrf_hash() ?>';

    $(document).ready(function () {
        $('#privilegeTable').DataTable();
        $('#editSubmenu').select2({
            width: '100%',
            placeholder: 'Pilih submenu...',
            dropdownParent: $('#editModal'),
            allowClear: true
        });
    });

    function openEditModal(id, roleId, roleName, submenuList, privileges) {
        $('#editId').val(id);
        $('#editRole').val(roleId).trigger('change');
        $('#editSubmenu').val(submenuList).trigger('change');
        
        $('input[name="privileges[]"]').prop('checked', false);
        privileges.forEach(p => {
            $(`input[name="privileges[]"][value="${p}"]`).prop('checked', true);
        });
    }

    $('#editPrivilegeForm').on('submit', function (e) {
        e.preventDefault();
        const form = this;
        const privileges = $(form).find('input[name="privileges[]"]:checked').length;

        if (privileges === 0) {
            const privilegesGroup = $(form).find('.privileges-options').parent();
            privilegesGroup.find('.invalid-feedback').show();
            return false;
        } else {
            $(form).find('.privileges-options').parent().find('.invalid-feedback').hide();
        }

        if (!form.checkValidity()) {
            e.stopPropagation();
            form.classList.add('was-validated');
            return false;
        }

        const formData = $(this).serialize() + `&${csrfName}=${csrfHash}`;
        $.ajax({
            url: '<?= base_url('create-privilege/update') ?>',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(res) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: res.message,
                    confirmButtonText: 'OK'
                }).then(() => {
                    $('#editModal').modal('hide');
                    location.reload();
                });
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.error ?? 'Gagal memperbarui privilege';
                Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
            }
        });
    });

    function confirmDelete(id) {
        Swal.fire({
            title: 'Yakin ingin menghapus privilege ini?',
            text: 'Tindakan ini tidak dapat dibatalkan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('create-privilege/delete') ?>',
                    method: 'POST',
                    data: {
                        id: id,
                        [csrfName]: csrfHash
                    },
                    dataType: 'json',
                    success: function (res) {
                        Swal.fire('Berhasil', res.message, 'success')
                            .then(() => location.reload());
                    },
                    error: function (xhr) {
                        const errorMsg = xhr.responseJSON?.error ?? 'Gagal menghapus privilege';
                        Swal.fire('Gagal', errorMsg, 'error');
                    }
                });
            }
        });
    }
</script>

<?= $this->endSection() ?>

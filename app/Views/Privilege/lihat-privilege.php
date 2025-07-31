<?= $this->include('partials/alerts') ?>
<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>


<div class="px-4 py-3">
    <h4>Privilege List</h4>
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
                    <?php 
                    // Cek apakah user memiliki privilege untuk update atau delete
                    $userPrivileges = session('privileges');
                    $currentPage = 'create-privilege'; // sesuaikan dengan slug submenu privilege
                    $canUpdate = isset($userPrivileges[$currentPage]['can_update']) && $userPrivileges[$currentPage]['can_update'] == 1;
                    $canDelete = isset($userPrivileges[$currentPage]['can_delete']) && $userPrivileges[$currentPage]['can_delete'] == 1;
                    
                    // Tampilkan kolom aksi hanya jika user punya privilege update atau delete
                    if ($canUpdate || $canDelete): ?>
                        <th class="text-center">Aksi</th>
                    <?php endif; ?>
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

                        <?php if ($canUpdate || $canDelete): ?>
                            <td class="text-center">
                                
                                
                                <?php if ($canDelete): ?>
                                    <button class="btn btn-sm btn-link text-danger p-0"
                                            onclick='confirmDelete(<?= json_encode($p['id']) ?>)'>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                <?php endif; ?>

                                <?php if ($canUpdate): ?>
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
                                <?php endif; ?>

                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Edit - Hanya tampil jika user punya privilege update -->
<?php if ($canUpdate): ?>
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <div class="modal-header">
                <h5 class="modal-title">Edit Privilege</h5>
            </div>
            <form id="editPrivilegeForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" id="editRole" name="role" required>
                            <option value="">Choose Role...</option>
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
                    </div>
                </div>
                <div class="modal-footer d-grid gap-2" style="grid-template-columns: 1fr 1fr;">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

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
        
        // Initialize Select2 hanya jika modal edit ada
        <?php if ($canUpdate): ?>
        $('#editSubmenu').select2({
            width: '100%',
            placeholder: 'Choose submenu...',
            dropdownParent: $('#editModal'),
            allowClear: true
        });
        <?php endif; ?>
    });

    <?php if ($canUpdate): ?>
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
                    title: 'Success',
                    text: res.message,
                    confirmButtonText: 'OK'
                }).then(() => {
                    $('#editModal').modal('hide');
                    location.reload();
                });
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.error ?? 'Failed to update privilege';
                Swal.fire({ icon: 'error', title: 'Failed', text: msg });
            }
        });
    });
    <?php endif; ?>

    <?php if ($canDelete): ?>
    function confirmDelete(id) {
        Swal.fire({
            title: 'Are you sure?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: 'rgba(118, 125, 131, 1)',
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel'
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
                        Swal.fire('Success', 'Successfully deleted', 'success')
                            .then(() => location.reload());
                    },
                    error: function (xhr) {
                        const errorMsg = xhr.responseJSON?.error ?? 'Failed to delete privilege';
                        Swal.fire('Failed', errorMsg, 'error');
                    }
                });
            }
        });
    }
    <?php endif; ?>
</script>

<?= $this->endSection() ?>


<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<?php
$privileges = session()->get('privileges');
$currentSubmenu = 'create-role'; 

$canCreate = isset($privileges[$currentSubmenu]['can_create']) ? $privileges[$currentSubmenu]['can_create'] : 0;
$canUpdate = isset($privileges[$currentSubmenu]['can_update']) ? $privileges[$currentSubmenu]['can_update'] : 0;
$canDelete = isset($privileges[$currentSubmenu]['can_delete']) ? $privileges[$currentSubmenu]['can_delete'] : 0;
?>

<div class="px-4 py-3 w-100">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Role List</h4>
    </div>
    <hr>

    <div class="table-responsive shadow-sm rounded bg-white p-3">
        <table class="table table-bordered table-hover align-middle" id="roleTable">
            <thead class="table-light">
                <tr>
                    <th class="text-center">No</th>
                    <th>Role Name</th>
                    <th>Level</th>
                    <th>Description</th>
                    <th>Status</th>
                    <?php if ($canUpdate || $canDelete): ?>
                        <th class="text-center">Action</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($roles as $role): ?>
                <tr>
                    <td class="text-center"><?= $i++ ?></td>
                    <td><?= esc($role['name']) ?></td>
                    <td>
                        <?php if ($role['access_level'] == '1'): ?>
                            Directorate/Faculty
                        <?php elseif ($role['access_level'] == '2'): ?>
                            Unit
                        <?php else: ?>
                            <?= esc($role['access_level']) ?>
                        <?php endif; ?>
                    </td>
                    <td><?= esc($role['description']) ?></td>
                    <td>
                        <?php if ($role['status'] == 1): ?>
                            <span class="badge bg-success">Active</span>
                        <?php elseif ($role['status'] == 2): ?>
                            <span class="badge bg-secondary">Inactive</span>
                        <?php else: ?>
                            <span class="badge bg-dark">Deleted</span>
                        <?php endif; ?>
                    </td>
                    <?php if ($canUpdate || $canDelete): ?>
                        <td class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <?php if ($canUpdate): ?>
                                    <button 
                                        class="btn btn-link p-0 text-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editModal"
                                        onclick="openEditModal(
                                            <?= $role['id'] ?>, 
                                            '<?= esc($role['name'], 'js') ?>', 
                                            '<?= esc($role['access_level']) ?>', 
                                            '<?= esc($role['description'], 'js') ?>', 
                                            '<?= esc($role['status']) ?>'
                                        )">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                <?php endif; ?>
                                <?php if ($canDelete): ?>
                                    <form action="<?= site_url('create-role/delete') ?>" method="post" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $role['id'] ?>">
                                        <button type="submit" class="btn btn-link p-0" onclick="SwalConfirmDelete(this)">
                                            <i class="bi bi-trash text-danger"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Edit Role -->
<?php if ($canUpdate): ?>
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <div class="modal-header">
                <h5 class="modal-title">Edit Role</h5>
            </div>
            <form method="post" action="<?= site_url('create-role/update') ?>" id="editRoleForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <input type="hidden" name="id" id="editRoleId">
                    <div class="mb-3">
                        <label class="form-label">Role Name <span class="text-danger">*</span></label>
                        <input type="text" name="role_name" id="editRoleName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Level <span class="text-danger">*</span></label>
                        <div class="mt-2 d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="role_level" id="editRoleLevel1" value="1" required>
                                <label class="form-check-label" for="editRoleLevel1">
                                    Directorate/Faculty
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="role_level" id="editRoleLevel2" value="2" required>
                                <label class="form-check-label" for="editRoleLevel2">
                                    Unit
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea name="role_description" id="editRoleDescription" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label d-block">Status <span class="text-danger">*</span></label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="role_status" id="statusActive" value="active" required>
                            <label class="form-check-label" for="statusActive">Active</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="role_status" id="statusInactive" value="inactive">
                            <label class="form-check-label" for="statusInactive">Inactive</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-grid gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (session()->has('swal')) : ?>
<script>
    Swal.fire({
        icon: '<?= session('swal.icon') ?>',
        title: '<?= session('swal.title') ?>',
        text: '<?= session('swal.text') ?>',
        confirmButtonColor: '#7c3aed'
    });
</script>
<?php endif; ?>

<?php if (session()->has('added_message')) : ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '<?= session('added_message') ?>',
        confirmButtonColor: '#7c3aed'
    });
</script>
<?php endif; ?>

<?php if (session()->has('updated_message')) : ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '<?= session('updated_message') ?>',
        confirmButtonColor: '#7c3aed'
    });
</script>
<?php endif; ?>

<?php if (session()->has('deleted_message')) : ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '<?= session('deleted_message') ?>',
        confirmButtonColor: '#7c3aed'
    });
</script>
<?php endif; ?>

<script>
    $(document).ready(function () {
        $('#roleTable').DataTable({
            responsive: true,
            pageLength: 10,
            ordering: true,
            searching: true,
            language: {
                search: "Search roles:",
                lengthMenu: "Show _MENU_ roles per page",
                info: "Showing _START_ to _END_ of _TOTAL_ roles",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });
    });

    const canUpdate = <?= json_encode($canUpdate) ?>;
    const canDelete = <?= json_encode($canDelete) ?>;

    <?php if ($canDelete): ?>
    function SwalConfirmDelete(elem) {
        event.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#d33',
            cancelButtonColor: 'rgba(118, 125, 131, 1)',
        }).then((result) => {
            if (result.isConfirmed) {
                elem.closest('form').submit();
            }
        });
    }
    <?php endif; ?>

    <?php if ($canUpdate): ?>
    function openEditModal(id, roleName, roleLevel, roleDescription, roleStatus) {
        document.getElementById('editRoleId').value = id;
        document.getElementById('editRoleName').value = roleName;

        if (roleLevel == '1') {
            document.getElementById('editRoleLevel1').checked = true;
        } else if (roleLevel == '2') {
            document.getElementById('editRoleLevel2').checked = true;
        }
        
        document.getElementById('editRoleDescription').value = roleDescription;

        if (roleStatus == 1) {
            document.getElementById('statusActive').checked = true;
        } else if (roleStatus == 2) {
            document.getElementById('statusInactive').checked = true;
        }
    }
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')): ?>
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '<?= session()->getFlashdata('success') ?>',
        confirmButtonColor: '#7c3aed'
    });
    <?php elseif (session()->getFlashdata('error')): ?>
    Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: '<?= session()->getFlashdata('error') ?>',
        confirmButtonColor: '#7c3aed'
    });
    <?php endif; ?>
</script>

<?= $this->endSection() ?>

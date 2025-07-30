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
                    <th class="text-center" style="width: 5%;">No</th>
                    <th>Role Name</th>
                    <th>Level</th>
                    <th>Description</th>
                    <th>Status</th>
                    <?php if ($canUpdate || $canDelete): ?>
                        <th class="text-center" style="width: 20%;">Action</th>
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
                                <?php if ($canDelete): ?>
                                    <form action="<?= site_url('create-role/delete') ?>" method="post" onsubmit="return confirmDelete(event, this);">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $role['id'] ?>">
                                        <button type="submit" class="btn btn-link p-0 text-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
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
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <div class="modal-header">
                <h5 class="modal-title">Edit Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    });

    <?php if ($canDelete): ?>
    function confirmDelete(event, form) {
        event.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: 'rgba(118, 125, 131, 1)',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
        return false;
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

        let statusText = '';
        if (roleStatus == 1) {
            statusText = 'active';
        } else if (roleStatus == 2) {
            statusText = 'inactive';
        }
        document.getElementById('editRoleStatus').value = statusText;
    }

    $('#editRoleForm').on('submit', function() {
        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Saving...');
    });

    const editForm = document.getElementById('editRoleForm');
    if (editForm) {
        editForm.addEventListener('submit', function (e) {
            const inputName = document.getElementById('editRoleName').value.trim().toLowerCase();
            const currentId = document.getElementById('editRoleId').value;

            let isDuplicate = false;
            <?php if (!empty($roles)): ?>
                <?php foreach ($roles as $role): ?>
                    if ('<?= strtolower(trim($role['name'])) ?>' === inputName && '<?= $role['id'] ?>' !== currentId) {
                        isDuplicate = true;
                    }
                <?php endforeach; ?>
            <?php endif; ?>

            if (isDuplicate) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Role name already exists. Please choose a different name.',
                    confirmButtonColor: '#abb3baff'
                });
                return false;
            }
        });
    }
    <?php endif; ?>

    <?php if ($canCreate): ?>
    $('#addRoleForm').on('submit', function() {
        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Saving...');
    });

    const addForm = document.getElementById('addRoleForm');
    if (addForm) {
        addForm.addEventListener('submit', function (e) {
            const inputName = document.getElementById('addRoleName').value.trim().toLowerCase();

            let isDuplicate = false;
            <?php if (!empty($roles)): ?>
                <?php foreach ($roles as $role): ?>
                    if ('<?= strtolower(trim($role['name'])) ?>' === inputName) {
                        isDuplicate = true;
                    }
                <?php endforeach; ?>
            <?php endif; ?>

            if (isDuplicate) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Role name already exists. Please choose a different name.',
                    confirmButtonColor: '#abb3baff'
                });
                return false;
            }
        });
    }
    <?php endif; ?>
</script>

<?= $this->endSection() ?>
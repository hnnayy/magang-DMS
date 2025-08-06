<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>


<?php
$privileges = session()->get('privileges');
$currentSubmenu = 'create-menu';

$canCreate = isset($privileges[$currentSubmenu]['can_create']) ? $privileges[$currentSubmenu]['can_create'] : 0;
$canUpdate = isset($privileges[$currentSubmenu]['can_update']) ? $privileges[$currentSubmenu]['can_update'] : 0;
$canDelete = isset($privileges[$currentSubmenu]['can_delete']) ? $privileges[$currentSubmenu]['can_delete'] : 0;
?>

<div class="px-4 py-3 w-100">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Menu List</h4>
    </div>
    <hr>

    <?= $this->include('partials/alerts') ?>

    <div class="table-responsive shadow-sm rounded bg-white p-3">
        <table class="table table-bordered table-hover align-middle" id="menuTable">
            <thead class="table-light">
                <tr>
                    <th class="text-center" style="width: 5%;">No</th>
                    <th style="width: 30%;">Menu Name</th>
                    <th style="width: 30%;">Icon</th>
                    <th class="text-center" style="width: 10%;">Status</th>
                    <?php if ($canUpdate || $canDelete): ?>
                        <th class="text-center" style="width: 25%;">Action</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($menus)): ?>
                    <?php $no = 1; foreach ($menus as $menu): ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td><?= esc($menu['name']) ?></td>
                            <td><?= esc($menu['icon']) ?></td>
                            <td class="text-center">
                                <span class="badge <?= $menu['status'] == 1 ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $menu['status'] == 1 ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <?php if ($canUpdate || $canDelete): ?>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        <?php if ($canUpdate): ?>
                                            <button class="btn btn-link p-0 text-primary" data-bs-toggle="modal" data-bs-target="#editModal"
                                                onclick="openEditModal(
                                                    <?= $menu['id'] ?>, 
                                                    '<?= esc($menu['name'], 'js') ?>', 
                                                    '<?= esc($menu['icon'], 'js') ?>', 
                                                    <?= $menu['status'] ?>
                                                )">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($canDelete): ?>
                                            <form action="<?= site_url('create-menu/delete') ?>" method="post" onsubmit="return confirmDelete(event, this);">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="id" value="<?= $menu['id'] ?>">
                                                <button type="submit" class="btn btn-link p-0 text-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?= ($canUpdate || $canDelete) ? '5' : '4' ?>" class="text-center text-muted">No menu data available</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Edit Menu -->
<?php if ($canUpdate): ?>
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <div class="modal-header">
                <h5 class="modal-title">Edit Menu</h5>
            </div>
            <form method="post" id="editMenuForm" action="<?= site_url('create-menu/update') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <input type="hidden" name="id" id="editMenuId">
                    <div class="mb-3">
                        <label class="form-label">Menu Name <span class="text-danger">*</span></label>
                        <input type="text" name="menu_name" id="editMenuName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icon <span class="text-danger">*</span></label>
                        <input type="text" name="icon" id="editMenuIcon" class="form-control" required 
                               placeholder="e.g: home, user, settings">
                        <small class="form-text text-muted">Only lowercase letters, numbers, spaces, and hyphens (-) allowed</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label d-block">Status <span class="text-danger">*</span></label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="editStatusActive" value="1" required>
                            <label class="form-check-label" for="editStatusActive">Active</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="editStatusInactive" value="2" required>
                            <label class="form-check-label" for="editStatusInactive">Inactive</label>
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

<!-- Scripts -->z
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function () {
        $('#menuTable').DataTable({
            "pageLength": 10,
            "ordering": true,
            "searching": true,
            "language": {
                "search": "Search:",
                "lengthMenu": "Show _MENU_ entries per page",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
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
            confirmButtonText: 'Yes, delete it',
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
    function openEditModal(id, name, icon, status) {
        document.getElementById('editMenuId').value = id;
        document.getElementById('editMenuName').value = name;
        document.getElementById('editMenuIcon').value = icon;
        document.getElementById('editStatusActive').checked = status == 1;
        document.getElementById('editStatusInactive').checked = status == 2;
    }

    document.getElementById('editMenuIcon').addEventListener('input', function(e) {
        const value = e.target.value;
        const regex = /^[a-z0-9\s-]*$/;
        
        if (!regex.test(value)) {
            e.target.setCustomValidity('Icon only allows lowercase letters, numbers, spaces, and hyphens (-)');
            e.target.classList.add('is-invalid');
        } else {
            e.target.setCustomValidity('');
            e.target.classList.remove('is-invalid');
        }
    });

    const editForm = document.getElementById('editMenuForm');
    editForm.addEventListener('submit', function (e) {
        const inputName = document.getElementById('editMenuName').value.trim().toLowerCase();
        const currentId = document.getElementById('editMenuId').value;

        let isDuplicate = false;
        <?php if (!empty($menus)): ?>
            <?php foreach ($menus as $menu): ?>
                if ('<?= strtolower(trim($menu['name'])) ?>' === inputName && '<?= $menu['id'] ?>' !== currentId) {
                    isDuplicate = true;
                }
            <?php endforeach; ?>
        <?php endif; ?>

        if (isDuplicate) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Menu name already exists. Please choose a different name.',
                confirmButtonColor: '#abb3baff'
            });
            return false;
        }
    });
    <?php endif; ?>

    <?php if ($canCreate): ?>
    document.getElementById('addMenuIcon').addEventListener('input', function(e) {
        const value = e.target.value;
        const regex = /^[a-z0-9\s-]*$/;
        
        if (!regex.test(value)) {
            e.target.setCustomValidity('Icon only allows lowercase letters, numbers, spaces, and hyphens (-)');
            e.target.classList.add('is-invalid');
        } else {
            e.target.setCustomValidity('');
            e.target.classList.remove('is-invalid');
        }
    });

    const addForm = document.getElementById('addMenuForm');
    addForm.addEventListener('submit', function (e) {
        const inputName = document.getElementById('addMenuName').value.trim().toLowerCase();

        let isDuplicate = false;
        <?php if (!empty($menus)): ?>
            <?php foreach ($menus as $menu): ?>
                if ('<?= strtolower(trim($menu['name'])) ?>' === inputName) {
                    isDuplicate = true;
                }
            <?php endforeach; ?>
        <?php endif; ?>

        if (isDuplicate) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Menu name already exists. Please choose a different name.',
                confirmButtonColor: '#abb3baff'
            });
            return false;
        }
    });
    <?php endif; ?>
</script>

<?= $this->endSection() ?>

<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<?php
// Ambil privilege dari session untuk submenu ini
$privileges = session()->get('privileges');
$currentSubmenu = 'create-submenu'; // atau sesuai dengan slug submenu management Anda

// Set default privileges jika tidak ada
$canCreate = isset($privileges[$currentSubmenu]['can_create']) ? $privileges[$currentSubmenu]['can_create'] : 0;
$canUpdate = isset($privileges[$currentSubmenu]['can_update']) ? $privileges[$currentSubmenu]['can_update'] : 0;
$canDelete = isset($privileges[$currentSubmenu]['can_delete']) ? $privileges[$currentSubmenu]['can_delete'] : 0;
?>

<div class="px-4 py-3 w-100">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Submenu List</h4>
        <?php if ($canCreate): ?>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="bi bi-plus-circle"></i> Add Submenu
            </button>
        <?php endif; ?>
    </div>
    <hr>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('added_message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('added_message') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('updated_message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('updated_message') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('deleted_message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('deleted_message') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="table-responsive shadow-sm rounded bg-white p-3">
        <table class="table table-bordered table-hover align-middle" id="submenuTable">
            <thead class="table-light">
                <tr>
                    <th class="text-center" style="width: 5%;">No</th>
                    <th style="width: 25%;">Menu</th>
                    <th style="width: 30%;">Submenu</th>
                    <th class="text-center" style="width: 15%;">Status</th>
                    <?php if ($canUpdate || $canDelete): ?>
                        <th class="text-center" style="width: 20%;">Action</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody id="tableBody">
                <?php if (!empty($submenus)) : ?>
                    <?php $no = 1; foreach ($submenus as $submenu) : ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td><?= esc($submenu['parent_name']) ?></td>
                            <td><?= esc($submenu['name']) ?></td>
                            <td class="text-center">
                                <span class="badge <?= $submenu['status'] == 1 ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $submenu['status'] == 1 ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <?php if ($canUpdate || $canDelete): ?>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        <?php if ($canDelete): ?>
                                            <form action="<?= site_url('create-submenu/delete') ?>" method="post" onsubmit="return confirmDelete(event, this);">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="id" value="<?= $submenu['id'] ?>">
                                                <button type="submit" class="btn btn-link p-0 text-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if ($canUpdate): ?>
                                            <button class="btn btn-link p-0 text-primary" data-bs-toggle="modal" data-bs-target="#editModal"
                                                onclick="openEditModal(<?= $submenu['id'] ?>, <?= esc($submenu['parent']) ?>, '<?= esc($submenu['name']) ?>', <?= $submenu['status'] ?>)">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach ?>
                <?php else : ?>
                    <tr>
                        <td colspan="<?= ($canUpdate || $canDelete) ? '5' : '4' ?>" class="text-center text-muted">No submenu data available.</td>
                    </tr>
                <?php endif ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Add Submenu -->
<?php if ($canCreate): ?>
<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <div class="modal-header">
                <h5 class="modal-title">Add New Submenu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="addSubmenuForm" action="<?= site_url('create-submenu/store') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Menu <span class="text-danger">*</span></label>
                        <select name="parent" id="addParentName" class="form-select" required>
                            <option value="">-- Choose Menu --</option>
                            <?php foreach ($menus as $menu) : ?>
                                <option value="<?= $menu['id'] ?>"><?= esc($menu['name']) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Submenu <span class="text-danger">*</span></label>
                        <input type="text" name="submenu" id="addSubmenuName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label d-block">Status <span class="text-danger">*</span></label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="addStatusActive" value="1" required checked>
                            <label class="form-check-label" for="addStatusActive">Active</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="addStatusInactive" value="2" required>
                            <label class="form-check-label" for="addStatusInactive">Inactive</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Submenu</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Edit Submenu -->
<?php if ($canUpdate): ?>
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <div class="modal-header">
                <h5 class="modal-title">Edit Submenu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="editUnitForm" action="<?= site_url('create-submenu/update') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <input type="hidden" name="id" id="editUnitId">
                    <div class="mb-3">
                        <label class="form-label">Menu <span class="text-danger">*</span></label>
                        <select name="parent" id="editParentName" class="form-select" required>
                            <option value="">-- Choose Menu --</option>
                            <?php foreach ($menus as $menu) : ?>
                                <option value="<?= $menu['id'] ?>"><?= esc($menu['name']) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Submenu <span class="text-danger">*</span></label>
                        <input type="text" name="submenu" id="editUnitName" class="form-control" required>
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
        // Auto hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);

        // Initialize DataTable
        const table = $('#submenuTable');
        const thCount = table.find('thead th').length;
        const tdCount = table.find('tbody tr:visible:first td').length;

        if (thCount === tdCount || table.find('tbody tr').length === 0) {
            table.DataTable({
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
        } else {
            console.warn('⚠️ DataTables failed to initialize because the number of columns does not match.');
        }
    });

    <?php if ($canDelete): ?>
    function confirmDelete(event, form) {
        event.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: "This submenu will be deleted and cannot be recovered!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
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
    function openEditModal(id, parentId, submenuName, status) {
        const form = document.getElementById('editUnitForm');
        if (!form) return;
        
        // Set nilai ke form fields
        document.getElementById('editUnitId').value = id;
        document.getElementById('editParentName').value = parentId;
        document.getElementById('editUnitName').value = submenuName;

        // Set radio button status
        if (status == 1) {
            document.getElementById('editStatusActive').checked = true;
        } else {
            document.getElementById('editStatusInactive').checked = true;
        }
    }
    <?php endif; ?>

    <?php if ($canCreate): ?>
    // Client-side validation for duplicate submenu names on add
    const addForm = document.getElementById('addSubmenuForm');
    if (addForm) {
        addForm.addEventListener('submit', function (e) {
            const inputName = document.getElementById('addSubmenuName').value.trim().toLowerCase();
            const parentId = document.getElementById('addParentName').value;

            let isDuplicate = false;
            <?php if (!empty($submenus)): ?>
                <?php foreach ($submenus as $submenu): ?>
                    if ('<?= strtolower(trim($submenu['name'])) ?>' === inputName && '<?= $submenu['parent'] ?>' === parentId) {
                        isDuplicate = true;
                    }
                <?php endforeach; ?>
            <?php endif; ?>

            if (isDuplicate) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Submenu name already exists in this menu. Please choose a different name.',
                    confirmButtonColor: '#abb3baff'
                });
                return false;
            }
        });
    }
    <?php endif; ?>

    <?php if ($canUpdate): ?>
    // Client-side validation for duplicate submenu names on edit
    const editForm = document.getElementById('editUnitForm');
    if (editForm) {
        editForm.addEventListener('submit', function (e) {
            const inputName = document.getElementById('editUnitName').value.trim().toLowerCase();
            const parentId = document.getElementById('editParentName').value;
            const currentId = document.getElementById('editUnitId').value;

            let isDuplicate = false;
            <?php if (!empty($submenus)): ?>
                <?php foreach ($submenus as $submenu): ?>
                    if ('<?= strtolower(trim($submenu['name'])) ?>' === inputName && 
                        '<?= $submenu['parent'] ?>' === parentId && 
                        '<?= $submenu['id'] ?>' !== currentId) {
                        isDuplicate = true;
                    }
                <?php endforeach; ?>
            <?php endif; ?>

            if (isDuplicate) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Submenu name already exists in this menu. Please choose a different name.',
                    confirmButtonColor: '#abb3baff'
                });
                return false;
            }
        });
    }
    <?php endif; ?>
</script>

<?= $this->endSection() ?>
<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="px-4 py-3 w-100">
    <h4>Unit List</h4>
    <hr>

    <div class="table-responsive shadow-sm rounded bg-white p-3">
        <table class="table table-bordered table-hover align-middle" id="documentTable">
            <thead class="table-light">
                <tr>
                    <th class="text-center">No</th>
                    <th>Faculty/Directorate</th>
                    <th>Unit</th>
                    <th class="text-center">Status</th>
                    <?php 
                    // Check if user has any action privileges for this page
                    $privileges = session('privileges');
                    $canUpdate = isset($privileges['unit-list']['can_update']) && $privileges['unit-list']['can_update'] == 1;
                    $canDelete = isset($privileges['unit-list']['can_delete']) && $privileges['unit-list']['can_delete'] == 1;
                    
                    if ($canUpdate || $canDelete): ?>
                        <th class="text-center">Action</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($units)) : ?>
                <?php foreach ($units as $index => $unit) : ?>
                    <tr>
                        <td class="text-center"><?= $index + 1 ?></td>
                        <td><?= esc($unit['parent_name']) ?></td>
                        <td><?= esc($unit['name']) ?></td>
                        <td class="text-center">
                            <span class="badge <?= $unit['status'] == 1 ? 'bg-success' : 'bg-secondary' ?>">
                                <?= $unit['status'] == 1 ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        
                        <?php if ($canUpdate || $canDelete): ?>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <?php if ($canDelete): ?>
                                    <form action="<?= site_url('create-unit/delete') ?>" method="post" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $unit['id'] ?>">
                                        <button type="submit" class="btn btn-link p-0" onclick="SwalConfirmDelete(this)">
                                            <i class="bi bi-trash text-danger"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if ($canUpdate): ?>
                                    <button class="btn btn-link p-0 text-primary" data-bs-toggle="modal" data-bs-target="#editModal"
                                        onclick="openEditModal(<?= $unit['id'] ?>, '<?= esc($unit['parent_id']) ?>', '<?= esc($unit['name']) ?>', '<?= esc($unit['status']) ?>')">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td class="text-center" colspan="<?= ($canUpdate || $canDelete) ? '5' : '4' ?>">Belum ada data</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Edit Unit - Only show if user has update privilege -->
<?php if ($canUpdate): ?>
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <div class="modal-header">
                <h5 class="modal-title">Edit Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="editUnitForm" action="<?= site_url('create-unit/update') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="editUnitId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Faculty/Directorate</label>
                        <select name="parent_id" id="editParentId" class="form-control" required>
                            <?php foreach ($fakultas as $f) : ?>
                                <option value="<?= $f['id'] ?>"><?= esc($f['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Unit</label>
                        <input type="text" name="unit_name" id="editUnitName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label d-block">Status</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="statusActive" value="1">
                            <label class="form-check-label" for="statusActive">Active</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="statusInactive" value="2">
                            <label class="form-check-label" for="statusInactive">Inactive</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary w-100">Save Changes</button>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (session()->has('swal')) : ?>
<script>
    Swal.fire({
        icon: '<?= session('swal.icon') ?>',
        title: '<?= session('swal.title') ?>',
        text: '<?= session('swal.text') ?>',
        confirmButtonColor: '#d33'
    });
</script>
<?php endif; ?>

<!-- Success messages -->
<?php if (session()->has('added_message')) : ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '<?= session('added_message') ?>',
        confirmButtonColor: '#28a745'
    });
</script>
<?php endif; ?>

<?php if (session()->has('updated_message')) : ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '<?= session('updated_message') ?>',
        confirmButtonColor: '#28a745'
    });
</script>
<?php endif; ?>

<?php if (session()->has('deleted_message')) : ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '<?= session('deleted_message') ?>',
        confirmButtonColor: '#28a745'
    });
</script>
<?php endif; ?>

<script>
    // Check privileges from PHP session
    const canUpdate = <?= json_encode($canUpdate) ?>;
    const canDelete = <?= json_encode($canDelete) ?>;

    // Only define openEditModal function if user has update privilege
    <?php if ($canUpdate): ?>
    function openEditModal(id, parentId, unitName, status) {
        $('#editUnitId').val(id);
        $('#editUnitName').val(unitName);
        $('#editParentId').val(parentId);

        // Reset radio, lalu set sesuai value
        $('input[name="status"]').prop('checked', false);
        if (status == 1) {
            $('#statusActive').prop('checked', true);
        } else if (status == 2) {
            $('#statusInactive').prop('checked', true);
        }
    }
    <?php endif; ?>

    // Only define SwalConfirmDelete function if user has delete privilege
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

    $(document).ready(function () {
        $('#documentTable').DataTable({
            "language": {
                "search": "Search:",
                "lengthMenu": "Show _MENU_ entries",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "infoEmpty": "Showing 0 to 0 of 0 entries",
                "infoFiltered": "(filtered from _MAX_ total entries)",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                }
            }
        });
    });
</script>

<?= $this->endSection() ?>
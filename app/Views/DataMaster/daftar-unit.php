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
                    <th class="text-center">Action</th>
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
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <form action="<?= site_url('data-master/unit/' . $unit['id'] . '/delete') ?>" method="post" class="d-inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-link p-0" onclick="SwalConfirmDelete(this)">
                                        <i class="bi bi-trash text-danger"></i>
                                    </button>
                                </form>
                                <button class="btn btn-link p-0 text-primary" data-bs-toggle="modal" data-bs-target="#editModal"
                                    onclick="openEditModal(<?= $unit['id'] ?>, '<?= esc($unit['parent_id']) ?>', '<?= esc($unit['name']) ?>', '<?= esc($unit['status']) ?>')">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td class="text-center" colspan="5">Belum ada data</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <div class="modal-header">
                <h5 class="modal-title">Edit Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="editUnitForm">
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

<script>
    function openEditModal(id, parentId, unitName, status) {
        $('#editUnitForm').attr('action', `<?= site_url('data-master/unit') ?>/${id}/update`);
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

    function SwalConfirmDelete(elem) {
        event.preventDefault();
        Swal.fire({
            title: 'Hapus unit ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                elem.closest('form').submit();
            }
        });
    }

    $(document).ready(function () {
        $('#documentTable').DataTable();
    });
</script>

<?= $this->endSection() ?>

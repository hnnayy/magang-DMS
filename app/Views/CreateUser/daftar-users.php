<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="px-4 py-3 w-100">
    <h4>Lihat User</h4>
    <hr>

    <!-- Table -->
    <div class="table-container table-responsive bg-white p-3 shadow-sm rounded">
        <table class="table table-bordered table-hover align-middle" id="userTable" style="width: 100%;">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Employee ID</th>
                    <th>Direktorat</th>
                    <th>Unit</th>
                    <th>Fullname</th>
                    <th>Role</th>
                    <th class="text-center noExport">Aksi</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <?php $i = 1; foreach ($users as $user): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= esc($user['id']) ?></td>
                    <td><?= esc($user['parent_name']) ?></td>
                    <td><?= esc($user['unit_name']) ?></td>
                    <td><?= esc($user['fullname']) ?></td>
                    <td><?= esc($user['role_name'] ?? 'N/A') ?></td>
                    <td class="text-center">
                        <a href="#" class="text-danger me-2" title="Delete"><i class="bi bi-trash"></i></a>
                        <a href="#" class="text-primary edit-user"
                            data-bs-toggle="modal"
                            data-bs-target="#editUserModal"
                            data-id="<?= $user['id'] ?>"
                            data-employee="<?= esc($user['id']) ?>"
                            data-directorate="<?= esc($user['parent_name']) ?>"
                            data-unit="<?= esc($user['unit_name']) ?>"
                            data-fullname="<?= esc($user['fullname']) ?>"
                            data-role="<?= esc($user['role_name'] ?? '') ?>">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ======== MODAL EDIT USER ======== -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="editUserModalLabel">Edit User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="editUserForm">
        <div class="modal-body">
          <div class="mb-3">
            <label for="editEmployeeId" class="form-label">Employee ID</label>
            <input type="text" class="form-control" id="editEmployeeId" name="employee_id" readonly>
          </div>

          <div class="mb-3">
            <label for="editDirectorate" class="form-label">Fakultas/Direktorat</label>
            <input type="text" class="form-control" id="editDirectorate" name="directorate" readonly>
          </div>

          <div class="mb-3">
            <label for="editUnit" class="form-label">Unit</label>
            <input type="text" class="form-control" id="editUnit" name="unit" readonly>
          </div>

          <div class="mb-3">
            <label for="editFullname" class="form-label">Fullname</label>
            <input type="text" class="form-control" id="editFullname" name="fullname">
          </div>

          <div class="mb-3">
            <label for="editRole" class="form-label">Role</label>
            <input type="text" class="form-control" id="editRole" name="role">
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary w-100">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('style') ?>
<style>
    .export-buttons .btn {
        background-color: #b41616;
        color: white;
        border-radius: 8px;
        padding: 6px 14px;
    }
    .export-buttons .btn:hover {
        background-color: #921212;
    }
    .table-container {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
    }
    .table th {
        background-color: #f8f9fa;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>

<script>
$(document).ready(function () {
    const table = $('#userTable').DataTable({
        dom: '<"row mb-3"<"col-md-6 d-flex gap-2 export-buttons"B><"col-md-6 text-end"f>>' +
             'rt' +
             '<"row mt-3"<"col-md-6"l><"col-md-6 text-end"p>>',
        pageLength: 5,
        lengthMenu: [5, 10, 25, 50],
        order: [],
        columnDefs: [
            { orderable: false, targets: 6 },
            { className: 'text-center', targets: 6 }
        ],
        buttons: [
            { extend: 'copyHtml5', text: 'Copy', className: 'btn' },
            { extend: 'csvHtml5', text: 'CSV', className: 'btn' },
            { extend: 'excelHtml5', text: 'Excel', className: 'btn' },
            { extend: 'pdfHtml5', text: 'PDF', className: 'btn' },
            { extend: 'print', text: 'Print', className: 'btn' }
        ]
    });

    table.buttons().container().appendTo('.export-buttons');

    $(document).on('click', '.edit-user', function () {
        $('#editEmployeeId').val($(this).data('employee'));
        $('#editDirectorate').val($(this).data('directorate'));
        $('#editUnit').val($(this).data('unit'));
        $('#editFullname').val($(this).data('fullname'));
        $('#editRole').val($(this).data('role'));
    });
});
</script>
<?= $this->endSection() ?>

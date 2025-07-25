<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="px-4 py-3 w-100">
  <h4>Lihat User</h4>
  <hr>

  <div class="table-responsive bg-white p-3 rounded shadow-sm">
    <table class="table table-bordered table-hover align-middle" id="userTable" style="width: 100%;">
      <thead class="table-light">
        <tr>
          <th>No</th>
          <th>Employee ID</th>
          <th>Direktorat</th>
          <th>Unit</th>
          <th>Username</th>
          <th>Fullname</th>
          <th>Role</th>
          <th class="text-center noExport">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php $i = 1; foreach ($users as $user): ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><?= esc($user['id']) ?></td>
          <td><?= esc($user['parent_name']) ?></td>
          <td><?= esc($user['unit_name']) ?></td>
          <td><?= esc($user['username']) ?></td>
          <td><?= esc($user['fullname']) ?></td>
          <td><?= esc($user['role_name'] ?? 'N/A') ?></td>
          <td class="text-center">
            <a href="#" class="text-danger me-2 delete-user" data-id="<?= $user['id'] ?>" title="Delete">
              <i class="bi bi-trash"></i>
            </a>
            <a href="#" class="text-primary edit-user"
              data-bs-toggle="modal"
              data-bs-target="#editUserModal"
              data-id="<?= $user['id'] ?>"
              data-employee="<?= esc($user['id']) ?>"
              data-directorate="<?= esc($user['parent_id']) ?>"
              data-unit="<?= esc($user['unit_id']) ?>"
              data-username="<?= esc($user['username']) ?>"
              data-fullname="<?= esc($user['fullname']) ?>"
              data-role="<?= esc($user['role_name']) ?>">
              <i class="bi bi-pencil-square"></i>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Edit User -->
<div class="modal fade" id="editUserModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow">
      <div class="modal-header">
        <h5 class="modal-title">Edit User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="editUserForm">
        <div class="modal-body">
          <input type="hidden" id="editUserId"> <!-- Hidden field untuk user ID -->
          <div class="mb-3">
            <label for="editEmployeeId">Employee ID</label>
            <input type="text" class="form-control" id="editEmployeeId" readonly>
          </div>
          <div class="mb-3">
            <label for="editDirectorate">Fakultas/Direktorat</label>
            <select class="form-select" id="editDirectorate" name="fakultas" required>
              <option value="">Pilih Fakultas/Direktorat</option>
              <?php foreach ($unitParents as $parent): ?>
                <option value="<?= $parent['id'] ?>"><?= esc($parent['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
         <div class="mb-3">
          <label for="editUnit">Unit</label>
          <select class="form-select" id="editUnit" name="unit" required>
            <option value="">Pilih Unit</option>
            <?php foreach ($units as $unit): ?>
              <option value="<?= $unit['id'] ?>" data-parent="<?= $unit['parent_id'] ?>"><?= esc($unit['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
          <div class="mb-3">
            <label for="editUsername">Username</label>
            <input type="text" class="form-control" id="editUsername">
          </div>
          <div class="mb-3">
            <label for="editFullname">Fullname</label>
            <input type="text" class="form-control" id="editFullname">
          </div>
          <div class="mb-3">
            <label for="editRole">Role</label>
            <select class="form-select" id="editRole" name="role" required>
              <option value="">Pilih Role</option>
              <?php foreach ($roles as $role): ?>
                <option value="<?= esc($role['name']) ?>"><?= esc($role['name']) ?></option>
              <?php endforeach; ?>
            </select>
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

<?= $this->section('script') ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function () {
  const table = $('#userTable').DataTable({
    dom: '<"row mb-3"<"col-md-6 export-buttons d-flex gap-2"B><"col-md-6 text-end"f>>rt<"row mt-3"<"col-md-6"l><"col-md-6 text-end"p>>',
    pageLength: 5,
    order: [],
    columnDefs: [
      { orderable: false, targets: 7 },
      { className: 'text-center', targets: 7 }
    ],
    buttons: [
      {
        extend: 'excel',
        className: 'btn btn-outline-success btn-sm',
        title: 'Data_Users',
        exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] }
      },
      {
        extend: 'pdfHtml5',
        text: 'PDF',
        className: 'btn',
        title: 'Data Users',
        filename: 'data_users',
        exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] },
        orientation: 'landscape',
        pageSize: 'A4',
        customize: function (doc) {
          const now = new Date();
          const waktuCetak = now.toLocaleString('id-ID', {
            day: '2-digit', month: '2-digit', year: 'numeric',
            hour: '2-digit', minute: '2-digit'
          });

          if (doc.content[0] && doc.content[0].text === 'Data Users') {
            doc.content.splice(0, 1);
          }

          doc.content.unshift({
            text: 'Data Users',
            alignment: 'center',
            bold: true,
            fontSize: 16,
            margin: [0, 0, 0, 15]
          });

          doc.styles.tableHeader = {
            fillColor: '#eaeaea',
            color: '#000',
            alignment: 'center',
            bold: true,
            fontSize: 10
          };

          doc.defaultStyle.fontSize = 9;
          doc.styles.tableBody = { alignment: 'center', fontSize: 9 };

          doc.footer = function (currentPage, pageCount) {
            return {
              columns: [
                { text: 'Dicetak: ' + waktuCetak, alignment: 'left', margin: [40, 0] },
                { text: '© 2025 Telkom University – Document Management System', alignment: 'center' },
                { text: 'Halaman ' + currentPage.toString() + ' dari ' + pageCount, alignment: 'right', margin: [0, 0, 40, 0] }
              ],
              fontSize: 8,
              margin: [0, 10, 0, 0]
            };
          };

          doc.pageMargins = [40, 60, 40, 60];

          if (doc.content[1] && doc.content[1].table) {
            doc.content[1].table.widths = ['8%', '15%', '20%', '20%', '15%', '15%', '7%'];
            doc.content[1].margin = [0, 0, 0, 0];
            doc.content[1].layout = {
              hLineWidth: () => 0.5,
              vLineWidth: () => 0.5,
              hLineColor: () => '#000000',
              vLineColor: () => '#000000',
              paddingLeft: () => 5,
              paddingRight: () => 5,
              paddingTop: () => 3,
              paddingBottom: () => 3
            };
          }

          doc.content.push({
            text: '* Dokumen ini berisi daftar pengguna aktif dalam sistem.',
            alignment: 'left',
            italics: true,
            fontSize: 8,
            margin: [0, 15, 0, 0]
          });
        }
      }
    ]
  });

  // Edit User Modal Setup
  $(document).on('click', '.edit-user', function () {
    const userId = $(this).data('id');
    const employeeId = $(this).data('employee');
    const fullname = $(this).data('fullname');
    const parentId = $(this).data('directorate');
    const unitId = $(this).data('unit');
    const roleName = $(this).data('role');

    // Set values in modal
    $('#editUserId').val(userId); // Store user ID
    $('#editEmployeeId').val(employeeId);
    $('#editUsername').val($(this).data('username'));
    $('#editFullname').val(fullname);
    $('#editDirectorate').val(parentId).trigger('change');

    // Filter units based on selected directorate
    $('#editUnit option').each(function () {
      const optionParent = $(this).data('parent');
      if (optionParent == parentId || $(this).val() === '') {
        $(this).show();
      } else {
        $(this).hide();
      }
    });

    $('#editUnit').val(unitId);
    $('#editRole').val(roleName);
  });

  // Handle Edit Form Submission
  $('#editUserForm').submit(function (e) {
    e.preventDefault();

    if (!$('#editDirectorate').val() || !$('#editUnit').val() || !$('#editRole').val()) {
      Swal.fire('Peringatan', 'Semua field harus diisi!', 'warning');
      return;
    }

    $.ajax({
      url: '<?= base_url('create-user/update') ?>', // Fixed URL
      method: 'POST',
      data: {
        <?= csrf_token() ?>: '<?= csrf_hash() ?>',
        id: $('#editUserId').val(), // Use hidden user ID
        username: $('#editUsername').val(),
        fullname: $('#editFullname').val(),
        role: $('#editRole').val(),
        fakultas: $('#editDirectorate').val(),
        unit: $('#editUnit').val(),
        status: 1
      },
      success: function (res) {
        $('#editUserModal').modal('hide');
        Swal.fire('Berhasil!', res.message, 'success').then(() => {
          location.reload();
        });
      },
      error: function (xhr) {
        const err = xhr.responseJSON?.error || 'Terjadi kesalahan saat update.';
        Swal.fire('Gagal', err, 'error');
      }
    });
  });

  // Handle Directorate Change
  $('#editDirectorate').on('change', function () {
    const selectedParentId = $(this).val();
    $('#editUnit').val('');
    $('#editUnit option').each(function () {
      const optionParent = $(this).data('parent');
      if (optionParent == selectedParentId || $(this).val() === '') {
        $(this).show();
      } else {
        $(this).hide();
      }
    });
  });

  // Handle Delete User
  $(document).on('click', '.delete-user', function (e) {
    e.preventDefault();
    const id = $(this).data('id');

    Swal.fire({
      title: 'Yakin ingin menghapus?',
      text: 'User akan dihapus secara permanen.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, hapus',
      cancelButtonText: 'Batal',
      confirmButtonColor: '#dc3545',
      cancelButtonColor: '#6c757d'
    }).then((result) => {
      if (result.isConfirmed) {
        $.post('<?= base_url('create-user/delete') ?>', { // Fixed URL
          <?= csrf_token() ?>: '<?= csrf_hash() ?>',
          id: id
        })
        .done(function (res) {
          Swal.fire('Berhasil!', res.message, 'success').then(() => {
            location.reload();
          });
        })
        .fail(function (xhr) {
          const err = xhr.responseJSON?.error || 'Gagal menghapus user.';
          Swal.fire('Gagal', err, 'error');
        });
      }
    });
  });
});
</script>
<?= $this->endSection() ?>
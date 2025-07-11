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

<!-- Modal Edit -->
<div class="modal fade" id="editUserModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow">
      <div class="modal-header">
        <h5 class="modal-title">Edit User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="editUserForm">
        <div class="modal-body">
          <div class="mb-3">
            <label for="editEmployeeId">Employee ID</label>
            <input type="text" class="form-control" id="editEmployeeId" readonly>
          </div>
          <div class="mb-3">
            <label for="editDirectorate">Fakultas/Direktorat</label>
            <input type="text" class="form-control" id="editDirectorate" readonly>
          </div>
          <div class="mb-3">
            <label for="editUnit">Unit</label>
            <input type="text" class="form-control" id="editUnit" readonly>
          </div>
          <div class="mb-3">
            <label for="editFullname">Fullname</label>
            <input type="text" class="form-control" id="editFullname">
          </div>
          <div class="mb-3">
            <label for="editRole">Role</label>
            <input type="text" class="form-control" id="editRole">
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
      { orderable: false, targets: 6 },
      { className: 'text-center', targets: 6 }
    ],
    buttons: [
      {
        extend: 'excel',
        className: 'btn btn-outline-success btn-sm',
        title: 'Data_Users',
        exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
      },
      {
        extend: 'pdfHtml5',
        text: 'PDF',
        className: 'btn',
        title: 'Data Users',
        filename: 'data_users',
        exportOptions: { columns: [0, 1, 2, 3, 4, 5] },
        orientation: 'portrait', 
        pageSize: 'A4',
        customize: function (doc) {
          const now = new Date();
          const waktuCetak = now.toLocaleString('id-ID', {
            day: '2-digit', month: '2-digit', year: 'numeric',
            hour: '2-digit', minute: '2-digit'
          });

          // Hapus judul default jika ada
          if (doc.content[0].text === 'Data Users') {
            doc.content.splice(0, 1);
          }

          // Tambahkan judul
          doc.content.unshift({
            text: 'Data Users',
            alignment: 'center',
            bold: true,
            fontSize: 16,
            margin: [0, 0, 0, 10]
          });

          // Header tabel abu-abu terang
          doc.styles.tableHeader = {
            fillColor: '#eaeaea',
            color: '#000',
            alignment: 'center',
            bold: true,
            fontSize: 9
          };

          // Warna isi tabel putih tanpa selang-seling
          doc.styles.tableBodyEven = { fillColor: '#ffffff' };
          doc.styles.tableBodyOdd = { fillColor: '#ffffff' };

          // Ukuran & padding
          doc.defaultStyle.fontSize = 8;
          doc.styles.tableBody = { alignment: 'left', fontSize: 8 };

          // Footer halaman
          doc.footer = function (currentPage, pageCount) {
            return {
              columns: [
                { text: waktuCetak, alignment: 'left', margin: [30, 0] },
                { text: '© 2025 Telkom University – Document Management System', alignment: 'center' },
                { text: currentPage.toString() + '/' + pageCount, alignment: 'right', margin: [0, 0, 30, 0] }
              ],
              fontSize: 9
            };
          };

          // Set margin yang seimbang untuk halaman portrait
          doc.pageMargins = [40, 40, 40, 40]; // [left, top, right, bottom] - lebih kecil untuk ruang lebih

          // Atur lebar kolom tabel agar muat dalam portrait A4
          if (doc.content[1] && doc.content[1].table) {
            doc.content[1].table.widths = ['6%', '12%', '28%', '18%', '21%', '15%']; // Total 100%, lebih compact
            doc.content[1].margin = [0, 0, 0, 0];
          }

          // Tabel: garis dan padding
          doc.content[doc.content.length - 1].layout = {
            hLineWidth: function () { return 0.5; },
            vLineWidth: function () { return 0.5; },
            hLineColor: function () { return '#000000'; },
            vLineColor: function () { return '#000000'; },
            paddingLeft: function () { return 3; },
            paddingRight: function () { return 3; },
            paddingTop: function () { return 2; },
            paddingBottom: function () { return 2; }
          };

          // Catatan akhir
          doc.content.push({
            text: '* Dokumen ini berisi daftar pengguna aktif dalam sistem.',
            alignment: 'left',
            italics: true,
            fontSize: 8,
            margin: [0, 12, 0, 0]
          });
        }
      }
    ]
  });

  $(document).on('click', '.edit-user', function () {
    $('#editEmployeeId').val($(this).data('employee'));
    $('#editDirectorate').val($(this).data('directorate'));
    $('#editUnit').val($(this).data('unit'));
    $('#editFullname').val($(this).data('fullname'));
    $('#editRole').val($(this).data('role'));
  });

  $('#editUserForm').submit(function (e) {
    e.preventDefault();
    $.ajax({
      url: '<?= base_url('CreateUser/update') ?>',
      method: 'POST',
      data: {
        id: $('#editEmployeeId').val(),
        fullname: $('#editFullname').val(),
        role: $('#editRole').val(),
        employee: $('#editEmployeeId').val(),
        fakultas: $('#editDirectorate').val(),
        unit: $('#editUnit').val(),
        status: 1
      },
      success: function (res) {
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

  $(document).on('click', '.delete-user', function (e) {
    e.preventDefault();
    const id = $(this).data('id');

    Swal.fire({
      title: 'Yakin ingin menghapus?',
      text: 'User akan dihapus secara permanen.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, hapus',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: '<?= base_url('CreateUser/delete/') ?>' + id,
          method: 'DELETE',
          success: function (res) {
            Swal.fire('Berhasil!', res.message, 'success').then(() => {
              location.reload();
            });
          },
          error: function (xhr) {
            const err = xhr.responseJSON?.error || 'Gagal menghapus user.';
            Swal.fire('Gagal', err, 'error');
          }
        });
      }
    });
  });
});
</script>
<?= $this->endSection() ?>
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
                        <!-- DELETE -->
                        <a href="#"
                          class="text-danger me-2 delete-user"
                          data-id="<?= $user['id'] ?>"
                          title="Delete">
                            <i class="bi bi-trash"></i>
                        </a>

                        <!-- EDIT -->
                        <a href="#"
                          class="edit-user"
                          data-id="<?= $user['id'] ?>"
                          data-employee="<?= $user['username'] ?>"
                          data-parent="<?= $user['parent_id'] ?>"   
                          data-unitid="<?= $user['unit_id'] ?>"     
                          data-fullname="<?= esc($user['fullname']) ?>"
                          data-role="<?= esc($user['role_name'] ?? '') ?>"
                          data-bs-toggle="modal"                 
                          data-bs-target="#editUserModal"         
                          title="Edit">
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
        <!-- ==== hidden ID user ==== -->
        <input type="hidden" id="editUserId" name="id">
        <div class="modal-body">
          <div class="mb-3">
            <label for="editEmployeeId" class="form-label">Employee ID</label>
            <input type="text" class="form-control" id="editEmployeeId" name="employee" readonly>
          </div>

          <!-- <div class="mb-3">
            <label class="form-label" for="editDirectorate">Fakultas/Direktorat</label>
            <select id="editDirectorate" name="fakultas" class="form-select">
                <option value="">Pilih Fakultas...</option>
                <?php foreach ($unitParents as $p): ?>
                    <option value="<?= esc($p['name']) ?>" data-id="<?= $p['id'] ?>">
                        <?= esc($p['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

          </div> -->

          <div class="mb-3">
            <label class="form-label" for="editDirectorate">Fakultas/Direktorat</label>
            <select id="editDirectorate" name="fakultas" class="form-select" required>
                <option value="">Pilih Fakultas...</option>
                <?php foreach ($unitParents as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= esc($p['name']) ?></option>
                <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label" for="editUnit">Unit</label>
            <select id="editUnit" name="unit" class="form-select" required>
                <option value="">Pilih Unit...</option>
            </select>
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
$(function () {

  /* ────── 1. Inisialisasi DataTables ────── */
  const dt = $('#userTable').DataTable({
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
          { extend:'copyHtml5',  text:'Copy',  className:'btn' },
          { extend:'csvHtml5',   text:'CSV',   className:'btn' },
          { extend:'excelHtml5', text:'Excel', className:'btn' },
          { extend:'pdfHtml5',   text:'PDF',   className:'btn' },
          { extend:'print',      text:'Print', className:'btn' }
      ]
  });
  dt.buttons().container().appendTo('.export-buttons');

  /* ========== fungsi isi dropdown Unit ========== */
  function loadUnits(parentId, selectedId=''){
    if(!parentId){ $('#editUnit').html('<option value="">Pilih Unit...</option>'); return; }
    $.getJSON(`/CreateUser/getUnits/${parentId}`, rows=>{
      let html='<option value="">Pilih Unit...</option>';
      rows.forEach(u=>{
        html+=`<option value="${u.id}" ${u.id==selectedId?'selected':''}>${u.name}</option>`;
      });
      $('#editUnit').html(html);
    });
  }




  /* ========== klik ikon ✏️ ========== */
  $(document).on('click','.edit-user',function(){
    const parentId = $(this).data('parent');
    const unitId   = $(this).data('unitid');

    $('#editUserId').val($(this).data('id'));
    $('#editEmployeeId').val($(this).data('employee'));
    $('#editFullname').val($(this).data('fullname'));
    $('#editRole').val($(this).data('role'));

    $('#editDirectorate').val(parentId);
    loadUnits(parentId, unitId);
  });

  /* ganti Fakultas → muat Unit baru */
  $('#editDirectorate').on('change',function(){
    loadUnits(this.value);
  });

  /* ────── 4. Submit formulir Edit ────── */
  $('#editUserForm').on('submit',function(e){
    e.preventDefault();
    $.post('/CreateUser/update',$(this).serialize())
    .done(()=>{ $('#editUserModal').modal('hide'); location.reload(); })
    .fail(xhr=>alert(xhr.responseJSON?.error || 'Gagal mengupdate user'));
  });

  /* ────── 5. Klik ikon 🗑 Delete ────── */
  $(document).on('click', '.delete-user', function (e) {
      e.preventDefault();
      const id = $(this).data('id');
      if (!confirm('Apakah Anda yakin ingin menghapus user ini?')) return;

      $.ajax({
          url: `/CreateUser/delete/${id}`,
          method: 'DELETE',
          data: { '<?= csrf_token() ?>':'<?= csrf_hash() ?>' }
      })
      .done(() => location.reload())
      .fail(xhr => alert('Gagal menghapus user: ' + xhr.responseText));
  });

});
</script>
<?= $this->endSection() ?>

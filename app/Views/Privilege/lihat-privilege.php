<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="px-4 py-3">
    <h4>Lihat Privilege</h4>
    <hr>

    <div class="table-responsive bg-white p-3 rounded shadow-sm">
        <table class="table table-bordered table-hover align-middle" id="privilegeTable">
            <thead class="table-light">
                <tr>
                    <th class="col-no text-center">No</th>
                    <th class="col-role">Role</th>
                    <th class="col-submenu">Sub Menu</th>
                    <th class="col-privilege">Privilege</th>
                    <th class="col-action text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($privileges as $i => $p): ?>
                <tr>
                    <td class="text-center"><?= $i + 1 ?></td>
                    <td><?= esc($p['role']) ?></td>
                    <td><?= esc(implode(', ', $p['submenu'])) ?></td>
                    <td>
                        <?php foreach ($p['actions'] as $action): ?>
                            <?php
                                $badgeClass = match($action) {
                                    'create' => 'bg-success text-white',
                                    'read'   => 'bg-primary text-white',
                                    'update' => 'bg-warning text-white',
                                    'delete' => 'bg-danger text-white',
                                    default  => 'bg-secondary text-white'
                                };
                            ?>
                            <span class="privilege-badge <?= $badgeClass ?>"><?= ucfirst($action) ?></span>
                        <?php endforeach ?>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-link text-primary p-0"
                            data-bs-toggle="modal"
                            data-bs-target="#editModal"
                            onclick="openEditModal('<?= esc($p['role']) ?>', <?= json_encode($p['submenu']) ?>, <?= json_encode($p['actions']) ?>)">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <button class="btn btn-sm btn-link text-danger p-0" onclick="confirmDelete()">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content shadow border-0">
      <div class="modal-header">
        <h5 class="modal-title">Edit Privilege</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editPrivilegeForm">
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Role</label>
                <p id="editRoleText" class="form-control-plaintext fw-semibold mb-0"></p>
                <input type="hidden" id="editRole" name="role">
            </div>
            <div class="mb-3">
                <label class="form-label">Sub Menu</label>
                <select class="form-select" id="editSubmenu" multiple="multiple" required>
                    <option value="" disabled>Pilih atau ketik submenu...</option>
                    <option value="Tambah Users">Tambah Users</option>
                    <option value="Lihat Users">Lihat Users</option>
                    <option value="Edit Users">Edit Users</option>
                    <option value="Hapus Users">Hapus Users</option>
                    <option value="Lihat Dokumen">Lihat Dokumen</option>
                    <option value="Tambah Dokumen">Tambah Dokumen</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Privilege</label>
                <div class="d-flex flex-wrap gap-3 ps-2">
                    <label><input type="checkbox" name="privileges[]" value="create"> Create</label>
                    <label><input type="checkbox" name="privileges[]" value="read"> Read</label>
                    <label><input type="checkbox" name="privileges[]" value="update"> Update</label>
                    <label><input type="checkbox" name="privileges[]" value="delete"> Delete</label>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary w-100">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<script>
    $(document).ready(function () {
        $('#privilegeTable').DataTable();
        $('#editSubmenu').select2({
            width: '100%',
            tags: true,
            placeholder: "Pilih atau ketik submenu...",
            dropdownParent: $('#editModal')
        });
    });

    function openEditModal(role, submenuList, privileges) {
        $('#editRoleText').text(role ?? '-');
        $('#editRole').val(role);
        $('#editSubmenu').val(submenuList).trigger('change');

        $('input[name="privileges[]"]').prop('checked', false);
        privileges.forEach(p => {
            $(`input[name="privileges[]"][value="${p}"]`).prop('checked', true);
        });
    }

    function confirmDelete() {
        Swal.fire({
            title: 'Yakin ingin menghapus privilege ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('Dihapus!', 'Data berhasil dihapus (simulasi).', 'success');
            }
        });
    }
    function openEditModal(role, submenuList, privileges) {
    console.log("ROLE:", role);
    console.log("SUBMENU:", submenuList);
    console.log("PRIVILEGES:", privileges);
    
    $('#editRoleText').text(role ?? '-');
    $('#editRole').val(role);
    $('#editSubmenu').val(submenuList).trigger('change');

    $('input[name="privileges[]"]').prop('checked', false);
    privileges.forEach(p => {
        $(`input[name="privileges[]"][value="${p}"]`).prop('checked', true);
    });
}

</script>

<?= $this->endSection() ?>

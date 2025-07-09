<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="px-4 py-3 w-100">
    <h4>Lihat Sub Menu</h4>
    <hr>

    <div class="table-responsive shadow-sm rounded bg-white p-3">
        <table class="table table-bordered table-hover align-middle" id="submenuTable">
            <thead class="table-light">
                <tr>
                    <th class="text-center" style="width: 5%;">No</th>
                    <th style="width: 30%;">Menu</th>
                    <th style="width: 45%;">Sub Menu</th>
                    <th class="text-center" style="width: 20%;">Aksi</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <!-- Dummy Data -->
                <tr>
                    <td class="text-center">1</td>
                    <td>Create User</td>
                    <td>Tambah Users</td>
                    <td class="text-center">
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <form action="<?= site_url('sub-menu/delete/1') ?>" method="post" onsubmit="return confirmDelete(event, this);">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-link p-0 text-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            <button class="btn btn-link p-0 text-primary" data-bs-toggle="modal" data-bs-target="#editModal"
                                onclick="openEditModal(1, 'Create User', 'Tambah User')">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- No Results -->
    <div id="noResults" class="text-center py-4" style="display: none;">
        <i class="bi bi-search" style="font-size: 3rem; color: #6c757d;"></i>
        <h5 class="mt-3 text-muted">No results found</h5>
        <p class="text-muted">Try adjusting your search criteria</p>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content shadow border-0">
      <div class="modal-header">
        <h5 class="modal-title">Edit Sub Menu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" id="editUnitForm">
        <?= csrf_field() ?>
        <div class="modal-body">
            <input type="hidden" name="id" id="editUnitId">
            <div class="mb-3">
                <label class="form-label">Fakultas/Direktorat</label>
                <input type="text" name="parent_name" id="editParentName" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Sub Menu</label>
                <input type="text" name="unit_name" id="editUnitName" class="form-control" required>
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

<script>
    function confirmDelete(event, form) {
    event.preventDefault();
    Swal.fire({
        title: 'Yakin ingin menghapus?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
    return false;
}

    $(document).ready(function () {
        $('#submenuTable').DataTable(); // tanpa tombol export
    });

    function openEditModal(id, parentName, unitName) {
        const form = document.getElementById('editUnitForm');
        form.action = `#`; // dummy action
        document.getElementById('editUnitId').value = id;
        document.getElementById('editParentName').value = parentName;
        document.getElementById('editUnitName').value = unitName;
    }
</script>

<?= $this->endSection() ?>

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
                <?php if (!empty($submenus)) : ?>
                    <?php $no = 1; foreach ($submenus as $submenu) : ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td><?= esc($submenu['parent_name']) ?></td>
                            <td><?= esc($submenu['name']) ?></td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <form action="<?= site_url('submenu/delete/' . $submenu['id']) ?>" method="post" onsubmit="return confirmDelete(event, this);">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-link p-0 text-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    <button class="btn btn-link p-0 text-primary" data-bs-toggle="modal" data-bs-target="#editModal"
                                        onclick="openEditModal(<?= $submenu['id'] ?>, <?= esc($submenu['parent']) ?>, '<?= esc($submenu['name']) ?>')">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach ?>
                <?php else : ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted">Tidak ada data submenu.</td>
                    </tr>
                <?php endif ?>
            </tbody>
        </table>
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
                <label class="form-label">Menu</label>
                <select name="parent" id="editParentName" class="form-select" required>
                    <option value="">-- Pilih Menu --</option>
                    <?php foreach ($menus as $menu) : ?>
                        <option value="<?= $menu['id'] ?>"><?= esc($menu['name']) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Sub Menu</label>
                <input type="text" name="submenu" id="editUnitName" class="form-control" required>
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
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
            form.submit(); // Submit normal kalau user konfirmasi
            }
        });
        return false;
        }

    function openEditModal(id, parentId, submenuName) {
        const form = document.getElementById('editUnitForm');
        if (!form) return;
        form.action = `/submenu/update/${id}`;
        document.getElementById('editUnitId').value = id;
        document.getElementById('editParentName').value = parentId;
        document.getElementById('editUnitName').value = submenuName;
    }

    $(document).ready(function () {
        const table = $('#submenuTable');
        const thCount = table.find('thead th').length;
        const tdCount = table.find('tbody tr:visible:first td').length;

        // Inisialisasi DataTables jika kolom match
        if (thCount === tdCount || table.find('tbody tr').length === 0) {
            table.DataTable();
        } else {
            console.warn('⚠️ DataTables tidak dijalankan karena jumlah kolom tidak sesuai.');
        }
    });
</script>

<?= $this->endSection() ?>

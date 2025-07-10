<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="px-4 py-3 w-100">
    <h4>Daftar Menu</h4>
    <hr>

    <div class="table-responsive shadow-sm rounded bg-white p-3">
        <table class="table table-bordered table-hover align-middle" id="menuTable">
            <thead class="table-light">
                <tr>
                    <th class="text-center" style="width: 5%;">No</th>
                    <th style="width: 30%;">Nama Menu</th>
                    <th style="width: 30%;">Icon</th>
                    <th class="text-center" style="width: 10%;">Status</th>
                    <th class="text-center" style="width: 25%;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($menus as $menu): ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td><?= esc($menu['name']) ?></td>
                        <td><?= esc($menu['icon']) ?></td>
                        <td class="text-center">
                            <span class="badge <?= $menu['status'] == 1 ? 'bg-success' : 'bg-secondary' ?>">
                                <?= $menu['status'] == 1 ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <form action="<?= site_url('Menu/delete/' . $menu['id']) ?>" method="post" onsubmit="return confirmDelete(event, this);">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-link p-0 text-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                <button class="btn btn-link p-0 text-primary" data-bs-toggle="modal" data-bs-target="#editModal"
                                    onclick="openEditModal(
                                        <?= $menu['id'] ?>, 
                                        '<?= esc($menu['name'], 'js') ?>', 
                                        '<?= esc($menu['icon'], 'js') ?>', 
                                        <?= $menu['status'] ?>
                                    )">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Edit Menu -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content shadow border-0">
      <div class="modal-header">
        <h5 class="modal-title">Edit Menu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" id="editMenuForm">
        <?= csrf_field() ?>
        <div class="modal-body">
            <input type="hidden" name="id" id="editMenuId">
            <div class="mb-3">
                <label class="form-label">Nama Menu</label>
                <input type="text" name="menu_name" id="editMenuName" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Icon</label>
                <input type="text" name="icon" id="editMenuIcon" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label d-block">Status</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="editStatusActive" value="1">
                    <label class="form-check-label" for="editStatusActive">Active</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="editStatusInactive" value="0">
                    <label class="form-check-label" for="editStatusInactive">Inactive</label>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function () {
        $('#menuTable').DataTable();
    });

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

    function openEditModal(id, name, icon, status) {
        const form = document.getElementById('editMenuForm');
        form.action = `<?= site_url('Menu/update') ?>/${id}`;
        document.getElementById('editMenuId').value = id;
        document.getElementById('editMenuName').value = name;
        document.getElementById('editMenuIcon').value = icon;
        document.getElementById('editStatusActive').checked = status == 1;
        document.getElementById('editStatusInactive').checked = status == 0;
    }
</script>

<script>
    <?php if (session()->getFlashdata('success')): ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '<?= session()->getFlashdata('success') ?>',
            confirmButtonText: 'OK',
            confirmButtonColor: '#6C63FF'
        });
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: '<?= session()->getFlashdata('error') ?>',
            confirmButtonText: 'OK',
            confirmButtonColor: '#6C63FF'
        });
    <?php endif; ?>
</script>


<?= $this->endSection() ?>

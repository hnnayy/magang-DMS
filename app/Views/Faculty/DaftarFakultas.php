<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="px-4 py-3 w-100">
    <h4>Daftar Fakultas</h4>
    <hr>

    <div class="table-responsive shadow-sm rounded bg-white p-3">
        <table class="table table-bordered table-hover align-middle" id="fakultasTable">
            <thead class="table-light">
                <tr>
                    <th class="text-center">No</th>
                    <th>Nama Fakultas</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($unitParent as $fakultas): ?>
                    <!-- Cek jika status fakultas aktif (status != 0) -->
                    <?php if ($fakultas['status'] != 0): ?>
                        <tr>
                            <td class="text-center"><?= $i++ ?></td>
                            <td><?= esc($fakultas['name']) ?></td>
                            <td><?= $fakultas['type'] == 1 ? 'Directorate' : 'Faculty' ?></td>
                            <td>
                                <?= $fakultas['status'] == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <!-- Form Hapus -->
                                    <form action="<?= site_url('data-master/fakultas/delete/' . $fakultas['id']) ?>" method="post" onsubmit="return confirmDelete(event, this);">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-link p-0 text-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    <button 
                                        class="btn btn-link p-0 text-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editModal"
                                        onclick="openEditModal(
                                            <?= $fakultas['id'] ?>, 
                                            '<?= esc($fakultas['name']) ?>', 
                                            '<?= esc($fakultas['type']) ?>', 
                                            '<?= esc($fakultas['status']) ?>'
                                        )">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Edit Fakultas -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content shadow border-0">
      <div class="modal-header">
        <h5 class="modal-title">Edit Fakultas</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" id="editFakultasForm">
        <?= csrf_field() ?>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Nama Fakultas</label>
                <input type="text" name="name" id="editFakultasName" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label d-block">Level</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="type" id="editType1" value="1">
                    <label class="form-check-label" for="editType1">Directorate</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="type" id="editType2" value="2">
                    <label class="form-check-label" for="editType2">Faculty</label>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label d-block">Status</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="editStatus1" value="1">
                    <label class="form-check-label" for="editStatus1">Aktif</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="editStatus2" value="2">
                    <label class="form-check-label" for="editStatus2">Nonaktif</label>
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


<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
    }

    $(document).ready(function () {
        $('#fakultasTable').DataTable();
    });

    function openEditModal(id, name, type, status) {
        const form = document.getElementById('editFakultasForm');
        form.action = `<?= site_url('data-master/fakultas/update/') ?>${id}`;
        document.getElementById('editFakultasName').value = name;
        document.getElementById('editFakultasType').value = type;
        document.getElementById('editFakultasStatus').value = status;
    }
</script>

<?php if (session()->getFlashdata('success')) : ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '<?= session()->getFlashdata('success') ?>',
        showConfirmButton: false,
        timer: 2000
    });
</script>
<?php endif; ?>
<?= $this->endSection() ?>

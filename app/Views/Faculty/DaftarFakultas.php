<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="px-4 py-3 w-100">
    <h4>Faculty list</h4>
    <hr>

    <div class="table-responsive shadow-sm rounded bg-white p-3">
        <table class="table table-bordered table-hover align-middle" id="fakultasTable">
            <thead class="table-light">
                <tr>
                    <th class="text-center">No</th>
                    <th>Name Faculty</th>
                    <th>Level</th>
                    <th>Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($unitParent as $fakultas): ?>
                    <?php if ($fakultas['status'] != 0): ?>
                        <tr>
                            <td class="text-center"><?= $i++ ?></td>
                            <td><?= esc($fakultas['name']) ?></td>
                            <td><?= $fakultas['type'] == 1 ? 'Directorate' : 'Faculty' ?></td>
                            <td>
                                <?= $fakultas['status'] == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <!-- Tombol Hapus -->
                                    <form action="<?= site_url('data-master/fakultas/delete/' . $fakultas['id']) ?>" method="post" onsubmit="return confirmDelete(event, this);">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-link p-0 text-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    
                                    <!-- Tombol Edit -->
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
        <h5 class="modal-title">Edit Faculty</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" id="editFakultasForm">
        <?= csrf_field() ?>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Faculty Name</label>
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
                    <label class="form-check-label" for="editStatus1">Active</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="editStatus2" value="2">
                    <label class="form-check-label" for="editStatus2">Inactive</label>
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
<!-- JS Libraries -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Script -->
<script>
    // Konfirmasi sebelum hapus
    function confirmDelete(event, form) {
        event.preventDefault();
        Swal.fire({
            title: 'Are you sure you want to delete?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete!',
            cancelButtonText: 'Cancalled'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }

    // Inisialisasi DataTable
    $(document).ready(function () {
        $('#fakultasTable').DataTable();
    });

    // Buka modal edit dan isi data
    function openEditModal(id, name, type, status) {
        const form = document.getElementById('editFakultasForm');
        form.action = `<?= site_url('data-master/fakultas/update/') ?>${id}`;

        document.getElementById('editFakultasName').value = name;

        // Centang radio "type" (1 = Directorate, 2 = Faculty)
        const typeRadio = document.querySelector(`input[name="type"][value="${type}"]`);
        if (typeRadio) typeRadio.checked = true;

        // Centang radio "status" (1 = Active, 2 = Inactive)
        const statusRadio = document.querySelector(`input[name="status"][value="${status}"]`);
        if (statusRadio) statusRadio.checked = true;
    }
</script>

<!-- Notifikasi jika berhasil -->
<?php if (session()->getFlashdata('success')) : ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'success!',
        text: '<?= session()->getFlashdata('success') ?>',
        showConfirmButton: false,
        timer: 2000
    });
</script>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="px-4 py-3 w-100">
    <h4>Lihat Fakultas</h4>
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
                <?php foreach ($unitParent as $fakultas): ?>
                    <tr data-id="<?= $fakultas['id'] ?>">
                        <td class="text-center">#</td>
                        <td><?= esc($fakultas['name']) ?></td>
                        <td><?= $fakultas['type'] == 1 ? 'Directorate' : 'Faculty' ?></td>
                        <td>
                            <?= $fakultas['status'] == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?>
                        </td>
                        <td class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <form class="delete-fakultas-form" data-id="<?= $fakultas['id'] ?>">
                                    <?= csrf_field() ?>
                                    <button type="button" class="btn btn-link p-0 text-danger delete-fakultas-btn">
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
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content shadow border-0">
      <div class="modal-header">
        <h5 class="modal-title">Edit Fakultas</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editFakultasForm">
        <?= csrf_field() ?>
        <div class="modal-body">
            <input type="hidden" name="id" id="editFakultasId">
            <div class="mb-3">
                <label class="form-label">Nama Fakultas</label>
                <input type="text" name="name" id="editFakultasName" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Level</label>
                <select name="type" id="editFakultasType" class="form-select" required>
                    <option value="1">Directorate</option>
                    <option value="2">Faculty</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" id="editFakultasStatus" class="form-select" required>
                    <option value="1">Active</option>
                    <option value="2">Inactive</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary w-100" type="submit">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function () {
        let table = $('#fakultasTable').DataTable({
            columnDefs: [{ targets: 0, orderable: false }],
            order: [],
            drawCallback: function (settings) {
                let api = this.api();
                api.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1;
                });
            }
        });

        // Delete Fakultas AJAX
        $(document).on('click', '.delete-fakultas-btn', function () {
            const form = $(this).closest('.delete-fakultas-form');
            const id = form.data('id');
            const csrfToken = form.find('[name=<?= csrf_token() ?>]').val();

            Swal.fire({
                title: 'Yakin ingin menghapus?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `<?= site_url('fakultas/delete/') ?>${id}`,
                        type: 'POST',
                        data: { '<?= csrf_token() ?>': csrfToken },
                        success: function (response) {
                            if (response.success) {
                                table.row(form.closest('tr')).remove().draw();
                                Swal.fire('Berhasil!', response.message, 'success');
                            } else {
                                Swal.fire('Gagal', response.message, 'error');
                            }
                        },
                        error: function () {
                            Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus.', 'error');
                        }
                    });
                }
            });
        });

        // Update Fakultas AJAX
        $('#editFakultasForm').submit(function (e) {
            e.preventDefault();
            const form = $(this);
            const id = $('#editFakultasId').val();
            const formData = form.serialize();

            $.ajax({
                url: `<?= site_url('fakultas/update/') ?>${id}`,
                type: 'POST',
                data: formData,
                success: function (response) {
                    if (response.success) {
                        Swal.fire('Berhasil!', response.message, 'success')
                            .then(() => location.reload());
                    } else {
                        Swal.fire('Gagal', response.message, 'error');
                    }
                },
                error: function () {
                    Swal.fire('Gagal', 'Terjadi kesalahan saat mengupdate.', 'error');
                }
            });
        });
    });

    function openEditModal(id, name, type, status) {
        $('#editFakultasForm').attr('action', `<?= site_url('fakultas/update/') ?>${id}`);
        $('#editFakultasId').val(id);
        $('#editFakultasName').val(name);
        $('#editFakultasType').val(type);
        $('#editFakultasStatus').val(status);
    }
</script>

<?= $this->endSection() ?>

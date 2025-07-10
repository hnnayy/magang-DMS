<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="px-4 py-3 w-100">
    <h4>Lihat Unit</h4>
    <hr>

    <!-- Table Section -->
    <div class="table-responsive shadow-sm rounded bg-white p-3">
        <table class="table table-bordered table-hover align-middle" id="documentTable">
            <thead class="table-light">
                <tr>
                    <th class="text-center" style="width: 5%;">No</th>
                    <th style="width: 30%;">Fakultas/Direktorat</th>
                    <th style="width: 45%;">Bagian/Unit/Prodi</th>
                    <th class="text-center" style="width: 20%;">Aksi</th>
                </tr>
            </thead>
            <tbody id="tableBody">
            <?php if (! empty($units)) : ?>
                <?php foreach ($units as $index => $row) : ?>
                <tr>
                    <td class="text-center"><?= $index + 1 ?></td>
                    <td><?= esc($row['parent_name']) ?></td>
                    <td><?= esc($row['name']) ?></td>
                    <td class="text-center">
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <!-- DELETE -->
                            <form action="<?= site_url('data-master/unit/' . $row['id'] . '/delete') ?>" method="post" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-link p-0" onclick="SwalConfirmDelete(this)">
                                    <i class="bi bi-trash text-danger"></i>
                                </button>
                            </form>
                            <!-- EDIT -->
                            <button class="btn btn-link p-0 text-primary" data-bs-toggle="modal" data-bs-target="#editModal" 
                                onclick="openEditModal(<?= $row['id'] ?>, '<?= esc($row['parent_name']) ?>', '<?= esc($row['name']) ?>')">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td class="text-center">-</td>
                    <td>-</td>
                    <td>-</td>
                    <td class="text-center text-muted">Belum ada data</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content shadow border-0">
      <div class="modal-header">
        <h5 class="modal-title">Edit Unit</h5>
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
                <label class="form-label">Unit</label>
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

<!-- JS Modal & Delete -->
<script>
function openEditModal(id, parentName, unitName) {
    const form = document.getElementById('editUnitForm');
    form.action = `<?= site_url('data-master/unit') ?>/${id}/update`;
    document.getElementById('editUnitId').value = id;
    document.getElementById('editParentName').value = parentName;
    document.getElementById('editUnitName').value = unitName;
}

function SwalConfirmDelete(elem) {
    event.preventDefault();
    Swal.fire({
        title: 'Hapus unit ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            elem.closest('form').submit();
        }
    });
}
</script>

<!-- DataTables + jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(function () {
    const dt = $('#documentTable').DataTable({
        dom: '<"row mb-3"<"col-md-6 d-flex gap-2 export-buttons"B><"col-md-6 text-end"f>>' +
             'rt' +
             '<"row mt-3"<"col-md-6"l><"col-md-6 text-end"p>>',
        pageLength: 10,
        order: [],
        columnDefs: [{ orderable: false, targets: 3 }],
        language: {
            emptyTable: "Belum ada data"
        },
        buttons: [
            {
                text: 'Excel',
                className: "btn btn-success",
                action: function () {
                    window.location = '<?= site_url('data-master/export/excel') ?>';
                }
            },
            {
                text: 'PDF',
                className: "btn btn-danger",
                action: function () {
                    window.location = '<?= site_url('data-master/export/pdf') ?>';
                }
            }
        ]
    });
});
</script>

<?= $this->endSection() ?>

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
                    <?php 
                    // Check if user has any action privileges for this page
                    $privileges = session('privileges');
                    $canUpdate = isset($privileges['faculty-list']['can_update']) && $privileges['faculty-list']['can_update'] == 1;
                    $canDelete = isset($privileges['faculty-list']['can_delete']) && $privileges['faculty-list']['can_delete'] == 1;
                    
                    if ($canUpdate || $canDelete): ?>
                        <th class="text-center">Action</th>
                    <?php endif; ?>
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
                            
                            <?php if ($canUpdate || $canDelete): ?>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <?php if ($canDelete): ?>
                                        <!-- Tombol Hapus -->
                                        <form action="<?= site_url('create-faculty/delete') ?>" method="post" onsubmit="return confirmDelete(event, this);">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= $fakultas['id'] ?>">
                                            <button type="submit" class="btn btn-link p-0 text-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <?php if ($canUpdate): ?>
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
                                    <?php endif; ?>
                                </div>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Edit Fakultas - Only show if user has update privilege -->
<?php if ($canUpdate): ?>
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content shadow border-0">
      <div class="modal-header">
        <h5 class="modal-title">Edit Faculty</h5>
      </div>
      <form method="post" id="editFakultasForm" action="<?= site_url('create-faculty/update/') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="id" id="editFakultasId">
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

        <div class="modal-footer d-grid gap-2" style="grid-template-columns: 1fr 1fr;">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('script') ?>
<!-- JS Libraries -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Script -->
<script>
    // Check privileges from PHP session
    const canUpdate = <?= json_encode($canUpdate) ?>;
    const canDelete = <?= json_encode($canDelete) ?>;

    // Only define confirmDelete function if user has delete privilege
    <?php if ($canDelete): ?>
    function confirmDelete(event, form) {
        event.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: 'rgba(118, 125, 131, 1)',
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }
    <?php endif; ?>

    // Inisialisasi DataTable
    $(document).ready(function () {
        $('#fakultasTable').DataTable();
    });

    // Only define openEditModal function if user has update privilege
    <?php if ($canUpdate): ?>
    function openEditModal(id, name, type, status) {
        // Set ID di hidden input
        document.getElementById('editFakultasId').value = id;
        
        // Set nama fakultas
        document.getElementById('editFakultasName').value = name;

        // Centang radio "type" (1 = Directorate, 2 = Faculty)
        const typeRadio = document.querySelector(`input[name="type"][value="${type}"]`);
        if (typeRadio) typeRadio.checked = true;

        // Centang radio "status" (1 = Active, 2 = Inactive)
        const statusRadio = document.querySelector(`input[name="status"][value="${status}"]`);
        if (statusRadio) statusRadio.checked = true;
    }
    <?php endif; ?>

    // Show messages
    <?php if (session()->getFlashdata('added_message')): ?>
        Swal.fire({
            title: 'Success!',
            text: '<?= session()->getFlashdata('added_message') ?>',
            icon: 'success',
            confirmButtonText: 'OK'
        });
    <?php endif; ?>

    <?php if (session()->getFlashdata('updated_message')): ?>
        Swal.fire({
            title: 'Success!',
            text: '<?= session()->getFlashdata('updated_message') ?>',
            icon: 'success',
            confirmButtonText: 'OK'
        });
    <?php endif; ?>

    <?php if (session()->getFlashdata('deleted_message')): ?>
        Swal.fire({
            title: 'Success!',
            text: '<?= session()->getFlashdata('deleted_message') ?>',
            icon: 'success',
            confirmButtonText: 'OK'
        });
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        Swal.fire({
            title: 'Error!',
            text: '<?= session()->getFlashdata('error') ?>',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    <?php endif; ?>
</script>

<?= $this->endSection() ?>
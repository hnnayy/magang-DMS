<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="px-4 py-3 w-100">
    <h4>Role List</h4>
    <hr>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('deleted_message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('deleted_message') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('updated_message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('updated_message') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="table-responsive shadow-sm rounded bg-white p-3">
        <table class="table table-bordered table-hover align-middle" id="roleTable">
            <thead class="table-light">
                <tr>
                    <th class="text-center" style="width: 5%;">No</th>
                    <th>Role Name</th>
                    <th>Level</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th class="text-center" style="width: 20%;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($roles as $role): ?>
                <tr>
                    <td class="text-center"><?= $i++ ?></td>
                    <td><?= esc($role['name']) ?></td>
                    <td>
                        <?php if ($role['access_level'] == '1'): ?>
                            Directorate/Faculty
                        <?php elseif ($role['access_level'] == '2'): ?>
                            Unit
                        <?php else: ?>
                            <?= esc($role['access_level']) ?>
                        <?php endif; ?>
                    </td>
                    <td><?= esc($role['description']) ?></td>
                    <td>
                        <?php if ($role['status'] == 1): ?>
                            <span class="badge bg-success">Active</span>
                        <?php elseif ($role['status'] == 2): ?>
                            <span class="badge bg-secondary">Inactive</span>
                        <?php else: ?>
                            <span class="badge bg-dark">Deleted</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <!-- Delete Form - Sesuaikan dengan routes POST -->
                            <form action="<?= site_url('create-role/delete') ?>" method="post" onsubmit="return confirmDelete(event, this);">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $role['id'] ?>">
                                <button type="submit" class="btn btn-link p-0 text-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            
                            <!-- Edit Button -->
                            <button 
                                class="btn btn-link p-0 text-primary" 
                                data-bs-toggle="modal" 
                                data-bs-target="#editModal"
                                onclick="openEditModal(
                                    <?= $role['id'] ?>, 
                                    '<?= esc($role['name']) ?>', 
                                    '<?= esc($role['access_level']) ?>', 
                                    '<?= esc($role['description']) ?>', 
                                    '<?= esc($role['status']) ?>'
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

<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content shadow border-0">
      <div class="modal-header">
        <h5 class="modal-title">Edit Role</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <!-- Form Update - Sesuaikan dengan routes POST -->
      <form method="post" action="<?= site_url('create-role/update') ?>" id="editRoleForm">
        <?= csrf_field() ?>
        <div class="modal-body">
            <input type="hidden" name="id" id="editRoleId">
            <div class="mb-3">
                <label class="form-label">Role Name</label>
                <input type="text" name="role_name" id="editRoleName" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Level</label>
                <select name="role_level" id="editRoleLevel" class="form-select" required>
                    <option value="">-- Choose Level --</option>
                    <option value="1">Directorate/Faculty</option>
                    <option value="2">Unit</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="role_description" id="editRoleDescription" class="form-control" required></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="role_status" id="editRoleStatus" class="form-select">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary w-100">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function confirmDelete(event, form) {
        event.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: 'You want to delete this role?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
        return false;
    }

    $(document).ready(function () {
        $('#roleTable').DataTable({
            responsive: true,
            language: {
                search: "Search roles:",
                lengthMenu: "Show _MENU_ roles per page",
                info: "Showing _START_ to _END_ of _TOTAL_ roles",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });

        // Auto hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    });

    function openEditModal(id, roleName, roleLevel, roleDescription, roleStatus) {
        // Set form action sudah di-set static di form
        document.getElementById('editRoleId').value = id;
        document.getElementById('editRoleName').value = roleName;
        document.getElementById('editRoleLevel').value = roleLevel;
        document.getElementById('editRoleDescription').value = roleDescription;

        // Convert status 1/2 to 'active'/'inactive'
        let statusText = '';
        if (roleStatus == 1) {
            statusText = 'active';
        } else if (roleStatus == 2) {
            statusText = 'inactive';
        }
        document.getElementById('editRoleStatus').value = statusText;
    }

    // Handle form submission with loading state
    $('#editRoleForm').on('submit', function() {
        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Saving...');
    });
</script>

<?= $this->endSection() ?>
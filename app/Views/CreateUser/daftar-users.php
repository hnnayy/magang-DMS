<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="px-4 py-3 w-100">
  <h4>User List</h4>
  <hr>

  <div class="table-responsive bg-white p-3 rounded shadow-sm">
    <table class="table table-bordered table-hover align-middle" id="userTable" style="width: 100%;">
      <thead class="table-light">
        <tr>
          <th>No</th>
          <th>Employee ID</th>
          <th>Direktorat</th>
          <th>Unit</th>
          <th>Username</th>
          <th>Fullname</th>
          <th>Role</th>
          <?php 
          // Check if user has any action privileges for this page
          $privileges = session('privileges');
          $canUpdate = isset($privileges['user-list']['can_update']) && $privileges['user-list']['can_update'] == 1;
          $canDelete = isset($privileges['user-list']['can_delete']) && $privileges['user-list']['can_delete'] == 1;
          
          if ($canUpdate || $canDelete): ?>
            <th class="text-center noExport">Aksi</th>
          <?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php $i = 1; foreach ($users as $user): ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><?= esc($user['id']) ?></td>
          <td><?= esc($user['parent_name']) ?></td>
          <td><?= esc($user['unit_name']) ?></td>
          <td><?= esc($user['username']) ?></td>
          <td><?= esc($user['fullname']) ?></td>
          <td><?= esc($user['role_name'] ?? 'N/A') ?></td>
          
          <?php if ($canUpdate || $canDelete): ?>
          <td class="text-center">
            <?php if ($canDelete): ?>
              <a href="#" class="text-danger me-2 delete-user" data-id="<?= $user['id'] ?>" title="Delete">
                <i class="bi bi-trash"></i>
              </a>
            <?php endif; ?>
            
            <?php if ($canUpdate): ?>
              <a href="#" class="text-primary edit-user"
                data-bs-toggle="modal"
                data-bs-target="#editUserModal"
                data-id="<?= $user['id'] ?>"
                data-employee="<?= esc($user['id']) ?>"
                data-directorate="<?= esc($user['parent_id']) ?>"
                data-unit="<?= esc($user['unit_id']) ?>"
                data-username="<?= esc($user['username']) ?>"
                data-fullname="<?= esc($user['fullname']) ?>"
                data-role="<?= esc($user['role_name']) ?>">
                <i class="bi bi-pencil-square"></i>
              </a>
            <?php endif; ?>
          </td>
          <?php endif; ?>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Edit User - Only show if user has update privilege -->
<?php if ($canUpdate): ?>
<div class="modal fade" id="editUserModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow">
      <div class="modal-header">
        <h5 class="modal-title">Edit User</h5>
      </div>
      <form id="editUserForm">
        <div class="modal-body">
          <input type="hidden" id="editUserId"> <!-- Hidden field untuk user ID -->
          
          <div class="mb-3">
            <label for="editEmployeeId" class="form-label">Employee ID</label>
            <input type="text" class="form-control" id="editEmployeeId" readonly>
          </div>
          
          <!-- Fakultas/Direktorat with Search -->
          <div class="mb-3">
            <label class="form-label" for="editDirectorate">Fakultas/Direktorat</label>
            <div class="search-dropdown-container">
              <input type="text" id="editDirectorate-search" class="form-control search-input" 
                     placeholder="Search faculty/directorate..." autocomplete="off">
              <select class="form-select" id="editDirectorate" name="fakultas" required style="display: none;">
                <option value="">Choose Fakulty/Direktorate</option>
                <?php foreach ($unitParents as $parent): ?>
                  <option value="<?= $parent['id'] ?>"><?= esc($parent['name']) ?></option>
                <?php endforeach; ?>
              </select>
              <div id="editDirectorate-dropdown" class="search-dropdown-list" style="display: none;">
                <!-- Options will be populated here -->
              </div>
            </div>
          </div>
          
          <!-- Unit with Search -->
          <div class="mb-3">
            <label class="form-label" for="editUnit">Unit</label>
            <div class="search-dropdown-container">
              <input type="text" id="editUnit-search" class="form-control search-input" 
                     placeholder="Search unit..." autocomplete="off" disabled>
              <select class="form-select" id="editUnit" name="unit" required style="display: none;">
                <option value="">Choose Unit</option>
                <?php foreach ($units as $unit): ?>
                  <option value="<?= $unit['id'] ?>" data-parent="<?= $unit['parent_id'] ?>"><?= esc($unit['name']) ?></option>
                <?php endforeach; ?>
              </select>
              <div id="editUnit-dropdown" class="search-dropdown-list" style="display: none;">
                <!-- Options will be populated here -->
              </div>
            </div>
          </div>
          
          <div class="mb-3">
            <label for="editUsername" class="form-label">Username</label>
            <input type="text" class="form-control" id="editUsername">
          </div>
          
          <div class="mb-3">
            <label for="editFullname" class="form-label">Fullname</label>
            <input type="text" class="form-control" id="editFullname">
          </div>
          
          <!-- Role with Search -->
          <div class="mb-3">
            <label class="form-label" for="editRole">Role</label>
            <div class="search-dropdown-container">
              <input type="text" id="editRole-search" class="form-control search-input" 
                     placeholder="Search role..." autocomplete="off">
              <select class="form-select" id="editRole" name="role" required style="display: none;">
                <option value="">Choose Role</option>
                <?php foreach ($roles as $role): ?>
                  <option value="<?= esc($role['name']) ?>"><?= esc($role['name']) ?></option>
                <?php endforeach; ?>
              </select>
              <div id="editRole-dropdown" class="search-dropdown-list" style="display: none;">
                <!-- Options will be populated here -->
              </div>
            </div>
          </div>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="width: 48%;">Cancel</button>
          <button type="submit" class="btn btn-primary" style="width: 48%;">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function () {
  // Check privileges from PHP session
  const canUpdate = <?= json_encode($canUpdate) ?>;
  const canDelete = <?= json_encode($canDelete) ?>;
  
  // Define column definitions based on privileges
  let columnDefs = [{ orderable: false, targets: -1 }];
  if (canUpdate || canDelete) {
    columnDefs.push({ className: 'text-center', targets: -1 });
  }

  const table = $('#userTable').DataTable({
    dom: '<"row mb-3"<"col-md-6 export-buttons d-flex gap-2"B><"col-md-6 text-end"f>>rt<"row mt-3"<"col-md-6"l><"col-md-6 text-end"p>>',
    pageLength: 5,
    order: [],
    columnDefs: columnDefs,
    buttons: [
      {
        extend: 'excel',
        className: 'btn btn-outline-success btn-sm',
        title: 'Data_Users',
        exportOptions: { 
          columns: function(idx, data, node) {
            return $(node).hasClass('noExport') ? false : true;
          }
        }
      },
      {
        extend: 'pdfHtml5',
        text: 'PDF',
        className: 'btn',
        title: 'Data Users',
        filename: 'data_users',
        exportOptions: { 
          columns: function(idx, data, node) {
            return $(node).hasClass('noExport') ? false : true;
          }
        },
        orientation: 'landscape',
        pageSize: 'A4',
        customize: function (doc) {
          const now = new Date();
          const waktuCetak = now.toLocaleString('id-ID', {
            day: '2-digit', month: '2-digit', year: 'numeric',
            hour: '2-digit', minute: '2-digit'
          });

          if (doc.content[0] && doc.content[0].text === 'Data Users') {
            doc.content.splice(0, 1);
          }

          doc.content.unshift({
            text: 'Data Users',
            alignment: 'center',
            bold: true,
            fontSize: 16,
            margin: [0, 0, 0, 15]
          });

          doc.styles.tableHeader = {
            fillColor: '#eaeaea',
            color: '#000',
            alignment: 'center',
            bold: true,
            fontSize: 10
          };

          doc.defaultStyle.fontSize = 9;
          doc.styles.tableBody = { alignment: 'center', fontSize: 9 };

          doc.footer = function (currentPage, pageCount) {
            return {
              columns: [
                { text: 'Dicetak: ' + waktuCetak, alignment: 'left', margin: [40, 0] },
                { text: '© 2025 Telkom University – Document Management System', alignment: 'center' },
                { text: 'Halaman ' + currentPage.toString() + ' dari ' + pageCount, alignment: 'right', margin: [0, 0, 40, 0] }
              ],
              fontSize: 8,
              margin: [0, 10, 0, 0]
            };
          };

          doc.pageMargins = [40, 60, 40, 60];

          if (doc.content[1] && doc.content[1].table) {
            const hasActionColumn = canUpdate || canDelete;
            doc.content[1].table.widths = hasActionColumn 
              ? ['8%', '15%', '20%', '20%', '15%', '15%', '7%']
              : ['10%', '18%', '24%', '24%', '18%', '6%'];
            doc.content[1].margin = [0, 0, 0, 0];
            doc.content[1].layout = {
              hLineWidth: () => 0.5,
              vLineWidth: () => 0.5,
              hLineColor: () => '#000000',
              vLineColor: () => '#000000',
              paddingLeft: () => 5,
              paddingRight: () => 5,
              paddingTop: () => 3,
              paddingBottom: () => 3
            };
          }

          doc.content.push({
            text: '* This document contains a list of active users in the system.',
            alignment: 'left',
            italics: true,
            fontSize: 8,
            margin: [0, 15, 0, 0]
          });
        }
      }
    ]
  });

  // Edit User Modal Setup - Only if user has update privilege
  if (canUpdate) {
    // Initialize searchable dropdowns for modal
    const unitParentsData = <?= json_encode($unitParents) ?>;
    const unitsData = <?= json_encode($units) ?>;
    const rolesData = <?= json_encode($roles) ?>;
    
    let currentModalUnitData = [];
    
    // Generic function to populate dropdown
    function populateModalDropdown(dropdown, data, nameField = 'name') {
      dropdown.empty();
      
      if (data.length === 0) {
        dropdown.append('<div class="search-dropdown-item no-results">No results found</div>');
      } else {
        data.forEach(item => {
          const div = $('<div class="search-dropdown-item" data-id="' + item.id + '">' + item[nameField] + '</div>');
          dropdown.append(div);
        });
      }
    }

    // Initialize dropdowns
    populateModalDropdown($('#editDirectorate-dropdown'), unitParentsData);
    populateModalDropdown($('#editRole-dropdown'), rolesData);
    populateModalDropdown($('#editUnit-dropdown'), []);

    // Handle Directorate search
    $('#editDirectorate-search').on('input', function() {
      const searchTerm = $(this).val().toLowerCase();
      const filteredData = unitParentsData.filter(item => 
        item.name.toLowerCase().includes(searchTerm)
      );
      populateModalDropdown($('#editDirectorate-dropdown'), filteredData);
      
      if (searchTerm && !$('#editDirectorate-dropdown').is(':visible')) {
        $('#editDirectorate-dropdown').show();
      }
    });

    // Handle Unit search
    $('#editUnit-search').on('input', function() {
      const searchTerm = $(this).val().toLowerCase();
      const filteredData = currentModalUnitData.filter(item => 
        item.name.toLowerCase().includes(searchTerm)
      );
      populateModalDropdown($('#editUnit-dropdown'), filteredData);
      
      if (searchTerm && !$('#editUnit-dropdown').is(':visible')) {
        $('#editUnit-dropdown').show();
      }
    });

    // Handle Role search
    $('#editRole-search').on('input', function() {
      const searchTerm = $(this).val().toLowerCase();
      const filteredData = rolesData.filter(item => 
        item.name.toLowerCase().includes(searchTerm)
      );
      populateModalDropdown($('#editRole-dropdown'), filteredData);
      
      if (searchTerm && !$('#editRole-dropdown').is(':visible')) {
        $('#editRole-dropdown').show();
      }
    });

    // Handle focus events
    $('#editDirectorate-search').on('focus', function() {
      $('#editDirectorate-dropdown').show();
    });

    $('#editUnit-search').on('focus', function() {
      if (currentModalUnitData.length > 0) {
        $('#editUnit-dropdown').show();
      }
    });

    $('#editRole-search').on('focus', function() {
      $('#editRole-dropdown').show();
    });

    // Handle click outside to close dropdowns
    $(document).on('click', function(e) {
      if (!$(e.target).closest('.search-dropdown-container').length) {
        $('.search-dropdown-list').hide();
      }
    });

    // Handle Directorate selection
    $('#editDirectorate-dropdown').on('click', '.search-dropdown-item:not(.no-results)', function() {
      const selectedId = $(this).data('id');
      const selectedText = $(this).text();
      
      $('#editDirectorate-search').val(selectedText).addClass('has-selection');
      $('#editDirectorate').val(selectedId);
      $('#editDirectorate-dropdown').hide();
      
      // Clear unit selection and update options
      $('#editUnit-search').val('').removeClass('has-selection');
      $('#editUnit').val('');
      updateModalUnitOptions(selectedId);
    });

    // Handle Unit selection
    $('#editUnit-dropdown').on('click', '.search-dropdown-item:not(.no-results)', function() {
      const selectedId = $(this).data('id');
      const selectedText = $(this).text();
      
      $('#editUnit-search').val(selectedText).addClass('has-selection');
      $('#editUnit').val(selectedId);
      $('#editUnit-dropdown').hide();
    });

    // Handle Role selection
    $('#editRole-dropdown').on('click', '.search-dropdown-item:not(.no-results)', function() {
      const selectedId = $(this).data('id');
      const selectedText = $(this).text();
      
      $('#editRole-search').val(selectedText).addClass('has-selection');
      $('#editRole').val(selectedText);
      $('#editRole-dropdown').hide();
    });

    // Function to update unit options based on selected directorate
    function updateModalUnitOptions(selectedParentId) {
      if (selectedParentId) {
        currentModalUnitData = unitsData.filter(unit => unit.parent_id == selectedParentId);
        populateModalDropdown($('#editUnit-dropdown'), currentModalUnitData);
        $('#editUnit-search').prop('disabled', false).attr('placeholder', 'Search unit...');
      } else {
        currentModalUnitData = [];
        populateModalDropdown($('#editUnit-dropdown'), []);
        $('#editUnit-search').prop('disabled', true).attr('placeholder', 'Select directorate first...');
      }
    }

    // Edit User Click Handler
    $(document).on('click', '.edit-user', function () {
      const userId = $(this).data('id');
      const employeeId = $(this).data('employee');
      const fullname = $(this).data('fullname');
      const parentId = $(this).data('directorate');
      const unitId = $(this).data('unit');
      const roleName = $(this).data('role');

      // Set basic values
      $('#editUserId').val(userId);
      $('#editEmployeeId').val(employeeId);
      $('#editUsername').val($(this).data('username'));
      $('#editFullname').val(fullname);

      // Set directorate
      const selectedDirectorate = unitParentsData.find(item => item.id == parentId);
      if (selectedDirectorate) {
        $('#editDirectorate-search').val(selectedDirectorate.name).addClass('has-selection');
        $('#editDirectorate').val(parentId);
        updateModalUnitOptions(parentId);
        
        // Set unit after updating options
        setTimeout(() => {
          const selectedUnit = currentModalUnitData.find(item => item.id == unitId);
          if (selectedUnit) {
            $('#editUnit-search').val(selectedUnit.name).addClass('has-selection');
            $('#editUnit').val(unitId);
          }
        }, 100);
      }

      // Set role
      const selectedRole = rolesData.find(item => item.name === roleName);
      if (selectedRole) {
        $('#editRole-search').val(selectedRole.name).addClass('has-selection');
        $('#editRole').val(roleName);
      }
    });

    // Handle Edit Form Submission
    $('#editUserForm').submit(function (e) {
      e.preventDefault();

      if (!$('#editDirectorate').val() || !$('#editUnit').val() || !$('#editRole').val()) {
        Swal.fire('Peringatan', 'Semua field harus diisi!', 'warning');
        return;
      }

      $.ajax({
        url: '<?= base_url('create-user/update') ?>',
        method: 'POST',
        data: {
          <?= csrf_token() ?>: '<?= csrf_hash() ?>',
          id: $('#editUserId').val(),
          username: $('#editUsername').val(),
          fullname: $('#editFullname').val(),
          role: $('#editRole').val(),
          fakultas: $('#editDirectorate').val(),
          unit: $('#editUnit').val(),
          status: 1
        },
        success: function (res) {
          $('#editUserModal').modal('hide');
          Swal.fire({
            title: 'Success',
            text: 'Successfully Updated',
            icon: 'success'
          }).then(() => {
            location.reload();
          });
        },
        error: function (xhr) {
          const err = xhr.responseJSON?.error || 'Error occurred during the update..';
          Swal.fire('Failed', err, 'error');
        }
      });
    });
  }

  // Handle Delete User - Only if user has delete privilege
  if (canDelete) {
    $(document).on('click', '.delete-user', function (e) {
      e.preventDefault();
      const id = $(this).data('id');

      Swal.fire({
        title: 'Are you sure?',
        text: 'User will be permanently deleted..',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d'
      }).then((result) => {
        if (result.isConfirmed) {
          $.post('<?= base_url('create-user/delete') ?>', {
            <?= csrf_token() ?>: '<?= csrf_hash() ?>',
            id: id
          })
          .done(function (res) {
            Swal.fire({
              title: 'Success',
              text: 'Successfully Deleted',
              icon: 'success'
            }).then(() => {
              location.reload();
            });
          })
          .fail(function (xhr) {
            const err = xhr.responseJSON?.error || 'Failed to delete user.';
            Swal.fire('Failed', err, 'error');
          });
        }
      });
    });
  }
});
</script>
<?= $this->endSection() ?>
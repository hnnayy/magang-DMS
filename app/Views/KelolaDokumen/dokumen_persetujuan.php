<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<?php
// Get current user information from session
$currentUserId = session()->get('user_id');
$currentUserUnitId = session()->get('unit_id');
$currentUserUnitParentId = session()->get('unit_parent_id');
$currentUserRoleId = session()->get('role_id');

// Get user's role information to determine access level
$roleModel = new \App\Models\RoleModel();
$currentUserRole = $roleModel->find($currentUserRoleId);
$currentUserAccessLevel = $currentUserRole['access_level'] ?? 2; // Default level 2 (lower access)

// Ambil privilege untuk submenu 'document-approval' dari session
$privileges = session('privileges');
$docApprovalPrivileges = $privileges['document-approval'] ?? [
    'can_create' => 0,
    'can_update' => 0,
    'can_delete' => 0,
    'can_approve' => 0
];
?>

<div class="px-4 py-3 w-100">
  <h4>Document Approval</h4>
  <hr>

  <div class="table-responsive bg-white p-3 rounded shadow-sm">
    <table class="table table-bordered table-hover align-middle" id="persetujuanTable" style="width: 100%;">
      <thead class="table-light">
        <tr>
          <th>No</th>
          <th>Faculty/Directorate</th>
          <th>Department/Unit/Program</th>
          <th>Document Name</th>
          <th>Revision</th>
          <th>Document Type</th>
          <th>Code & Document Name</th>
          <th>File</th>
          <th>Remark</th>
          <th>Created By</th>
          <?php if ($docApprovalPrivileges['can_update'] || $docApprovalPrivileges['can_delete']): ?>
            <th class="text-center noExport">Action</th>
          <?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php $i = 1; foreach ($documents as $doc): 
            // Access control is now handled in controller
            $documentCreatorId = $doc['createdby'] ?? 0;
            $documentCreatorName = $doc['creator_fullname'] ?? 'Unknown User';
            
            // Skip documents with invalid creator ID
            if ($documentCreatorId == 0) continue;
            
            // Check permissions from controller
            $canEdit = $doc['can_edit'] ?? false;
            $canDelete = $doc['can_delete'] ?? false;
            
            // Show creator name for all visible documents (access already controlled in controller)
            $showCreatorName = true;
        ?>
        <tr data-document-id="<?= esc($doc['id']) ?>">
          <td><?= $i++ ?></td>
          <td><?= esc($doc['parent_name'] ?? '-') ?></td>
          <td><?= esc($doc['unit_name'] ?? '-') ?></td>
          <td>
            <span class="text-truncate-custom" title="<?= esc($doc['title']) ?>">
              <?= esc($doc['title']) ?>
            </span>
          </td>
          <td class="text-center"><?= esc($doc['revision']) ?></td>
          <td><?= esc($doc['jenis_dokumen']) ?></td>
          <td>
            <span class="text-truncate-custom" title="<?= esc($doc['kode_nama_dokumen'] ?? '-') ?>">
              <?= esc($doc['kode_nama_dokumen'] ?? '-') ?>
            </span>
          </td>
          <td class="text-center">
            <?php if (!empty($doc['filepath']) && file_exists(ROOTPATH . '..' . DIRECTORY_SEPARATOR . $doc['filepath'])): ?>
              <div class="d-flex justify-content-center gap-2">
                <a href="<?= base_url('document-approval/serveFile?id=' . $doc['id'] . '&action=download') ?>" 
                   class="text-decoration-none" 
                   title="Download <?= esc($doc['filename'] ?? basename($doc['filepath'])) ?>">
                  <i class="bi bi-download text-success fs-4"></i>
                </a>
              </div>
            <?php else: ?>
              <span class="text-muted">
                <i class="bi bi-file-earmark-x"></i> No file
              </span>
            <?php endif; ?>
          </td>
          <td>
            <span class="text-truncate-custom" title="<?= esc($doc['remark']) ?>">
              <?= esc($doc['remark']) ?>
            </span>
          </td>
          <td>
            <?php if ($showCreatorName): ?>
              <span class="text-truncate-custom" title="<?= esc($documentCreatorName) ?>">
                <?= esc($documentCreatorName) ?>
              </span>
            <?php else: ?>
              <span class="text-muted">-</span>
            <?php endif; ?>
          </td>
          <?php if ($docApprovalPrivileges['can_update'] || $docApprovalPrivileges['can_delete']): ?>
          <td class="text-center">
            <div class="action-buttons d-flex justify-content-center gap-3">
              <?php if ($docApprovalPrivileges['can_update'] && $canEdit): ?>
              <a href="#" class="text-primary edit-document"
                 data-bs-toggle="modal"
                 data-bs-target="#editModal"
                 data-id="<?= esc($doc['id']) ?>"
                 data-title="<?= esc($doc['title']) ?>"
                 data-revision="<?= esc($doc['revision']) ?>"
                 data-remark="<?= esc($doc['remark']) ?>">
                <i class="bi bi-pencil-square"></i>
              </a>
              <?php endif; ?>
              <?php if ($docApprovalPrivileges['can_delete'] && $canDelete): ?>
              <a href="#" class="text-danger me-2 delete-document" 
                 data-id="<?= esc($doc['id']) ?>" 
                 title="Delete">
                <i class="bi bi-trash"></i>
              </a>
              <?php endif; ?>
            </div>
          </td>
          <?php endif; ?>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Single Edit Modal for all documents -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow">
      <div class="modal-header">
        <h5 class="modal-title">Edit Document</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="editDocumentForm">
        <div class="modal-body">
          <input type="hidden" id="editDocumentId" name="document_id">
          <div class="mb-3">
            <label class="form-label">Title <span class="text-danger">*</span></label>
            <input type="text" name="title" id="editDocumentTitle" class="form-control" required>
            <div class="invalid-feedback">Title is required.</div>
          </div>
          <div class="mb-3">
            <label class="form-label">Revision <span class="text-danger">*</span></label>
            <input type="text" name="revision" id="editDocumentRevision" class="form-control" required>
            <div class="invalid-feedback">Revision is required.</div>
          </div>
          <div class="mb-3">
            <label class="form-label">Remark</label>
            <textarea name="remark" id="editDocumentRemark" class="form-control" rows="3" placeholder="Enter remarks (optional)"></textarea>
          </div>
        </div>
        <div class="modal-footer d-grid gap-2" style="grid-template-columns: 1fr 1fr;">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="saveChangesBtn">
            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
            Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('script') ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* Highlight style for the selected document row */
tr.document-highlight {
    background-color: #d3d3d3 !important; /* Light gray background */
    transition: background-color 0.3s ease;
}

/* Action buttons styling */
.action-buttons .btn-action {
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
}

.action-buttons .btn-action i {
    fontSize: 14px;
}

/* Text truncation */
.text-truncate-custom {
    display: inline-block;
    max-width: 200px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    vertical-align: middle;
}

/* Form validation styles */
.form-control.is-invalid {
    border-color: #dc3545;
    padding-right: calc(1.5em + 0.75rem);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 5.8-3.6-3.6m0 0 3.6 3.6m-3.6-3.6 3.6 3.6'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.form-control.is-valid {
    border-color: #198754;
    padding-right: calc(1.5em + 0.75rem);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='m2.3 6.73.94-.94 1.44 1.44L2.3 6.73z'/%3e%3cpath fill='%23198754' d='m6.564.75-3.59 3.612-1.538-1.55L0 4.25 2.974 7.25 8 2.193z'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

/* Loading state */
.btn.loading {
    pointer-events: none;
}

.btn.loading .spinner-border {
    width: 1rem;
    height: 1rem;
}
</style>

<script>
$(document).ready(function () {
  const canUpdate = <?= json_encode($docApprovalPrivileges['can_update']) ?>;
  const canDelete = <?= json_encode($docApprovalPrivileges['can_delete']) ?>;
  
  let columnDefs = [
    { orderable: false, targets: [0, 7] }, // Disable sorting for No and File columns
    { className: 'text-center', targets: [0, 4, 7] } // Center-align No, Revision, File
  ];
  
  if (canUpdate || canDelete) {
    columnDefs.push({ orderable: false, searchable: false, targets: -1 });
    columnDefs.push({ className: 'text-center', targets: -1 });
  }

  const table = $('#persetujuanTable').DataTable({
    dom: '<"row mb-3"<"col-md-6 export-buttons d-flex gap-2"B><"col-md-6 text-end"f>>rt<"row mt-3"<"col-md-6"l><"col-md-6 text-end"p>>',
    pageLength: 10,
    order: [],
    columnDefs: columnDefs,
    responsive: true,
    buttons: [
      {
        extend: 'excel',
        className: 'btn btn-outline-success btn-sm',
        title: 'Document_Approval',
        exportOptions: { 
          columns: [0, 1, 2, 3, 4, 5, 6, 8, 9] // Exclude File and Action columns
        }
      },
      {
        extend: 'pdfHtml5',
        text: 'PDF',
        className: 'btn btn-outline-danger btn-sm',
        title: 'Document Approval',
        filename: 'document_approval',
        exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 8, 9] },
        orientation: 'landscape',
        pageSize: 'A4',
        customize: function (doc) {
          const now = new Date();
          const waktuCetak = now.toLocaleString('id-ID', {
            day: '2-digit', month: '2-digit', year: 'numeric',
            hour: '2-digit', minute: '2-digit'
          });

          if (doc.content[0].text === 'Document Approval') {
            doc.content.splice(0, 1);
          }

          doc.content.unshift({
            text: 'Document Approval Report',
            alignment: 'center',
            bold: true,
            fontSize: 16,
            margin: [0, 0, 0, 10]
          });

          doc.styles.tableHeader = {
            fillColor: '#eaeaea',
            color: '#000',
            alignment: 'center',
            bold: true,
            fontSize: 9
          };

          doc.styles.tableBodyEven = { fillColor: '#ffffff' };
          doc.styles.tableBodyOdd = { fillColor: '#ffffff' };

          doc.defaultStyle.fontSize = 8;
          doc.styles.tableBody = { alignment: 'left', fontSize: 8 };

          doc.footer = function (currentPage, pageCount) {
            return {
              columns: [
                { text: waktuCetak, alignment: 'left', margin: [30, 0] },
                { text: '© 2025 Telkom University – Document Management System', alignment: 'center' },
                { text: currentPage.toString() + '/' + pageCount, alignment: 'right', margin: [0, 0, 30, 0] }
              ],
              fontSize: 9
            };
          };

          doc.pageMargins = [40, 40, 40, 40];

          if (doc.content[1] && doc.content[1].table) {
            doc.content[1].table.widths = ['5%', '12%', '12%', '15%', '8%', '10%', '12%', '12%', '10%'];
            doc.content[1].margin = [0, 0, 0, 0];
          }

          doc.content[doc.content.length - 1].layout = {
            hLineWidth: function () { return 0.5; },
            vLineWidth: function () { return 0.5; },
            hLineColor: function () { return '#000000'; },
            vLineColor: function () { return '#000000'; },
            paddingLeft: function () { return 3; },
            paddingRight: function () { return 3; },
            paddingTop: function () { return 2; },
            paddingBottom: function () { return 2; }
          };

          doc.content.push({
            text: '* This document contains a list of documents pending approval based on your access level.',
            alignment: 'left',
            italics: true,
            fontSize: 8,
            margin: [0, 12, 0, 0]
          });
        }
      }
    ]
  });

  // Filter and highlight document from URL parameter
  function filterAndHighlightDocumentFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    const documentId = urlParams.get('document_id');
    
    if (documentId) {
      // Add custom filter to show only the row with the matching document_id
      $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
          const row = $('#persetujuanTable').DataTable().row(dataIndex);
          const rowDocumentId = row.node().getAttribute('data-document-id') || '';
          return rowDocumentId === documentId;
        }
      );
      table.draw();

      // Find the row with the matching document ID
      let targetRow = null;
      table.rows().every(function() {
        const rowNode = this.node();
        if ($(rowNode).data('document-id') == documentId) {
          targetRow = rowNode;
          return false; // Break the loop
        }
      });

      if (targetRow) {
        // Remove previous highlights
        $('#persetujuanTable tbody tr').removeClass('document-highlight');
        
        // Apply highlight class
        $(targetRow).addClass('document-highlight');
        
        // Ensure the row is visible and scroll to it
        setTimeout(() => {
          const $row = $(targetRow);
          if ($row.length) {
            $('html, body').animate({
              scrollTop: $row.offset().top - 100
            }, 500);
            
            // Fade out highlight after 5 seconds
            setTimeout(() => {
              $row.removeClass('document-highlight');
            }, 5000);
          }
        }, 100); // Delay to ensure DataTables has rendered
      } else {
        Swal.fire({
          icon: 'warning',
          title: 'Document Not Found',
          text: 'The document with ID ' + documentId + ' was not found or you do not have access to it.',
          confirmButtonColor: '#dc3545'
        });
      }
    } else {
      // If no document_id in URL, clear any existing filters
      if ($.fn.dataTable.ext.search.length > 0) {
        $.fn.dataTable.ext.search.pop();
        table.draw();
      }
    }
  }

  // Call filter and highlight function after table is initialized
  filterAndHighlightDocumentFromUrl();

  // Re-apply filter and highlight on page change or redraw
  table.on('draw', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const documentId = urlParams.get('document_id');
    
    if (documentId) {
      let targetRow = null;
      table.rows().every(function() {
        const rowNode = this.node();
        if ($(rowNode).data('document-id') == documentId) {
          targetRow = rowNode;
          return false;
        }
      });

      if (targetRow) {
        $('#persetujuanTable tbody tr').removeClass('document-highlight');
        $(targetRow).addClass('document-highlight');
      }
    }
  });

  // Handle edit document modal
  if (canUpdate) {
    $(document).on('click', '.edit-document', function (e) {
      e.preventDefault();
      
      const documentId = $(this).data('id');
      const title = $(this).data('title');
      const revision = $(this).data('revision');
      const remark = $(this).data('remark');

      // Populate modal fields
      $('#editDocumentId').val(documentId);
      $('#editDocumentTitle').val(title);
      $('#editDocumentRevision').val(revision);
      $('#editDocumentRemark').val(remark);
      
      // Clear validation states
      $('.form-control').removeClass('is-valid is-invalid');
      $('.invalid-feedback').hide();
      
      // Reset button state
      $('#saveChangesBtn').removeClass('loading');
      $('#saveChangesBtn .spinner-border').addClass('d-none');
    });

    // Form validation function
    function validateForm() {
      let isValid = true;
      const title = $('#editDocumentTitle').val().trim();
      const revision = $('#editDocumentRevision').val().trim();
      
      // Reset validation states
      $('.form-control').removeClass('is-valid is-invalid');
      
      // Validate title
      if (!title) {
        $('#editDocumentTitle').addClass('is-invalid');
        isValid = false;
      } else {
        $('#editDocumentTitle').addClass('is-valid');
      }
      
      // Validate revision
      if (!revision) {
        $('#editDocumentRevision').addClass('is-invalid');
        isValid = false;
      } else {
        $('#editDocumentRevision').addClass('is-valid');
      }
      
      // Remark is optional, but if filled, mark as valid
      if ($('#editDocumentRemark').val().trim()) {
        $('#editDocumentRemark').addClass('is-valid');
      }
      
      return isValid;
    }

    // Real-time validation
    $('#editDocumentTitle, #editDocumentRevision').on('input', function() {
      const value = $(this).val().trim();
      $(this).removeClass('is-valid is-invalid');
      
      if (value) {
        $(this).addClass('is-valid');
      } else {
        $(this).addClass('is-invalid');
      }
    });

    // Handle form submission
    $('#editDocumentForm').on('submit', function (e) {
      e.preventDefault();

      if (!validateForm()) {
        Swal.fire({
          icon: 'warning',
          title: 'Validation Error',
          text: 'Please fill in all required fields correctly.',
          confirmButtonColor: '#dc3545'
        });
        return;
      }

      const formData = {
        <?= csrf_token() ?>: '<?= csrf_hash() ?>',
        document_id: $('#editDocumentId').val(),
        title: $('#editDocumentTitle').val().trim(),
        revision: $('#editDocumentRevision').val().trim(),
        remark: $('#editDocumentRemark').val().trim()
      };

      // Set loading state
      const saveBtn = $('#saveChangesBtn');
      saveBtn.addClass('loading');
      saveBtn.find('.spinner-border').removeClass('d-none');
      saveBtn.prop('disabled', true);

      $.ajax({
        url: '<?= base_url('document-approval/update') ?>',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function (response) {
          $('#editModal').modal('hide');
          
          if (response.status === 'success') {
            Swal.fire({
              icon: 'success',
              title: 'Success',
              text: response.message || 'Document updated successfully',
              confirmButtonColor: '#198754'
            }).then(() => {
              location.reload();
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: response.error || 'Failed to update document',
              confirmButtonColor: '#dc3545'
            });
          }
        },
        error: function (xhr) {
          console.error('Update error:', xhr);
          let errorMessage = 'An error occurred while updating the document.';
          
          if (xhr.responseJSON && xhr.responseJSON.error) {
            errorMessage = xhr.responseJSON.error;
          } else if (xhr.status === 403) {
            errorMessage = 'You do not have permission to update this document.';
          } else if (xhr.status === 404) {
            errorMessage = 'Document not found.';
          } else if (xhr.status === 500) {
            errorMessage = 'Server error occurred. Please try again later.';
          }
          
          Swal.fire({
            icon: 'error',
            title: 'Update Failed',
            text: errorMessage,
            confirmButtonColor: '#dc3545'
          });
        },
        complete: function() {
          // Reset loading state
          const saveBtn = $('#saveChangesBtn');
          saveBtn.removeClass('loading');
          saveBtn.find('.spinner-border').addClass('d-none');
          saveBtn.prop('disabled', false);
        }
      });
    });

    // Reset form when modal is hidden
    $('#editModal').on('hidden.bs.modal', function () {
      $('#editDocumentForm')[0].reset();
      $('.form-control').removeClass('is-valid is-invalid');
      const saveBtn = $('#saveChangesBtn');
      saveBtn.removeClass('loading');
      saveBtn.find('.spinner-border').addClass('d-none');
      saveBtn.prop('disabled', false);
    });
  }

  // Handle delete document
  if (canDelete) {
    $(document).on('click', '.delete-document', function (e) {
      e.preventDefault();
      const id = $(this).data('id');
      const row = $(this).closest('tr');
      const documentTitle = row.find('td:nth-child(4)').text().trim();

      Swal.fire({
        title: 'Are you sure?',
        html: `This action will permanently delete the document:<br><strong>"${documentTitle}"</strong>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          // Show loading state
          Swal.fire({
            title: 'Deleting...',
            text: 'Please wait while we delete the document.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
              Swal.showLoading();
            }
          });

          $.ajax({
            url: '<?= base_url('document-approval/delete') ?>',
            method: 'POST',
            data: {
              <?= csrf_token() ?>: '<?= csrf_hash() ?>',
              document_id: id
            },
            dataType: 'json',
            success: function (response) {
              if (response.status === 'success') {
                Swal.fire({
                  icon: 'success',
                  title: 'Deleted!',
                  text: response.message || 'Document has been deleted successfully.',
                  confirmButtonColor: '#198754'
                }).then(() => {
                  location.reload();
                });
              } else {
                Swal.fire({
                  icon: 'error',
                  title: 'Delete Failed',
                  text: response.error || 'Failed to delete document.',
                  confirmButtonColor: '#dc3545'
                });
              }
            },
            error: function (xhr) {
              console.error('Delete error:', xhr);
              let errorMessage = 'An error occurred while deleting the document.';
              
              if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMessage = xhr.responseJSON.error;
              } else if (xhr.status === 403) {
                errorMessage = 'You do not have permission to delete this document.';
              } else if (xhr.status === 404) {
                errorMessage = 'Document not found.';
              } else if (xhr.status === 500) {
                errorMessage = 'Server error occurred. Please try again later.';
              }
              
              Swal.fire({
                icon: 'error',
                title: 'Delete Failed',
                text: errorMessage,
                confirmButtonColor: '#dc3545'
              });
            }
          });
        }
      });
    });
  }

  // Initialize tooltips
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // Enhanced search with debounce
  let searchTimeout;
  $('.dataTables_filter input').on('input', function() {
    clearTimeout(searchTimeout);
    const searchTerm = this.value;
    
    searchTimeout = setTimeout(function() {
      table.search(searchTerm).draw();
    }, 300);
  });

  // Keyboard shortcuts
  $(document).keydown(function(e) {
    if (e.ctrlKey && e.key === 'f') {
      e.preventDefault();
      $('.dataTables_filter input').focus();
    }
    
    if (e.key === 'Escape') {
      $('.modal.show').modal('hide');
    }
  });

  // Hover effects for table rows
  $('#persetujuanTable tbody').on('mouseenter', 'tr', function() {
    if (!$(this).hasClass('document-highlight')) {
      $(this).addClass('table-active');
    }
  }).on('mouseleave', 'tr', function() {
    if (!$(this).hasClass('document-highlight')) {
      $(this).removeClass('table-active');
    }
  });

  // Auto-hide alerts after 5 seconds
  setTimeout(function() {
    $('.alert').fadeOut('slow');
  }, 5000);
});
</script>
<?= $this->endSection() ?>
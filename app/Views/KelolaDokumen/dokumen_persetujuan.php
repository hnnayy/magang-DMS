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

<div class="container-fluid persetujuan-container">
    <h4 class="mb-4">Document Approval</h4>

    <div class="card persetujuan-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="export-buttons-container"></div>
                <div class="search-container"></div>
            </div>

            <div class="table-scroll-container">
                <table id="persetujuanTable" class="table table-bordered table-striped persetujuan-table">
                    <thead>
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
                        <?php 
                        $displayedCount = 0;
                        foreach ($documents as $i => $doc): 
                            // Check if document should be visible based on hierarchical access
                            $documentCreatorId = $doc['createdby'] ?? 0;
                            $documentCreatorUnitId = $doc['creator_unit_id'] ?? 0;
                            $documentCreatorUnitParentId = $doc['creator_unit_parent_id'] ?? 0;
                            $documentCreatorAccessLevel = $doc['creator_access_level'] ?? 2;
                            $documentCreatorName = $doc['creator_fullname'] ?? 'Unknown User';
                            
                            $canViewDocument = false;
                            $showCreatorName = false; // Control creator name visibility
                            
                            // Access Control Rules:
                            // Rule 1: Users can always see their own documents and their own name
                            if ($documentCreatorId == $currentUserId) {
                                $canViewDocument = true;
                                $showCreatorName = true;
                            }
                            // Rule 2: Higher level users (level 1) can see lower level documents (level 2) in same hierarchy
                            elseif ($currentUserAccessLevel < $documentCreatorAccessLevel) {
                                // Check if they are in the same organizational hierarchy
                                $sameUnit = ($documentCreatorUnitId == $currentUserUnitId);
                                $sameUnitParent = ($documentCreatorUnitParentId == $currentUserUnitParentId);
                                $creatorIsSubordinate = ($documentCreatorUnitParentId == $currentUserUnitId);
                                $inSameHierarchy = $sameUnit || $sameUnitParent || $creatorIsSubordinate;
                                
                                if ($inSameHierarchy) {
                                    $canViewDocument = true;
                                    $showCreatorName = true; // Higher level users can see creator names
                                }
                            }
                            // Rule 3: Same level users in same unit can see each other's documents
                            elseif ($currentUserAccessLevel == $documentCreatorAccessLevel) {
                                $sameUnit = ($documentCreatorUnitId == $currentUserUnitId);
                                if ($sameUnit) {
                                    $canViewDocument = true;
                                    $showCreatorName = true; // Same level users can see each other's names
                                }
                            }
                            
                            // Skip if user cannot view this document
                            if (!$canViewDocument) continue;
                            
                            // Skip documents with invalid creator ID
                            if ($documentCreatorId == 0) continue;
                            
                            $displayedCount++;
                        ?>
                        <tr>
                            <td class="text-center"><?= $displayedCount ?></td>
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
                                            <i class="bi bi-download text-success fs-5"></i>
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
                            
                            <!-- Action column - hanya tampil jika ada privilege update atau delete -->
                            <?php if ($docApprovalPrivileges['can_update'] || $docApprovalPrivileges['can_delete']): ?>
                            <td class="text-center">
                                <div class="action-buttons">
                                    <!-- Delete button - hanya tampil jika ada privilege can_delete dan user bisa akses dokumen -->
                                    <?php if ($docApprovalPrivileges['can_delete'] && ($documentCreatorId == $currentUserId || $currentUserAccessLevel < $documentCreatorAccessLevel)): ?>
                                    <form method="post" action="<?= base_url('document-approval/delete') ?>" class="d-inline-block delete-form">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="document_id" value="<?= $doc['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-action btn-delete" data-id="<?= $doc['id'] ?>" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    
                                    <!-- Edit button - hanya tampil jika ada privilege can_update dan user bisa akses dokumen -->
                                    <?php if ($docApprovalPrivileges['can_update'] && ($documentCreatorId == $currentUserId || $currentUserAccessLevel < $documentCreatorAccessLevel)): ?>
                                    <button class="btn btn-sm btn-outline-primary btn-action" data-bs-toggle="modal" data-bs-target="#editModal<?= $doc['id'] ?>" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <?php endif; ?>
                                    

                                </div>
                            </td>
                            <?php endif; ?>
                        </tr>

                        <!-- Edit Modal - hanya tampil jika ada privilege can_update dan user bisa edit -->
                        <?php if ($docApprovalPrivileges['can_update'] && ($documentCreatorId == $currentUserId || $currentUserAccessLevel < $documentCreatorAccessLevel)): ?>
                        <div class="modal fade" id="editModal<?= $doc['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form method="post" action="<?= base_url('document-approval/update') ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="document_id" value="<?= $doc['id'] ?>">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Document</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Title</label>
                                                <input type="text" name="title" class="form-control" value="<?= esc($doc['title']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Revision</label>
                                                <input type="text" name="revision" class="form-control" value="<?= esc($doc['revision']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Remark</label>
                                                <textarea name="remark" class="form-control" rows="3"><?= esc($doc['remark']) ?></textarea>
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
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="pagination-container">
                <div class="dataTables_length"></div>
                <div class="dataTables_paginate"></div>
            </div>
        </div>
    </div>
</div>

<!-- Styles -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="<?= base_url('assets/css/datatables-custom.css') ?>" rel="stylesheet">
<link href="<?= base_url('assets/css/persetujuan.css') ?>" rel="stylesheet">

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- DataTables Logic -->
<script>
$(document).ready(function () {
    <?php 
    // Hitung jumlah kolom berdasarkan privilege
    $totalColumns = 10; // kolom dasar dengan created by
    if ($docApprovalPrivileges['can_update'] || $docApprovalPrivileges['can_delete']) {
        $totalColumns = 11; // tambah kolom action
    }
    $actionColumnIndex = $totalColumns - 1;
    ?>
    
    const table = $('#persetujuanTable').DataTable({
        dom: 't', 
        pageLength: 10,
        order: [],
        columnDefs: [
            <?php if ($docApprovalPrivileges['can_update'] || $docApprovalPrivileges['can_delete']): ?>
            { orderable: false, targets: <?= $actionColumnIndex ?> },
            { className: 'text-center', targets: [0, 4, <?= $actionColumnIndex ?>] }
            <?php else: ?>
            { className: 'text-center', targets: [0, 4] }
            <?php endif; ?>
        ],
        buttons: [
            {
                extend: 'excel',
                className: 'btn btn-outline-success btn-sm',
                title: 'Document_Approval',
                exportOptions: { 
                    columns: [0, 1, 2, 3, 4, 5, 6, 8, 9] // Exclude file column (7) and action column
                }
            }
        ],
        lengthMenu: [10, 25, 50, 100],
        language: {
            lengthMenu: "Show _MENU_ entries",
            paginate: {
                previous: "Previous",
                next: "Next"
            },
            info: "",
            infoEmpty: "",
            infoFiltered: ""
        }
    });

    // Custom PDF Export Function
    function exportToPDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape', 'pt', 'a4');
        
        // Header
        doc.setFontSize(16);
        doc.setFont(undefined, 'bold');
        doc.text('Document Approval Report', doc.internal.pageSize.getWidth() / 2, 50, { align: 'center' });
        
        // Get table data
        const tableData = [];
        const headers = [
            'No', 'Faculty/Directorate', 'Department/Unit/Program', 
            'Document Name', 'Revision', 'Document Type', 
            'Code & Document Name', 'Remark', 'Created By'
        ];
        
        // Get visible rows from DataTable
        const visibleRows = table.rows({ search: 'applied' }).nodes();
        
        for (let i = 0; i < visibleRows.length; i++) {
            const row = visibleRows[i];
            const cells = row.querySelectorAll('td');
            const rowArray = [];
            
            // Extract data from each cell (exclude file and action columns)
            for (let j = 0; j < cells.length; j++) {
                if (j === 7) continue; // Skip file column (index 7)
                if (j >= 10) continue; // Skip action column if exists
                
                let cellText = cells[j].textContent.trim();
                
                // Clean up text
                if (cellText === 'No file') cellText = '-';
                if (cellText.includes('Download')) cellText = 'Available';
                
                rowArray.push(cellText);
            }
            
            if (rowArray.length === 9) { // Ensure we have exactly 9 columns
                tableData.push(rowArray);
            }
        }
        
        // Generate table with autoTable
        doc.autoTable({
            head: [headers],
            body: tableData,
            startY: 80,
            theme: 'grid',
            headStyles: {
                fillColor: [234, 234, 234],
                textColor: [0, 0, 0],
                fontStyle: 'bold',
                fontSize: 8,
                halign: 'center'
            },
            bodyStyles: {
                fontSize: 7,
                halign: 'center'
            },
            alternateRowStyles: {
                fillColor: [248, 249, 250]
            },
            columnStyles: {
                0: { cellWidth: 40 },   // No
                1: { cellWidth: 80 },   // Faculty
                2: { cellWidth: 100 },  // Department
                3: { cellWidth: 120 },  // Document Name
                4: { cellWidth: 50 },   // Revision
                5: { cellWidth: 80 },   // Document Type
                6: { cellWidth: 100 },  // Code & Name
                7: { cellWidth: 80 },   // Remark
                8: { cellWidth: 80 }    // Created By
            },
            margin: { top: 80, bottom: 60, left: 40, right: 40 },
            didDrawPage: function(data) {
                // Footer
                const pageHeight = doc.internal.pageSize.getHeight();
                const pageWidth = doc.internal.pageSize.getWidth();
                
                doc.setFontSize(8);
                doc.setFont(undefined, 'normal');
                
                // Print time
                const now = new Date();
                const printTime = now.toLocaleString('en-US', {
                    day: '2-digit', month: '2-digit', year: 'numeric',
                    hour: '2-digit', minute: '2-digit'
                });
                
                doc.text(`Printed: ${printTime}`, 40, pageHeight - 40);
                
                // Copyright
                doc.text('© 2025 Telkom University – Document Management System', 
                        pageWidth / 2, pageHeight - 40, { align: 'center' });
                
                // Page number
                doc.text(`Page ${data.pageNumber}`, pageWidth - 40, pageHeight - 40, { align: 'right' });
            }
        });
        
        // Add note at the bottom
        const finalY = doc.lastAutoTable.finalY + 20;
        doc.setFontSize(8);
        doc.setFont(undefined, 'italic');
        doc.text('* This document contains a list of documents pending approval based on your access level.', 
                 40, finalY);
        
        // Save the PDF
        doc.save('document_approval.pdf');
    }

    // Setup custom export buttons
    $('.export-buttons-container').html(`
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-success btn-sm" id="exportExcel">
                <i class="bi bi-file-earmark-excel"></i> Excel
            </button>
            <button type="button" class="btn btn-outline-danger btn-sm" id="exportPDF">
                <i class="bi bi-file-earmark-pdf"></i> PDF
            </button>
        </div>
    `);

    // Handle Excel export (using existing DataTables functionality)
    $('#exportExcel').on('click', function() {
        table.button(0).trigger(); // Trigger first button (Excel)
    });

    // Handle PDF export (using custom function)
    $('#exportPDF').on('click', function() {
        exportToPDF();
    });

    // Setup custom search
    $('.search-container').html(`
        <div class="d-flex align-items-center gap-2">
            <label class="mb-0">Search:</label>
            <input type="search" class="form-control form-control-sm" id="customSearch" placeholder="Search documents...">
        </div>
    `);

    $('#customSearch').on('keyup', function() {
        table.search(this.value).draw();
    });

    $('.dataTables_length').html(`
        <div class="d-flex align-items-center gap-2">
            <label class="mb-0">Show:</label>
            <select class="form-select form-select-sm" id="customLength" style="width: auto;">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
            <span class="mb-0">entries</span>
        </div>
    `);

    $('#customLength').on('change', function() {
        table.page.len(parseInt(this.value)).draw();
    });

    // Setup custom pagination
    function updatePagination() {
        const info = table.page.info();
        let paginationHtml = '<nav><ul class="pagination justify-content-center mb-0">';
        
        // Previous button
        if (info.page > 0) {
            paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${info.page - 1}">Previous</a></li>`;
        } else {
            paginationHtml += '<li class="page-item disabled"><span class="page-link">Previous</span></li>';
        }
        
        // Page numbers
        const startPage = Math.max(0, info.page - 2);
        const endPage = Math.min(info.pages - 1, info.page + 2);
        
        for (let i = startPage; i <= endPage; i++) {
            if (i === info.page) {
                paginationHtml += `<li class="page-item active"><span class="page-link">${i + 1}</span></li>`;
            } else {
                paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${i}">${i + 1}</a></li>`;
            }
        }
        
        // Next button
        if (info.page < info.pages - 1) {
            paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${info.page + 1}">Next</a></li>`;
        } else {
            paginationHtml += '<li class="page-item disabled"><span class="page-link">Next</span></li>';
        }
        
        paginationHtml += '</ul></nav>';
        
        $('.dataTables_paginate').html(paginationHtml);
    }

    table.on('draw', function() {
        updatePagination();
    });

    // Handle pagination click
    $(document).on('click', '.dataTables_paginate .page-link[data-page]', function(e) {
        e.preventDefault();
        const page = parseInt($(this).data('page'));
        table.page(page).draw(false);
    });

    updatePagination();

    // Delete confirmation with SweetAlert2 - hanya jika ada privilege delete
    <?php if ($docApprovalPrivileges['can_delete']): ?>
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        const form = $(this).closest('form');
        Swal.fire({
            title: 'Are you sure?',
            text: 'This action will delete the document permanently.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#d33',
            cancelButtonColor: 'rgba(118, 125, 131, 1)',
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
    <?php endif; ?>

    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<?= $this->endSection() ?>
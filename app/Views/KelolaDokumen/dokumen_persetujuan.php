<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<?php
$currentUserId = session()->get('user_id');
$currentUserUnitId = session()->get('unit_id');
$currentUserUnitParentId = session()->get('unit_parent_id');
$currentUserRoleId = session()->get('role_id');
$roleModel = new \App\Models\RoleModel();
$currentUserRole = $roleModel->find($currentUserRoleId);
$currentUserAccessLevel = $currentUserRole['access_level'] ?? 2;

$privileges = session()->get('privileges') ?? [];
$documentSubmissionPrivileges = $privileges['document-approval'] ?? [
    'can_create' => 0,
    'can_update' => 0,
    'can_delete' => 0
];

$badgeColors = [
    'bg-primary', 'bg-success', 'bg-info', 'bg-warning', 'bg-danger', 'bg-secondary', 'bg-dark', 'bg-primary-subtle', 'bg-success-subtle', 'bg-info-subtle'
];

$uniqueJenisDokumen = array_unique(array_column($documents, 'jenis_dokumen'));
sort($uniqueJenisDokumen);
$jenisColorMap = [];
foreach ($uniqueJenisDokumen as $index => $jenis) {
    $jenisColorMap[$jenis] = $badgeColors[$index % count($badgeColors)];
}
?>

<div class="container-fluid">
    <div class="px-4 py-3">
        <h4 class="mb-4">Document Approval</h4>

        <?php if (session()->getFlashdata('success')): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '<?= session()->getFlashdata('success') ?>',
                    confirmButtonColor: '#28a745',
                    showConfirmButton: true
                });
            });
        </script>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: '<?= session()->getFlashdata('error') ?>',
                    confirmButtonColor: '#dc3545',
                    showConfirmButton: true
                });
            });
        </script>
        <?php endif; ?>

        <div class="row mb-3">
            <div class="col-md-6">
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-success btn-sm" id="exportExcel">
                        <i class="bi bi-file-earmark-excel"></i> Excel
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" id="exportPDF">
                        <i class="bi bi-file-earmark-pdf"></i> PDF
                    </button>
                </div>
            </div>
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" class="form-control" id="customSearch" placeholder="Search documents...">
                    <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                        <i class="bi bi-search"></i> Search
                    </button>
                </div>
            </div>
        </div>

        <div class="table-responsive shadow-sm rounded bg-white p-3">
            <table class="table table-bordered table-hover align-middle" id="documentsTable">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width:5%;">No</th>
                        <th class="text-center d-none" style="width:5%;">Document ID</th>
                        <th style="width:12%;">Faculty</th>
                        <th style="width:10%;">Unit</th>
                        <th style="width:15%;">Document Name</th>
                        <th style="width:10%;">Document Number</th>
                        <th class="text-center" style="width:8%;">Revision</th>
                        <th style="width:10%;">Type</th>
                        <th style="width:12%;">Code & Name</th>
                        <th style="width:10%;">File</th>
                        <th style="width:10%;">Remark</th>
                        <th style="width:8%;">Created By</th>
                        <th class="text-center" style="width:8%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $hasVisibleDocuments = false; if (!empty($documents)): $no = 1; foreach ($documents as $doc): 
                        $documentCreatorId = $doc['createdby'] ?? 0;
                        $documentCreatorUnitId = $doc['creator_unit_id'] ?? 0;
                        $documentCreatorUnitParentId = $doc['creator_unit_parent_id'] ?? 0;
                        $documentCreatorAccessLevel = $doc['creator_access_level'] ?? 2;

                        $canViewDocument = false;
                        $showCreatorName = false;

                        if ($documentCreatorId == $currentUserId) {
                            $canViewDocument = true;
                            $showCreatorName = true;
                        } elseif ($currentUserAccessLevel == 1 && $documentCreatorAccessLevel == 2) {
                            if ($documentCreatorUnitId == $currentUserUnitId || $documentCreatorUnitParentId == $currentUserUnitParentId) {
                                $canViewDocument = true;
                                $showCreatorName = true;
                            }
                        }

                        if (!$canViewDocument) continue;
                        $hasVisibleDocuments = true;
                    ?>
                    <tr data-document-id="<?= esc($doc['id']) ?>">
                        <td class="text-center"><?= $no++ ?></td>
                        <td class="text-center d-none"><?= esc($doc['id']) ?></td>
                        <td data-fakultas="<?= esc($doc['unit_parent_id'] ?? '') ?>"><?= esc($doc['parent_name'] ?? '-') ?></td>
                        <td><?= esc($doc['unit_name'] ?? '-') ?></td>
                        <td><div class="text-truncate" style="max-width: 200px;" title="<?= esc($doc['title'] ?? '') ?>"><?= esc($doc['title'] ?? '-') ?></div></td>
                        <td><?= esc($doc['number'] ?? '-') ?></td>
                        <td class="text-center"><?= esc($doc['revision'] ?? 'Rev. 0') ?></td>
                        <td data-jenis="<?= esc($doc['type'] ?? '') ?>">
                            <?php $jenis_dokumen = $doc['jenis_dokumen'] ?? '-'; $badgeClass = isset($jenisColorMap[$jenis_dokumen]) ? $jenisColorMap[$jenis_dokumen] : 'bg-secondary'; ?>
                            <span class="badge <?= $badgeClass ?>"><?= esc($jenis_dokumen) ?></span>
                        </td>
                        <td><div class="text-truncate" style="max-width: 150px;" title="<?= esc($doc['kode_dokumen_kode'] . ' - ' . $doc['kode_dokumen_nama']) ?>"><?= esc($doc['kode_dokumen_kode'] . ' - ' . $doc['kode_dokumen_nama'] ?? '-') ?></div></td>
                        <td><?php if (!empty($doc['filepath'])): ?><div class="d-flex gap-2"><a href="<?= base_url('document-approval/serveFile?id=' . $doc['id']) ?>" class="text-decoration-none" title="Download file"><i class="bi bi-download text-success fs-5"></i></a></div><?php else: ?><span class="text-muted"><i class="bi bi-file-earmark-x"></i> No file</span><?php endif; ?></td>
                        <td><div class="text-truncate" style="max-width: 150px;" title="<?= esc($doc['approval_remark'] ?? '') ?>"><?= esc($doc['approval_remark'] ?? '-') ?></div></td>
                        <td><?php if ($showCreatorName): ?><div class="text-truncate" style="max-width: 100px;" title="<?= esc($doc['creator_name'] ?? '-') ?>"><?= esc($doc['creator_name'] ?? '-') ?></div><?php else: ?><span class="text-muted">-</span><?php endif; ?></td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <button class="btn btn-sm btn-outline-info view-history-btn" data-bs-toggle="modal" data-bs-target="#historyModal" data-id="<?= $doc['id'] ?? '' ?>" title="View History"><i class="bi bi-eye"></i></button>
                                <?php if ($documentSubmissionPrivileges['can_update'] && ($documentCreatorId == $currentUserId || ($currentUserAccessLevel == 1 && $documentCreatorAccessLevel == 2))): ?>
                                <button class="btn btn-sm btn-outline-primary edit-btn" data-id="<?= esc($doc['id'] ?? '') ?>" data-nama="<?= esc($doc['title'] ?? '') ?>" data-revisi="<?= esc($doc['revision'] ?? 'Rev. 0') ?>" data-keterangan="<?= esc($doc['approval_remark'] ?? '') ?>" data-filepath="<?= esc($doc['filepath'] ?? '') ?>" data-filename="<?= esc($doc['filename'] ?? '') ?>" title="Edit"><i class="bi bi-pencil-square"></i></button>
                                <?php endif; ?>
                                <?php if ($documentSubmissionPrivileges['can_delete'] && ($documentCreatorId == $currentUserId || ($currentUserAccessLevel == 1 && $documentCreatorAccessLevel == 2))): ?>
                                <button class="btn btn-sm btn-outline-danger delete-document" data-id="<?= esc($doc['id'] ?? '') ?>" title="Delete"><i class="bi bi-trash"></i></button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                    <?php if (!$hasVisibleDocuments): ?><tr class="empty-row"><td class="text-center" colspan="13">No documents available</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($documentSubmissionPrivileges['can_update']): ?>
        <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Document</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="editDocumentForm" action="<?= base_url('document-approval/update') ?>" method="post" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <div class="modal-body">
                            <input type="hidden" id="editDocumentId" name="document_id">
                            <div class="mb-3">
                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" name="nama" id="editDocumentTitle" class="form-control" required>
                                <div class="invalid-feedback">Title is required.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Revision <span class="text-danger">*</span></label>
                                <input type="text" name="revisi" id="editDocumentRevision" class="form-control" required>
                                <div class="invalid-feedback">Revision is required.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Remark</label>
                                <textarea name="keterangan" id="editDocumentRemark" class="form-control" rows="3" placeholder="Enter remarks (optional)"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Current File</label>
                                <table class="table table-bordered">
                                    <thead><tr><th>File Name</th><th>Action</th></tr></thead>
                                    <tbody><tr id="currentFileInfo"><td id="currentFileName" class="text-truncate" style="max-width: 200px;"></td><td class="text-center"><a id="currentFileLink" href="#" class="text-decoration-none d-none" title="Download File"><i class="bi bi-download text-success"></i> Download Document</a><span id="noFileMessage" class="text-muted">No file uploaded</span></td></tr></tbody>
                                </table>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Upload New File (optional)</label>
                                <input type="file" name="file_dokumen" id="editDocumentFile" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                                <div class="invalid-feedback">Invalid file type. Allowed: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX.</div>
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
        <?php endif; ?>

        <div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-sm">
                    <div class="modal-header border-bottom-0 pb-2">
                        <h5 class="modal-title fw-bold" id="historyModalLabel">Document Revision History</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-4">
                            <h6 class="fw-bold">Document Information</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Document Name</label>
                                    <p id="historyNamaDokumen" class="mb-0">-</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Document Type</label>
                                    <p id="historyJenisDokumen" class="mb-0">-</p>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle" id="historyTable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width: 5%;">No</th>
                                        <th class="text-center d-none" style="width: 10%;">Revision ID</th>
                                        <th style="width: 25%;">Document Name</th>
                                        <th style="width: 20%;">Document Number</th>
                                        <th style="width: 10%;">File</th>
                                        <th class="text-center" style="width: 15%;">Revision</th>
                                        <th style="width: 30%;">Date</th>
                                    </tr>
                                </thead>
                                <tbody id="historyTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
tr.document-highlight { background-color: #d3d3d3 !important; transition: background-color 0.3s ease; }
.action-buttons .btn-action { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; padding: 0; }
.action-buttons .btn-action i { font-size: 14px; }
.text-truncate-custom { display: inline-block; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; vertical-align: middle; }
.form-control.is-invalid, .form-select.is-invalid { border-color: #dc3545; padding-right: calc(1.5em + 0.75rem); background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 5.8-3.6-3.6m0 0 3.6 3.6m-3.6-3.6 3.6 3.6'/%3e%3c/svg%3e"); background-repeat: no-repeat; background-position: right calc(0.375em + 0.1875rem) center; background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem); }
.form-control.is-valid, .form-select.is-valid { border-color: #198754; padding-right: calc(1.5em + 0.75rem); background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='m2.3 6.73.94-.94 1.44 1.44L2.3 6.73z'/%3e%3cpath fill='%23198754' d='m6.564.75-3.59 3.612-1.538-1.55L0 4.25 2.974 7.25 8 2.193z'/%3e%3c/svg%3e"); background-repeat: no-repeat; background-position: right calc(0.375em + 0.1875rem) center; background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem); }
.btn.loading { pointer-events: none; }
.btn.loading .spinner-border { width: 1rem; height: 1rem; }
#currentFileInfo .table { margin-bottom: 0; }
#currentFileInfo .table th, #currentFileInfo .table td { vertical-align: middle; }
#historyTable { margin-bottom: 0; }
#historyTable th, #historyTable th { vertical-align: middle; }
#historyTable .text-truncate { max-width: 200px; }
#exportExcel, #exportPDF { min-width: 80px; }
#customSearch { border-radius: 0.375rem 0 0 0.375rem; }
#searchBtn { border-radius: 0 0.375rem 0.375rem 0; border-left: 0; }
.dataTables_filter { display: none !important; }
</style>

<script>
var documentPrivileges = <?= json_encode($documentSubmissionPrivileges) ?>;
var currentUserAccessLevel = <?= $currentUserAccessLevel ?>;
var currentUserId = <?= $currentUserId ?>;

$(document).ready(function() {
    const table = $('#documentsTable').DataTable({
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        paging: true,
        searching: true,
        ordering: true,
        dom: '<"row"<"col-sm-6"l><"col-sm-6">>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-6"i><"col-sm-6 text-end"p>>',
        buttons: [{
            extend: 'excelHtml5',
            text: '<i class="bi bi-file-earmark-excel"></i> Excel',
            className: 'btn btn-success btn-sm',
            title: 'Document Approval - ' + new Date().toLocaleDateString(),
            exportOptions: { columns: [0, 2, 3, 4, 5, 6, 7, 8, 10, 11] },
            customize: function(xlsx) {}
        }, {
            extend: 'pdfHtml5',
            text: '<i class="bi bi-file-earmark-pdf"></i> PDF',
            className: 'btn btn-danger btn-sm',
            title: 'Document Approval',
            orientation: 'landscape',
            pageSize: 'A4',
            exportOptions: { columns: [0, 2, 3, 4, 5, 6, 7, 8, 10, 11] },
            customize: function(doc) {
                doc.content[1].table.widths = ['5%', '12%', '12%', '15%', '10%', '8%', '10%', '12%', '10%', '8%'];
                doc.styles.tableHeader.fontSize = 8;
                doc.defaultStyle.fontSize = 7;
                doc.pageMargins = [20, 20, 20, 20];
            }
        }],
        language: {
            lengthMenu: "Show _MENU_ entries",
            zeroRecords: "No data found",
            search: "Search:",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "Showing 0 to 0 of 0 entries",
            infoFiltered: "(filtered from _MAX_ total entries)",
            paginate: { first: "First", last: "Last", next: "Next", previous: "Previous" }
        },
        columnDefs: [{ targets: 0, searchable: false, orderable: false }, { targets: 1, visible: false, searchable: true, orderable: true }, { targets: 12, orderable: false, searchable: false }],
        responsive: true,
        autoWidth: false,
        order: [[0, 'desc']],
        drawCallback: function(settings) {
            var api = this.api();
            var pageInfo = api.page.info();
            if (pageInfo.recordsDisplay > 0) {
                var startRow = pageInfo.start;
                $('#documentsTable tbody tr').each(function(index) {
                    if ($(this).hasClass('empty-row') || ($(this).hasClass('odd') && $(this).find('td').length === 1)) return;
                    $(this).find('td:first').text(startRow + index + 1);
                });
            }
        }
    });

    $('#customSearch').on('keyup', function() { table.search(this.value).draw(); });
    $('#searchBtn').on('click', function() { table.search($('#customSearch').val()).draw(); });
    $('#customSearch').on('keypress', function(e) { if (e.which === 13) table.search(this.value).draw(); });

    $('#exportExcel').on('click', function() {
        $(this).html('<span class="spinner-border spinner-border-sm" role="status"></span> Processing...');
        $(this).prop('disabled', true);
        table.button('.buttons-excel').trigger();
        setTimeout(() => { $(this).html('<i class="bi bi-file-earmark-excel"></i> Excel'); $(this).prop('disabled', false); }, 1000);
    });

    $('#exportPDF').on('click', function() {
        $(this).html('<span class="spinner-border spinner-border-sm" role="status"></span> Processing...');
        $(this).prop('disabled', true);
        table.button('.buttons-pdf').trigger();
        setTimeout(() => { $(this).html('<i class="bi bi-file-earmark-pdf"></i> PDF'); $(this).prop('disabled', false); }, 1000);
    });

    function filterAndHighlightDocumentFromUrl() {
        const urlParams = new URLSearchParams(window.location.search);
        const documentId = urlParams.get('document_id');
        const revisionId = urlParams.get('revision_id');

        if (documentId) {
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                const row = table.row(dataIndex);
                const rowDocumentId = row.node().getAttribute('data-document-id') || '';
                return rowDocumentId === documentId;
            });
            table.draw();
            $.fn.dataTable.ext.search.pop();

            let targetRow = null;
            table.rows().every(function() {
                const rowNode = this.node();
                if ($(rowNode).data('document-id') == documentId) {
                    targetRow = rowNode;
                    return false;
                }
            });

            if (targetRow) {
                $('#documentsTable tbody tr').removeClass('document-highlight');
                $(targetRow).addClass('document-highlight');
                setTimeout(() => {
                    const $row = $(targetRow);
                    if ($row.length) {
                        $('html, body').animate({ scrollTop: $row.offset().top - 100 }, 500);
                        setTimeout(() => { $row.removeClass('document-highlight'); }, 5000);
                    }
                }, 100);
            }
        }

        if (revisionId) {
            $.ajax({
                url: '<?= base_url("document-approval") ?>?action=get-history&id=' + (documentId || '0'),
                type: 'GET',
                dataType: 'json',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    if (response.success && response.data && response.data.history && response.data.history.length > 0) {
                        $('#historyNamaDokumen').text(response.data.document.title || '-');
                        $('#historyJenisDokumen').text(response.data.document.jenis_dokumen || '-');
                        let html = '';
                        const reversedHistory = response.data.history.slice().reverse();
                        reversedHistory.forEach((item, index) => {
                            const fileLink = item.filepath ? `<div class="d-flex gap-2"><a href="<?= base_url('document-approval/serveFile') ?>?id=${item.document_id}" class="text-decoration-none" title="Download file"><i class="bi bi-download text-success fs-5"></i></a></div>` : '<span class="text-muted"><i class="bi bi-file-earmark-x"></i> No file</span>';
                            html += `<tr data-revision-id="${item.revision_id}" ${item.revision_id == revisionId ? 'class="document-highlight"' : ''}><td class="text-center">${index + 1}</td><td class="text-center d-none">${item.revision_id}</td><td>${item.document_title || '-'}</td><td>${item.document_number || '-'}</td><td>${fileLink}</td><td class="text-center">${item.revision || 'Rev. 0'}</td><td>${formatDate(item.updated_at)}</td></tr>`;
                        });
                        $('#historyTableBody').html(html);
                        const historyModal = new bootstrap.Modal(document.getElementById('historyModal'), { backdrop: 'static', keyboard: false });
                        historyModal.show();
                        setTimeout(() => {
                            const $highlightedRow = $('#historyTableBody tr.document-highlight');
                            if ($highlightedRow.length) {
                                $highlightedRow[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                                setTimeout(() => { $highlightedRow.removeClass('document-highlight'); }, 5000);
                            }
                        }, 500);
                    } else {
                        Swal.fire({ icon: 'warning', title: 'Document Not Found', text: 'Revision with ID ' + revisionId + ' not found or you do not have access.', confirmButtonColor: '#dc3545' });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error checking document history:', error);
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to check document history. Please try again.', confirmButtonColor: '#dc3545' });
                }
            });
        }
    }

    filterAndHighlightDocumentFromUrl();
    table.on('draw', filterAndHighlightDocumentFromUrl);

    if (documentPrivileges.can_update) {
        $(document).on('click', '.edit-btn', function(e) {
            e.preventDefault();
            const editBtn = $(this);
            Swal.fire({
                title: 'Edit Document',
                text: 'Are you sure you want to edit this document?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Edit!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#editDocumentId').val(editBtn.data('id'));
                    $('#editDocumentTitle').val(editBtn.data('nama'));
                    $('#editDocumentRevision').val(editBtn.data('revisi'));
                    $('#editDocumentRemark').val(editBtn.data('keterangan'));
                    $('#editDocumentFile').val('');

                    const filepath = editBtn.data('filepath');
                    const filename = editBtn.data('filename');
                    if (filename && filepath) {
                        $('#currentFileName').text(filename);
                        $('#currentFileLink').attr('href', '<?= base_url('document-approval/serveFile') ?>?id=' + editBtn.data('id')).removeClass('d-none');
                        $('#noFileMessage').addClass('d-none');
                    } else {
                        $('#currentFileName').text('No file uploaded');
                        $('#currentFileLink').addClass('d-none');
                        $('#noFileMessage').removeClass('d-none');
                    }

                    $('.form-control, .form-select').removeClass('is-valid is-invalid');
                    $('.invalid-feedback').hide();
                    $('#saveChangesBtn').removeClass('loading').find('.spinner-border').addClass('d-none').prop('disabled', false);

                    const editModal = new bootstrap.Modal(document.getElementById('editModal'), { backdrop: 'static', keyboard: false });
                    editModal.show();
                }
            });
        });

        function validateForm() {
            let isValid = true;
            $('.form-control, .form-select').removeClass('is-valid is-invalid');
            const title = $('#editDocumentTitle').val().trim();
            const revision = $('#editDocumentRevision').val().trim();
            const fileInput = $('#editDocumentFile')[0].files[0];

            if (!title) { $('#editDocumentTitle').addClass('is-invalid'); isValid = false; }
            else { $('#editDocumentTitle').addClass('is-valid'); }
            if (!revision) { $('#editDocumentRevision').addClass('is-invalid'); isValid = false; }
            else { $('#editDocumentRevision').addClass('is-valid'); }
            if (fileInput) {
                const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'];
                if (!allowedTypes.includes(fileInput.type)) { $('#editDocumentFile').addClass('is-invalid'); isValid = false; }
                else { $('#editDocumentFile').addClass('is-valid'); }
            }
            if ($('#editDocumentRemark').val().trim()) { $('#editDocumentRemark').addClass('is-valid'); }
            return isValid;
        }

        $('#editDocumentTitle, #editDocumentRevision').on('input', function() {
            const value = $(this).val().trim();
            $(this).removeClass('is-valid is-invalid');
            if (value) $(this).addClass('is-valid');
            else $(this).addClass('is-invalid');
        });

        $('#editDocumentFile').on('change', function() {
            const fileInput = this.files[0];
            $(this).removeClass('is-valid is-invalid');
            if (fileInput) {
                const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'];
                if (allowedTypes.includes(fileInput.type)) $(this).addClass('is-valid');
                else $(this).addClass('is-invalid');
            }
        });

        $('#editDocumentForm').on('submit', function(e) {
            e.preventDefault();
            if (!validateForm()) {
                Swal.fire({ icon: 'warning', title: 'Validation Error', text: 'Please fill in all required fields correctly and ensure the file type is valid.', confirmButtonColor: '#dc3545' });
                return;
            }

            const formData = new FormData(this);
            const saveBtn = $('#saveChangesBtn');
            saveBtn.addClass('loading').find('.spinner-border').removeClass('d-none').prop('disabled', true);

            $.ajax({
                url: '<?= base_url('document-approval/update') ?>',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    const editModal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
                    editModal.hide();
                    if (response.success) {
                        Swal.fire({ icon: 'success', title: 'Success', text: response.message, confirmButtonColor: '#198754' }).then(() => {
                            location.reload(); // Auto-reload setelah sukses
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: response.message, confirmButtonColor: '#dc3545' });
                    }
                },
                error: function(xhr) {
                    console.error('Update error:', xhr);
                    let errorMessage = 'An error occurred while updating the document.';
                    if (xhr.responseJSON && xhr.responseJSON.message) errorMessage = xhr.responseJSON.message;
                    else if (xhr.status === 403) errorMessage = 'You do not have permission to update this document.';
                    else if (xhr.status === 404) errorMessage = 'Document not found.';
                    else if (xhr.status === 500) errorMessage = 'Server error occurred. Please try again later.';
                    Swal.fire({ icon: 'error', title: 'Update Failed', text: errorMessage, confirmButtonColor: '#dc3545' });
                },
                complete: function() {
                    saveBtn.removeClass('loading').find('.spinner-border').addClass('d-none').prop('disabled', false);
                }
            });
        });

        $('#editModal').on('hidden.bs.modal', function() {
            $('#editDocumentForm')[0].reset();
            $('.form-control, .form-select').removeClass('is-valid is-invalid');
            $('#currentFileName').text('No file uploaded');
            $('#currentFileLink').addClass('d-none');
            $('#noFileMessage').removeClass('d-none');
            $('#saveChangesBtn').removeClass('loading').find('.spinner-border').addClass('d-none').prop('disabled', false);
        });
    }

    if (documentPrivileges.can_delete) {
        $(document).on('click', '.delete-document', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            Swal.fire({
                title: 'Delete Document',
                text: 'Are you sure you want to delete this document? This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Delete!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Deleting Document...', text: 'Please wait a moment', allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false, didOpen: () => Swal.showLoading() });
                    $.ajax({
                        url: '<?= base_url("document-approval/delete") ?>',
                        type: 'POST',
                        data: { document_id: id, '<?= csrf_token() ?>': '<?= csrf_hash() ?>' },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({ icon: 'success', title: 'Successfully Deleted!', text: response.message, confirmButtonColor: '#28a745' }).then(() => location.reload());
                            } else {
                                Swal.fire({ icon: 'error', title: 'Failed to Delete!', text: response.message, confirmButtonColor: '#dc3545' });
                            }
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({ icon: 'error', title: 'Server Error!', text: 'A server error occurred. Please try again later.', confirmButtonColor: '#dc3545' });
                        }
                    });
                }
            });
        });
    }

    $(document).on('click', '.view-history-btn', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        $('#historyNamaDokumen').text('-');
        $('#historyJenisDokumen').text('-');
        $('#historyTableBody').html('<tr><td colspan="7" class="text-center">Loading data...</td></tr>');
        $.ajax({
            url: '<?= base_url("document-approval") ?>?action=get-history&id=' + id,
            type: 'GET',
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
                if (response.success && response.data) {
                    $('#historyNamaDokumen').text(response.data.document.title || '-');
                    $('#historyJenisDokumen').text(response.data.document.jenis_dokumen || '-');
                    let html = '';
                    if (response.data.history && response.data.history.length > 0) {
                        const reversedHistory = response.data.history.slice().reverse();
                        reversedHistory.forEach((item, index) => {
                            const fileLink = item.filepath ? `<div class="d-flex gap-2"><a href="<?= base_url('document-approval/serveFile') ?>?id=${item.document_id}" class="text-decoration-none" title="Download file"><i class="bi bi-download text-success fs-5"></i></a></div>` : '<span class="text-muted"><i class="bi bi-file-earmark-x"></i> No file</span>';
                            html += `<tr data-revision-id="${item.revision_id}"><td class="text-center">${index + 1}</td><td class="text-center d-none">${item.revision_id}</td><td>${item.document_title || '-'}</td><td>${item.document_number || '-'}</td><td>${fileLink}</td><td class="text-center">${item.revision || 'Rev. 0'}</td><td>${formatDate(item.updated_at)}</td></tr>`;
                        });
                    } else {
                        html = '<tr><td colspan="7" class="text-center text-muted">No revision history available</td></tr>';
                    }
                    $('#historyTableBody').html(html);
                } else {
                    $('#historyTableBody').html('<tr><td colspan="7" class="text-center text-muted">Failed to load revision history</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX error:', error, xhr.responseText);
                $('#historyTableBody').html('<tr><td colspan="7" class="text-center text-muted">Error loading revision history</td></tr>');
            }
        });
    });

    $('#historyModal').on('hidden.bs.modal', function() {
        $('#historyNamaDokumen').text('-');
        $('#historyJenisDokumen').text('-');
        $('#historyTableBody').html('');
    });
});

function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}
</script>

<?= $this->endSection() ?>
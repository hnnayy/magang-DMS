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

// Get privileges from session
$privileges = session()->get('privileges') ?? [];
$documentSubmissionPrivileges = $privileges['document-submission-list'] ?? [
    'can_create' => 0,
    'can_update' => 0,
    'can_delete' => 0,
    'can_approve' => 0
];

// Define a color palette for document types
$badgeColors = [
    'bg-primary',
    'bg-success',
    'bg-info',
    'bg-warning',
    'bg-danger',
    'bg-secondary',
    'bg-dark',
    'bg-primary-subtle',
    'bg-success-subtle',
    'bg-info-subtle'
];

// Get unique document types, sort them, and assign colors
$uniqueJenisDokumen = array_unique(array_column($documents, 'jenis_dokumen'));
sort($uniqueJenisDokumen); // Sort to ensure consistent order
$jenisColorMap = [];
foreach ($uniqueJenisDokumen as $index => $jenis) {
    $jenisColorMap[$jenis] = $badgeColors[$index % count($badgeColors)];
}
?>

<div class="container-fluid">
    <div class="px-4 py-3">
        <h4 class="mb-4">Document Submission List</h4>

        <!-- Enhanced Flash message -->
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

        <!-- Filter card -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="searchInput" class="form-label">Search Document</label>
                        <input type="text" class="form-control" placeholder="Search documents..." id="searchInput">
                    </div>
                    <div class="col-md-3">
                        <label for="filterFakultas" class="form-label">Filter Faculty</label>
                        <select class="form-select" id="filterFakultas">
                            <option value="">All Faculties</option>
                            <?php foreach ($fakultas_list as $fakultas): ?>
                                <option value="<?= esc($fakultas['id']) ?>"><?= esc($fakultas['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filterJenis" class="form-label">Filter Type</label>
                        <select class="form-select" id="filterJenis">
                            <option value="">All Types</option>
                            <?php foreach ($kategori_dokumen as $kategori): ?>
                                <option value="<?= esc($kategori['id']) ?>"><?= esc($kategori['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button id="resetButton" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
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
                        <th style="width:10%;">Description</th>
                        <th style="width:8%;">Created By</th>
                        <th class="text-center" style="width:8%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($documents)): ?>
                        <?php $no = 1; ?>
                        <?php foreach ($documents as $doc): ?>
                            <?php 
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
                                $sameUnit = ($documentCreatorUnitId == $currentUserUnitId);
                                $sameUnitParent = ($documentCreatorUnitParentId == $currentUserUnitParentId);
                                $creatorIsSubordinate = ($documentCreatorUnitParentId == $currentUserUnitId);
                                $inSameHierarchy = $sameUnit || $sameUnitParent || $creatorIsSubordinate;
                                
                                if ($inSameHierarchy) {
                                    $canViewDocument = true;
                                    $showCreatorName = true;
                                }
                            }
                            
                            if (!$canViewDocument) continue;
                            ?>
                            
                            <?php if ($documentCreatorId != 0): ?>
                            <tr data-document-id="<?= esc($doc['id']) ?>">
                                <td class="text-center"><?= $no++ ?></td>
                                <td class="text-center d-none"><?= esc($doc['id']) ?></td>
                                <td data-fakultas="<?= esc($doc['unit_parent_id'] ?? '') ?>">
                                    <?= esc($doc['parent_name'] ?? '-') ?>
                                </td>
                                <td><?= esc($doc['unit_name'] ?? '-') ?></td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;" title="<?= esc($doc['title'] ?? '') ?>">
                                        <?= esc($doc['title'] ?? '-') ?>
                                    </div>
                                </td>
                                <td><?= esc($doc['number'] ?? '-') ?></td>
                                <td class="text-center"><?= esc($doc['revision'] ?? 'Rev. 0') ?></td>
                                <td data-jenis="<?= esc($doc['type'] ?? '') ?>">
                                    <?php
                                        $jenis_dokumen = $doc['jenis_dokumen'] ?? '-';
                                        $badgeClass = isset($jenisColorMap[$jenis_dokumen]) ? $jenisColorMap[$jenis_dokumen] : 'bg-secondary';
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= esc($jenis_dokumen) ?></span>
                                </td>
                                <td>
                                    <?php 
                                    $usePredefined = false;
                                    foreach ($kategori_dokumen as $kategori) {
                                        if ($kategori['id'] == $doc['type']) {
                                            $usePredefined = $kategori['use_predefined_codes'];
                                            break;
                                        }
                                    }
                                    ?>
                                    <?php if (!empty($doc['kode_dokumen_kode']) && !empty($doc['kode_dokumen_nama'])): ?>
                                        <div class="text-truncate" style="max-width: 150px;" title="<?= esc($doc['kode_dokumen_kode'] . ' - ' . $doc['kode_dokumen_nama']) ?>">
                                            <?= esc($doc['kode_dokumen_kode']) ?> - <?= esc($doc['kode_dokumen_nama']) ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($doc['filepath'])): ?>
                                        <div class="d-flex gap-2">
                                            <a href="<?= base_url('document-submission-list?action=download-file&id=' . $doc['id']) ?>" 
                                               class="text-decoration-none" 
                                               title="Download file">
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
                                    <div class="text-truncate" style="max-width: 150px;" title="<?= esc($doc['description'] ?? '') ?>">
                                        <?= esc($doc['description'] ?? '-') ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($showCreatorName): ?>
                                        <div class="text-truncate" style="max-width: 100px;" title="<?= esc($doc['creator_name'] ?? '-') ?>">
                                            <?= esc($doc['creator_name'] ?? '-') ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <button class="btn btn-sm btn-outline-info view-history-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#historyModal"
                                            data-id="<?= $doc['id'] ?? '' ?>"
                                            title="View History">
                                            <i class="bi bi-eye"></i>
                                        </button>

                                        <?php if ($documentSubmissionPrivileges['can_update'] && 
                                                 ($documentCreatorId == $currentUserId || 
                                                  ($currentUserAccessLevel == 1 && $documentCreatorAccessLevel == 2))): ?>
                                        <button class="btn btn-sm btn-outline-primary edit-btn"
                                            data-id="<?= esc($doc['id'] ?? '') ?>"
                                            data-fakultas="<?= esc($doc['parent_name'] ?? '-') ?>"
                                            data-unit="<?= esc($doc['unit_name'] ?? '-') ?>"
                                            data-nama="<?= esc($doc['title'] ?? '') ?>"
                                            data-nomor="<?= esc($doc['number'] ?? '') ?>"
                                            data-revisi="<?= esc($doc['revision'] ?? 'Rev. 0') ?>"
                                            data-jenis="<?= esc($doc['type'] ?? '') ?>"
                                            data-keterangan="<?= esc($doc['description'] ?? '') ?>"
                                            data-kode-dokumen-id="<?= esc($doc['kode_dokumen_id'] ?? '') ?>"
                                            data-kode-dokumen-kode="<?= esc($doc['kode_dokumen_kode'] ?? '') ?>"
                                            data-kode-dokumen-nama="<?= esc($doc['kode_dokumen_nama'] ?? '') ?>"
                                            data-filepath="<?= esc($doc['filepath'] ?? '') ?>"
                                            data-filename="<?= esc($doc['filename'] ?? '') ?>"
                                            data-status="<?= esc($doc['status'] ?? 0) ?>"
                                            data-use-predefined="<?= $usePredefined ? 'true' : 'false' ?>"
                                            title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <?php endif; ?>

                                        <?php if ($documentSubmissionPrivileges['can_approve'] && 
                                                 $currentUserAccessLevel == 1 && 
                                                 $documentCreatorAccessLevel == 2): ?>
                                        <button class="btn btn-sm btn-outline-success approve-btn"
                                            data-id="<?= esc($doc['id'] ?? '') ?>"
                                            data-status="<?= esc($doc['status'] ?? 0) ?>"
                                            title="Approve">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                        <?php endif; ?>

                                        <?php if ($documentSubmissionPrivileges['can_delete'] && 
                                                 ($documentCreatorId == $currentUserId || 
                                                  ($currentUserAccessLevel == 1 && $documentCreatorAccessLevel == 2))): ?>
                                        <button class="btn btn-sm btn-outline-danger delete-document" 
                                            data-id="<?= esc($doc['id'] ?? '') ?>" 
                                            title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="12" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                No document submissions available for your access level.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Edit Modal -->
        <?php if ($documentSubmissionPrivileges['can_update']): ?>
        <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-sm">
                    <form action="<?= base_url('document-submission-list/update') ?>" method="post" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <input type="hidden" name="document_id" id="editDocumentId">
                        <div class="modal-header border-bottom-0 pb-2">
                            <h5 class="modal-title fw-bold" id="editModalLabel">Edit Document</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="editFakultas" class="form-label">Faculty</label>
                                    <input type="text" class="form-control" id="editFakultas" name="fakultas" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label for="editBagian" class="form-label">Unit</label>
                                    <input type="text" class="form-control" id="editBagian" name="bagian" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label for="editNama" class="form-label">Document Name</label>
                                    <input type="text" class="form-control" name="nama" id="editNama">
                                </div>
                                <div class="col-md-3">
                                    <label for="editNomor" class="form-label">Document Number</label>
                                    <input type="text" class="form-control" name="nomor" id="editNomor">
                                </div>
                                <div class="col-md-3">
                                    <label for="editRevisi" class="form-label">Revision</label>
                                    <input type="text" class="form-control" name="revisi" id="editRevisi">
                                </div>
                                <div class="col-md-12">
                                    <label for="editJenis" class="form-label">Document Type</label>
                                    <select name="type" class="form-select" id="editJenis" onchange="handleEditJenisChange()">
                                        <option value="">-- Select Document Type --</option>
                                        <?php foreach ($kategori_dokumen as $kategori): ?>
                                            <option value="<?= esc($kategori['id']) ?>" 
                                                    data-kode="<?= esc($kategori['kode']) ?>" 
                                                    data-use-predefined="<?= $kategori['use_predefined_codes'] ? 'true' : 'false' ?>">
                                                <?= esc($kategori['nama']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-12" id="editKodeGroup" style="display: none;">
                                    <label for="editNamaKode" class="form-label">Code - Document Name</label>
                                    <select name="kode_dokumen" id="editNamaKode" class="form-select">
                                        <option value="">-- Select Document Code --</option>
                                    </select>
                                    <small class="text-muted">Please select document type first</small>
                                </div>
                                <div id="editKodeCustomGroup" style="display: none;">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label for="editKodeCustom" class="form-label">Document Code</label>
                                            <input type="text" id="editKodeCustom" name="kode_dokumen_custom" class="form-control" 
                                                   style="text-transform: uppercase;">
                                        </div>
                                        <div class="col-md-8">
                                            <label for="editNamaCustom" class="form-label">Detailed Document Name</label>
                                            <input type="text" id="editNamaCustom" name="nama_dokumen_custom" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="editKeterangan" class="form-label">Description</label>
                                    <textarea class="form-control" name="keterangan" id="editKeterangan" rows="3"></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Document File</label>
                                    <input type="file" class="form-control" name="file_dokumen" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                                    <div id="currentFileInfo" class="mt-2" style="display: none;">
                                        <div class="d-flex align-items-center text-muted">
                                            <i class="bi bi-file-earmark-text me-2"></i>
                                            <div>
                                                <strong>Current file:</strong> <span id="currentFileName">-</span><br>
                                                <small>Upload a new file to replace the existing one</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 pt-3">
                            <div class="d-flex gap-2 w-100">
                                <button type="button" class="btn btn-secondary flex-grow-1" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary flex-grow-1">Save Changes</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- History Modal -->
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
                                        <th style="width: 20%;">Document Name</th>
                                        <th style="width: 15%;">Document Number</th>
                                        <th style="width: 15%;">File</th>
                                        <th class="text-center" style="width: 15%;">Revision</th>
                                        <th style="width: 20%;">Date</th>
                                        <th style="width: 10%;">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="historyTableBody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approve Modal -->
        <?php if ($documentSubmissionPrivileges['can_approve']): ?>
        <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-sm">
                    <form action="<?= base_url('document-submission-list/approve') ?>" method="post" id="approveForm">
                        <?= csrf_field() ?>
                        <input type="hidden" name="document_id" id="approveDocumentId">
                        <div class="modal-header border-bottom-0 pb-2">
                            <h5 class="modal-title fw-bold">Document Approval</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="approved_by_display" class="form-label">
                                    Approver Name <span class="text-danger">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="approved_by_display" 
                                    value="<?= esc(session()->get('fullname')) ?>" 
                                    readonly
                                >
                                <input 
                                    type="hidden" 
                                    name="approved_by" 
                                    value="<?= esc(session()->get('user_id')) ?>"
                                >
                            </div>
                            <div class="mb-3">
                                <label for="approval_date" class="form-label">Approval Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="approval_date" id="approval_date" required>
                            </div>
                            <div class="mb-3">
                                <label for="remarks" class="form-label">Remarks</label>
                                <textarea class="form-control" name="remarks" id="remarks" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 pt-0">
                            <div class="row w-100 g-2">
                                <div class="col-6">
                                    <button type="submit" name="action" value="disapprove" class="btn w-100 text-white" style="background-color: #dc3545;">Disapprove</button>
                                </div>
                                <div class="col-6">
                                    <button type="submit" name="action" value="approve" class="btn btn-success w-100">Approve</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
tr.document-highlight {
    background-color: #d3d3d3 !important;
    transition: background-color 0.3s ease;
}
</style>

<script>
// Global variables
var kodeDokumenByType = <?= json_encode($kode_dokumen_by_type ?? []) ?>;
var documentPrivileges = <?= json_encode($documentSubmissionPrivileges) ?>;
var currentUserAccessLevel = <?= $currentUserAccessLevel ?>;
var currentUserId = <?= $currentUserId ?>;

function handleEditJenisChange() {
    const jenisSelect = document.getElementById('editJenis');
    const selectedOption = jenisSelect.options[jenisSelect.selectedIndex];
    const usePredefined = selectedOption.getAttribute('data-use-predefined') === 'true';
    const jenisId = jenisSelect.value;

    const kodeGroup = document.getElementById('editKodeGroup');
    const kodeCustomGroup = document.getElementById('editKodeCustomGroup');
    const kodeSelect = document.getElementById('editNamaKode');

    if (jenisSelect.value === '') {
        kodeGroup.style.display = 'none';
        kodeCustomGroup.style.display = 'none';
        return;
    }

    if (usePredefined) {
        kodeGroup.style.display = 'block';
        kodeCustomGroup.style.display = 'none';
        loadEditKodeDokumen(jenisId);
    } else {
        kodeGroup.style.display = 'none';
        kodeCustomGroup.style.display = 'block';
        kodeSelect.disabled = false;
        return;
    }
}

function loadEditKodeDokumen(jenisId) {
    const kodeSelect = document.getElementById('editNamaKode');
    kodeSelect.innerHTML = '<option value="">-- Loading... --</option>';
    kodeSelect.disabled = true;

    if (kodeDokumenByType[jenisId]) {
        kodeSelect.innerHTML = '<option value="">-- Select Document Code --</option>';
        kodeDokumenByType[jenisId].forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.kode + ' - ' + item.nama;
            kodeSelect.appendChild(option);
        });
        kodeSelect.disabled = false;
        return;
    }

    fetch('<?= base_url('document-submission-list') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        body: 'action=get-kode-dokumen&jenis=' + encodeURIComponent(jenisId)
    })
    .then(response => response.json())
    .then(data => {
        kodeSelect.innerHTML = '<option value="">-- Select Document Code --</option>';
        if (Array.isArray(data) && data.length > 0) {
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.kode + ' - ' + item.nama;
                kodeSelect.appendChild(option);
            });
        } else {
            kodeSelect.innerHTML = '<option value="">-- No data available --</option>';
        }
        kodeSelect.disabled = false;
    })
    .catch(error => {
        console.error('Error loading document codes:', error);
        kodeSelect.innerHTML = '<option value="">-- Failed to load data --</option>';
        kodeSelect.disabled = false;
    });
}

$(document).ready(function() {
    const table = $('#documentsTable').DataTable({
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        paging: true,
        language: {
            lengthMenu: "Show _MENU_ entries",
            zeroRecords: "No data found",
            search: "Search:",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "Showing 0 to 0 of 0 entries",
            infoFiltered: "(filtered from _MAX_ total entries)",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        columnDefs: [
            {
                targets: 0, // No column
                searchable: false,
                orderable: false
            },
            {
                targets: 1, // Document ID column
                visible: false,
                searchable: true,
                orderable: true
            },
            {
                targets: 12, // Actions column
                orderable: false,
                searchable: false
            }
        ],
        responsive: true,
        autoWidth: false,
        order: [[0, 'desc']],
        drawCallback: function(settings) {
            var api = this.api();
            var pageInfo = api.page.info();
            var startRow = pageInfo.start;
            $('#documentsTable tbody tr').each(function(index) {
                $(this).find('td:first').text(startRow + index + 1);
            });
        }
    });

    $('.dataTables_filter').hide();

    $('#searchInput').on('keyup', function() {
        table.search(this.value).draw();
    });

    $('#filterFakultas').on('change', function() {
        const val = this.value;
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            const row = $('#documentsTable').DataTable().row(dataIndex);
            const fakultasId = row.node().querySelector('td[data-fakultas]')?.getAttribute('data-fakultas') || '';
            return val === '' || fakultasId === val;
        });
        table.draw();
        $.fn.dataTable.ext.search.pop();
    });

    $('#filterJenis').on('change', function() {
        const val = this.value;
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            const row = $('#documentsTable').DataTable().row(dataIndex);
            const jenisId = row.node().querySelector('td[data-jenis]')?.getAttribute('data-jenis') || '';
            return val === '' || jenisId === val;
        });
        table.draw();
        $.fn.dataTable.ext.search.pop();
    });

    function filterAndHighlightDocumentFromUrl() {
        const urlParams = new URLSearchParams(window.location.search);
        const revisionId = urlParams.get('revision_id');
        const documentId = urlParams.get('document_id');

        if (revisionId) {
            $.ajax({
                url: '<?= base_url("document-submission-list") ?>?action=get-history&id=' + (documentId || '0'),
                type: 'GET',
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success && response.data && response.data.history && response.data.history.length > 0) {
                        $('#historyNamaDokumen').text(response.data.document.title || '-');
                        $('#historyJenisDokumen').text(response.data.document.jenis_dokumen || '-');
                        let html = '';
                        const reversedHistory = response.data.history.slice().reverse();
                        reversedHistory.forEach((item, index) => {
                            const fileLink = item.filepath ? `
                                <div class="d-flex gap-2">
                                    <a href="<?= base_url('document-submission-list') ?>?action=download-file&id=${item.document_id}" class="text-decoration-none" title="Download file">
                                        <i class="bi bi-download text-success fs-5"></i>
                                    </a>
                                </div>
                            ` : '<span class="text-muted"><i class="bi bi-file-earmark-x"></i> No file</span>';
                            const statusBadge = item.status == 0 ? '<span class="badge bg-warning text-white">Waiting</span>' :
                                               item.status == 1 ? '<span class="badge bg-success text-white">Approved</span>' :
                                               item.status == 2 ? '<span class="badge bg-danger text-white">Disapproved</span>' :
                                               item.status == 3 ? '<span class="badge bg-secondary text-white">Deleted</span>' :
                                               item.status == 4 ? '<span class="badge bg-secondary text-white">Superseded</span>' : '-';
                            html += `
                                <tr data-revision-id="${item.revision_id}" ${item.revision_id == revisionId ? 'class="document-highlight"' : ''}>
                                    <td class="text-center">${index + 1}</td>
                                    <td class="text-center d-none">${item.revision_id}</td>
                                    <td>${item.document_title || '-'}</td>
                                    <td>${item.document_number || '-'}</td>
                                    <td>${fileLink}</td>
                                    <td class="text-center">${item.revision || 'Rev. 0'}</td>
                                    <td>${formatDate(item.updated_at)}</td>
                                    <td>${statusBadge}</td>
                                </tr>
                            `;
                        });
                        $('#historyTableBody').html(html);
                        const historyModal = new bootstrap.Modal(document.getElementById('historyModal'));
                        historyModal.show();
                        setTimeout(() => {
                            const $highlightedRow = $('#historyTableBody tr.document-highlight');
                            if ($highlightedRow.length) {
                                $highlightedRow[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                                setTimeout(() => {
                                    $highlightedRow.removeClass('document-highlight');
                                }, 5000);
                            }
                        }, 500);
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Document Not Found',
                            text: 'Revision with ID ' + revisionId + ' not found or you do not have access.',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error checking document history:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to check document history. Please try again.',
                        confirmButtonColor: '#dc3545'
                    });
                }
            });
        } else if (documentId) {
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
                        $('html, body').animate({
                            scrollTop: $row.offset().top - 100
                        }, 500);
                        setTimeout(() => {
                            $row.removeClass('document-highlight');
                        }, 5000);
                    }
                }, 100);
            }
        }
    }

    filterAndHighlightDocumentFromUrl();

    table.on('draw', function() {
        filterAndHighlightDocumentFromUrl();
    });

    if (documentPrivileges.can_update) {
        $('.edit-btn').each(function() {
            const status = parseInt($(this).data('status'));
            $(this).attr('title', status === 1 ? 'Document is already approved and cannot be edited' : 'Edit');
            $(this).tooltip('dispose').tooltip();
        });
    }

    function resetFilters() {
        table.search('').columns().search('');
        $.fn.dataTable.ext.search.pop();
        $('#searchInput').val('');
        $('#filterFakultas').val('');
        $('#filterJenis').val('');
        table.draw();
        window.location.href = '<?= base_url('document-submission-list') ?>';
    }

    $('#resetButton').on('click', resetFilters);

    const today = new Date().toISOString().split('T')[0];
    if (documentPrivileges.can_approve) {
        $('#approval_date').val(today);
    }

    if (documentPrivileges.can_update) {
        $(document).on('click', '.edit-btn', function() {
            const editBtn = $(this);
            const status = parseInt(editBtn.data('status'));

            if (status === 1) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Cannot Edit',
                    text: 'This document is already approved and cannot be edited.',
                    confirmButtonColor: '#dc3545'
                });
                return false;
            }

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
                    $('#editFakultas').val(editBtn.data('fakultas'));
                    $('#editBagian').val(editBtn.data('unit'));
                    $('#editNama').val(editBtn.data('nama'));
                    $('#editNomor').val(editBtn.data('nomor'));
                    $('#editRevisi').val(editBtn.data('revisi'));
                    $('#editKeterangan').val(editBtn.data('keterangan'));

                    const filepath = editBtn.data('filepath');
                    const filename = editBtn.data('filename');
                    if (filepath || filename) {
                        $('#currentFileInfo').show();
                        $('#currentFileName').text(filename || filepath);
                    } else {
                        $('#currentFileInfo').hide();
                    }

                    $('#editKodeGroup').hide();
                    $('#editKodeCustomGroup').hide();

                    const jenisId = editBtn.data('jenis');
                    $('#editJenis').val(jenisId);
                    
                    const usePredefined = editBtn.data('use-predefined') === true || editBtn.data('use-predefined') === 'true';
                    
                    if (usePredefined) {
                        $('#editKodeGroup').show();
                        $('#editKodeCustomGroup').hide();
                        loadEditKodeDokumen(jenisId);
                        const kodeDokumenId = editBtn.data('kode-dokumen-id');
                        if (kodeDokumenId) {
                            setTimeout(function() {
                                $('#editNamaKode').val(kodeDokumenId);
                            }, 500);
                        }
                    } else {
                        $('#editKodeGroup').hide();
                        $('#editKodeCustomGroup').show();
                        const kodeCustom = editBtn.data('kode-dokumen-kode');
                        const namaCustom = editBtn.data('kode-dokumen-nama');
                        $('#editKodeCustom').val(kodeCustom || '');
                        $('#editNamaCustom').val(namaCustom || '');
                    }

                    const editModal = new bootstrap.Modal(document.getElementById('editModal'));
                    editModal.show();
                }
            });
        });

        $('#editModal form').on('submit', function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: 'Save Changes',
                text: 'Are you sure you want to save these changes?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Save!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Saving Changes...',
                        text: 'Please wait a moment',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    this.submit();
                }
            });
        });
    }

    if (documentPrivileges.can_approve) {
        $('.approve-btn').each(function() {
            const status = parseInt($(this).data('status'));
            $(this).attr('title', status === 1 ? 'Document is already approved' : 'Approve');
            $(this).tooltip('dispose').tooltip();
        });

        $(document).on('click', '.approve-btn, .approve-btn i', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const $button = $(this).hasClass('approve-btn') ? $(this) : $(this).closest('.approve-btn');
            const id = $button.data('id');
            const status = parseInt($button.data('status'));

            console.log(`Approve button clicked - ID: ${id}, Status: ${status}, Element: ${$(this).prop('tagName')}`);

            if (!id) {
                console.error('No document ID found');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Document ID not found. Please try again.',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }

            if (status === 1) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Document Already Approved',
                    text: 'This document is already approved and cannot be approved again.',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }

            Swal.fire({
                title: 'Document Approval',
                text: 'Are you sure you want to proceed with document approval?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Proceed!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#approveDocumentId').val(id);
                    const modal = new bootstrap.Modal(document.getElementById('approveModal'));
                    modal.show();
                }
            });
        });

        $('#approveForm').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();
            const action = $('button[type="submit"][name="action"]:focus').val() || 
                           $('input[name="action"]').val() || '';

            console.log(`Approve form submitted - Action: ${action}, FormData: ${formData}`);

            if (!action) {
                console.error('No action value detected. Check button focus or form structure.');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Action not detected. Please try again.',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }

            const isApprove = action === 'approve';
            const confirmationConfig = {
                title: isApprove ? 'Approve Document' : 'Disapprove Document',
                text: isApprove ? 
                    'Are you sure you want to approve this document?' : 
                    'Are you sure you want to reject this document?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: isApprove ? '#28a745' : '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: isApprove ? 'Yes, Approve!' : 'Yes, Disapprove!',
                cancelButtonText: 'Cancel'
            };

            Swal.fire(confirmationConfig).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: isApprove ? 'Approving Document...' : 'Rejecting Document...',
                        text: 'Please wait a moment',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    $(this).append('<input type="hidden" name="action" value="' + action + '">');
                    $(this).unbind('submit').submit();
                }
            });
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
                    Swal.fire({
                        title: 'Deleting Document...',
                        text: 'Please wait a moment',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: '<?= base_url("document-submission-list/delete") ?>',
                        type: 'POST',
                        data: {
                            document_id: id,
                            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Successfully Deleted!',
                                    text: response.message || 'Document has been successfully deleted.',
                                    confirmButtonColor: '#28a745'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Failed to Delete!',
                                    text: response.message || 'An error occurred while deleting the document.',
                                    confirmButtonColor: '#dc3545'
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Delete error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Server Error!',
                                text: 'A server error occurred. Please try again later.',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    });
                }
            });
        });
    }

    $(document).on('click', '.view-history-btn', function() {
        const id = $(this).data('id');
        
        $('#historyNamaDokumen').text('-');
        $('#historyJenisDokumen').text('-');
        $('#historyTableBody').html('<tr><td colspan="7" class="text-center">Loading data...</td></tr>');

        $.ajax({
            url: '<?= base_url("document-submission-list") ?>?action=get-history&id=' + id,
            type: 'GET',
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success && response.data) {
                    $('#historyNamaDokumen').text(response.data.document.title || '-');
                    $('#historyJenisDokumen').text(response.data.document.jenis_dokumen || '-');
                    let html = '';
                    if (response.data.history && response.data.history.length > 0) {
                        const reversedHistory = response.data.history.slice().reverse();
                        reversedHistory.forEach((item, index) => {
                            const fileLink = item.filepath ? `
                                <div class="d-flex gap-2">
                                    <a href="<?= base_url('document-submission-list') ?>?action=download-file&id=${item.document_id}" class="text-decoration-none" title="Download file">
                                        <i class="bi bi-download text-success fs-5"></i>
                                    </a>
                                </div>
                            ` : '<span class="text-muted"><i class="bi bi-file-earmark-x"></i> No file</span>';
                            
                            const statusBadge = item.status == 0 ? '<span class="badge bg-warning text-white">Waiting</span>' :
                                               item.status == 1 ? '<span class="badge bg-success text-white">Approved</span>' :
                                               item.status == 2 ? '<span class="badge bg-danger text-white">Disapproved</span>' :
                                               item.status == 3 ? '<span class="badge bg-secondary text-white">Deleted</span>' :
                                               item.status == 4 ? '<span class="badge bg-secondary text-white">Superseded</span>' : '-';
                            
                            html += `
                                <tr data-revision-id="${item.revision_id}">
                                    <td class="text-center">${index + 1}</td>
                                    <td class="text-center d-none">${item.revision_id}</td>
                                    <td>${item.document_title || '-'}</td>
                                    <td>${item.document_number || '-'}</td>
                                    <td>${fileLink}</td>
                                    <td class="text-center">${item.revision || 'Rev. 0'}</td>
                                    <td>${formatDate(item.updated_at)}</td>
                                    <td>${statusBadge}</td>
                                </tr>
                            `;
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

    $('form').on('submit', function(e) {
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        
        submitBtn.prop('disabled', true);
        
        setTimeout(function() {
            submitBtn.prop('disabled', false);
        }, 3000);
    });

    $('[title]').tooltip();

    $('input[type="file"]').on('change', function() {
        const file = this.files[0];
        if (file) {
            const fileSize = file.size / 1024 / 1024;
            const allowedTypes = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation'
            ];

            if (!allowedTypes.includes(file.type)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Unsupported File Type',
                    text: 'Only PDF, DOC, DOCX, XLS, XLSX, PPT, and PPTX files are allowed',
                    confirmButtonColor: '#dc3545'
                });
                $(this).val('');
                return;
            }
        }
    });

    $('textarea').each(function() {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
    }).on('input', function() {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
    });

    let searchTimeout;
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = this.value;
        
        searchTimeout = setTimeout(function() {
            table.search(searchTerm).draw();
        }, 300);
    });

    $(document).keydown(function(e) {
        if (e.ctrlKey && e.keyCode === 70) {
            e.preventDefault();
            $('#searchInput').focus();
        }
        
        if (e.keyCode === 27) {
            $('.modal.show').modal('hide');
        }
    });

    $(document).on('mouseenter', '[title]', function() {
        $(this).tooltip('show');
    });

    $('#documentsTable tbody').on('mouseenter', 'tr', function() {
        if (!$(this).hasClass('document-highlight')) {
            $(this).addClass('table-active');
        }
    }).on('mouseleave', 'tr', function() {
        if (!$(this).hasClass('document-highlight')) {
            $(this).removeClass('table-active');
        }
    });

    if (documentPrivileges.can_update) {
        $('#editKodeCustom').on('input', function() {
            $(this).val($(this).val().toUpperCase());
        });
    }
});

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function formatDate(dateString) {
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return new Date(dateString).toLocaleDateString('en-US', options);
}
</script>

<?= $this->endSection() ?>
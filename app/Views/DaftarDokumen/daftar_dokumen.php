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
    // Get privilege for document-list from session
    $documentPrivilege = session()->get('privileges')['document-list'] ?? [
        'can_create' => 0,
        'can_update' => 0,
        'can_delete' => 0,
        'can_approve' => 0
    ];
    // Check if user has privilege for any action
    $hasAnyPrivilege = $documentPrivilege['can_update'] || $documentPrivilege['can_delete'];
?>
<style>
    /* Hide action column if no privileges */
    <?php if (!$hasAnyPrivilege): ?>
    .aksi-column {
        display: none !important;
    }
    <?php endif; ?>
    
</style>
<!-- CSS External Links -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="stylesheet" href="<?= base_url('assets/css/daftar-dokumen.css') ?>">
<div class="container-fluid px-4 py-4">
    <h4 class="mb-4">Document List</h4>
    <!-- FILTER SECTION -->
    <div class="bg-light p-3 rounded mb-4">
        <div class="d-flex flex-wrap align-items-center gap-2">
            
            <!-- Filter Standard with Radio Button -->
            <div class="filter-dropdown flex-grow-1" style="min-width:180px;">
                <div class="filter-toggle" onclick="toggleDropdown('filterStandar', event)">
                    <span id="filterStandarText">Select Standard...</span>
                </div>
                <div class="filter-dropdown-content" id="filterStandarContent">
                    <div class="clear-selection" onclick="clearStandardSelection()">
                        <i class="bi bi-x-circle me-1"></i> Clear Selection
                    </div>
                    <div class="dropdown-search">
                        <input type="text" placeholder="Search standard..." onkeyup="filterStandardOptions()" id="standardSearchInput">
                    </div>
                    <div class="option-group" id="standardCheckboxGroup">
                        <?php foreach ($standards as $s): ?>
                            <div class="option-item" data-text="<?= strtolower(esc($s['nama_standar'])) ?>">
                                <input type="checkbox" 
                                       id="standar_<?= $s['id'] ?>" 
                                       value="<?= $s['id'] ?>" 
                                       name="standar_filter"
                                       class="standar-checkbox"
                                       onchange="updateClauseFilter(); updateStandardText();">
                                <label for="standar_<?= $s['id'] ?>"><?= esc($s['nama_standar']) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Filter Clause with Checkbox (Dynamic based on Standard) -->
            <div class="filter-dropdown flex-grow-1" style="min-width:180px;">
                <div class="filter-toggle" onclick="toggleDropdown('filterKlausul', event)" id="clauseToggle">
                    <span id="filterKlausulText">Select Clause...</span>
                </div>
                <div class="filter-dropdown-content" id="filterKlausulContent">
                    <div class="clear-selection" onclick="clearClauseSelection()">
                        <i class="bi bi-x-circle me-1"></i> Clear Selection
                    </div>
                    <div class="dropdown-search">
                        <input type="text" placeholder="Search clause..." onkeyup="filterClauseOptions()" id="clauseSearchInput">
                    </div>
                    <div class="option-group" id="clauseCheckboxGroup">
                        <div class="disabled-message">Select standard first</div>
                    </div>
                </div>
            </div>
            
            <!-- Filter Document Owner - UPDATED: Now Searchable -->
            <div class="filter-dropdown flex-grow-1" style="min-width:180px;">
                <div class="filter-toggle" onclick="toggleDropdown('filterPemilik', event)">
                    <span id="filterPemilikText">All Document Owners</span>
                </div>
                <div class="filter-dropdown-content" id="filterPemilikContent">
                    <div class="clear-selection" onclick="clearOwnerSelection()">
                        <i class="bi bi-x-circle me-1"></i> Clear Selection
                    </div>
                    <div class="dropdown-search">
                        <input type="text" placeholder="Search owner..." onkeyup="filterOwnerOptions()" id="ownerSearchInput">
                    </div>
                    <div class="option-group" id="ownerRadioGroup">
                        <div class="option-item" data-text="all document owners">
                            <input type="radio" 
                                   id="pemilik_all" 
                                   value="" 
                                   name="pemilik_filter"
                                   class="pemilik-radio"
                                   checked
                                   onchange="updateOwnerText(); closeDropdown('filterPemilik');">
                            <label for="pemilik_all">All Document Owners</label>
                        </div>
                        <?php 
                        $unique_owners = [];
                        foreach ($document as $doc) {
                            $documentCreatorId = $doc['createdby_id'] ?? 0;
                            $documentCreatorUnitId = $doc['creator_unit_id'] ?? 0;
                            $documentCreatorUnitParentId = $doc['creator_unit_parent_id'] ?? 0;
                            $documentCreatorAccessLevel = $doc['creator_access_level'] ?? 2;
                            $documentCreatorName = $doc['creator_fullname'] ?? $doc['createdby'] ?? 'Unknown User';
                            $canViewDocument = false;
                            $showCreatorName = false;
                            
                            if ($documentCreatorId == $currentUserId) {
                                $canViewDocument = true;
                                $showCreatorName = true;
                            } elseif ($currentUserAccessLevel < $documentCreatorAccessLevel) {
                                $sameUnit = ($documentCreatorUnitId == $currentUserUnitId);
                                $sameUnitParent = ($documentCreatorUnitParentId == $currentUserUnitParentId);
                                $creatorIsSubordinate = ($documentCreatorUnitParentId == $currentUserUnitId);
                                $inSameHierarchy = $sameUnit || $sameUnitParent || $creatorIsSubordinate;
                                if ($inSameHierarchy) {
                                    $canViewDocument = true;
                                    $showCreatorName = true;
                                }
                            } elseif ($currentUserAccessLevel == 2) {
                                $canViewDocument = false;
                            }
                            
                            if ($canViewDocument && $showCreatorName && !empty($documentCreatorName) && $documentCreatorName != 'Unknown User') {
                                $unique_owners[$documentCreatorName] = $documentCreatorName;
                            }
                        }
                        foreach ($unique_owners as $owner): 
                        ?>
                            <div class="option-item" data-text="<?= strtolower(esc($owner)) ?>">
                                <input type="radio" 
                                       id="pemilik_<?= md5($owner) ?>" 
                                       value="<?= esc($owner) ?>" 
                                       name="pemilik_filter"
                                       class="pemilik-radio"
                                       onchange="updateOwnerText(); closeDropdown('filterPemilik');">
                                <label for="pemilik_<?= md5($owner) ?>"><?= esc($owner) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Filter Document Type - UPDATED: Now Searchable -->
            <div class="filter-dropdown flex-grow-1" style="min-width:180px;">
                <div class="filter-toggle" onclick="toggleDropdown('filterJenis', event)">
                    <span id="filterJenisText">All Document Types</span>
                </div>
                <div class="filter-dropdown-content" id="filterJenisContent">
                    <div class="clear-selection" onclick="clearTypeSelection()">
                        <i class="bi bi-x-circle me-1"></i> Clear Selection
                    </div>
                    <div class="dropdown-search">
                        <input type="text" placeholder="Search document type..." onkeyup="filterTypeOptions()" id="typeSearchInput">
                    </div>
                    <div class="option-group" id="typeRadioGroup">
                        <div class="option-item" data-text="all document types">
                            <input type="radio" 
                                   id="jenis_all" 
                                   value="" 
                                   name="jenis_filter"
                                   class="jenis-radio"
                                   checked
                                   onchange="updateTypeText(); closeDropdown('filterJenis');">
                            <label for="jenis_all">All Document Types</label>
                        </div>
                        <?php foreach ($kategori_dokumen as $k): ?>
                            <div class="option-item" data-text="<?= strtolower(esc($k['name'])) ?>">
                                <input type="radio" 
                                       id="jenis_<?= $k['id'] ?>" 
                                       value="<?= $k['id'] ?>" 
                                       name="jenis_filter"
                                       class="jenis-radio"
                                       onchange="updateTypeText(); closeDropdown('filterJenis');">
                                <label for="jenis_<?= $k['id'] ?>"><?= esc($k['name']) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Filter and Export Buttons -->
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-primary btn-sm" id="btnFilter">Filter</button>
                <button class="btn btn-secondary btn-sm" id="btnResetFilter">Reset Filter</button>
                <button class="btn btn-success btn-sm" id="excel-button-container">Export Excel</button>
            </div>
        </div>
    </div>
    <!-- DATA TABLE CARD -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="dt-buttons-container"></div>
                    <div class="dt-length-container"></div>
                </div>
                <div class="dt-search-container"></div>
            </div>
            <div class="table-wrapper position-relative">
                <div class="table-responsive">
                    <div class="datatable-info-container mt-2"></div>
                    <table id="dokumenTable" class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Faculty/Directorate</th>
                                <th>Department/Unit</th>
                                <th>Standard</th>
                                <th>Clause</th>
                                <th>Document Type</th>
                                <th>Code & Document Name</th>
                                <th>Document Number</th>
                                <th>Document Name</th>
                                <th>Document Owner</th>
                                <th>Document File</th>
                                <th>Revision</th>
                                <th>Effective Date</th>
                                <th>Approved By</th>
                                <th>Approval Date</th>
                                <?php if ($hasAnyPrivilege): ?>
                                    <th class="aksi-column">Action</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $displayedCount = 0;
                            foreach ($document as $row): 
                                $documentCreatorId = $row['createdby_id'] ?? 0;
                                $documentCreatorUnitId = $row['creator_unit_id'] ?? 0;
                                $documentCreatorUnitParentId = $row['creator_unit_parent_id'] ?? 0;
                                $documentCreatorAccessLevel = $row['creator_access_level'] ?? 2;
                                $documentCreatorName = $row['creator_fullname'] ?? $row['createdby'] ?? 'Unknown User';
                                $canViewDocument = false;
                                $showCreatorName = false;
                                $canEditDocument = false;
                                $canDeleteDocument = false;
                                if ($documentCreatorId == $currentUserId) {
                                    $canViewDocument = true;
                                    $showCreatorName = true;
                                    $canEditDocument = true;
                                    $canDeleteDocument = true;
                                } elseif ($currentUserAccessLevel < $documentCreatorAccessLevel) {
                                    $sameUnit = ($documentCreatorUnitId == $currentUserUnitId);
                                    $sameUnitParent = ($documentCreatorUnitParentId == $currentUserUnitParentId);
                                    $creatorIsSubordinate = ($documentCreatorUnitParentId == $currentUserUnitId);
                                    $inSameHierarchy = $sameUnit || $sameUnitParent || $creatorIsSubordinate;
                                    if ($inSameHierarchy) {
                                        $canViewDocument = true;
                                        $showCreatorName = true;
                                        $canEditDocument = true;
                                        $canDeleteDocument = true;
                                    }
                                } elseif ($currentUserAccessLevel == 2) {
                                    $canViewDocument = false;
                                }
                                if (!$canViewDocument) continue;
                                if ($documentCreatorId == 0) continue;
                                $displayedCount++;
                            ?>
                                <tr data-standar="<?= implode(',', array_filter(explode(',', $row['standar_ids'] ?? ''))) ?>" 
                                    data-klausul="<?= implode(',', array_filter(explode(',', $row['klausul_ids'] ?? ''))) ?>"
                                    data-pemilik="<?= $showCreatorName ? esc($documentCreatorName) : '' ?>"
                                    data-creator-id="<?= $documentCreatorId ?>"
                                    data-can-edit="<?= $canEditDocument ? '1' : '0' ?>"
                                    data-can-delete="<?= $canDeleteDocument ? '1' : '0' ?>">
                                    <td class="text-center"><?= $displayedCount ?></td>
                                    <td><?= esc($row['parent_name'] ?? '-') ?></td>
                                    <td><?= esc($row['unit_name'] ?? '-') ?></td>
                                    <td>
                                        <?php
                                        // Display standards with color coding
                                        $standardIds = array_filter(explode(',', $row['standar_ids'] ?? ''));
                                        $standardNames = [];
                                        
                                        // Get standard names
                                        foreach ($standardIds as $standardId) {
                                            foreach ($standards as $standard) {
                                                if ($standard['id'] == $standardId) {
                                                    $standardNames[] = $standard['nama_standar'];
                                                    break;
                                                }
                                            }
                                        }
                                        
                                        if (!empty($standardNames)):
                                            $colorIndex = 1;
                                            foreach ($standardNames as $standardName):
                                                $colorClass = $colorIndex <= 6 ? "standard-{$colorIndex}" : "standard-default";
                                                $colorIndex++;
                                        ?>
                                            <div class="clause-group standard-only <?= $colorClass ?>">
                                                <div class="standard-name"><?= esc($standardName) ?></div>
                                            </div>
                                        <?php 
                                            endforeach;
                                        else: 
                                        ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        // Group clauses by standard
                                        $standardIds = array_filter(explode(',', $row['standar_ids'] ?? ''));
                                        $clauseIds = array_filter(explode(',', $row['klausul_ids'] ?? ''));
                                        
                                        $clausesByStandard = [];
                                        
                                        // Group clauses by their standards
                                        foreach ($clauseIds as $clauseId) {
                                            foreach ($clausesData as $clause) {
                                                if ($clause['id'] == $clauseId) {
                                                    $standardId = $clause['standar_id'];
                                                    if (!isset($clausesByStandard[$standardId])) {
                                                        $clausesByStandard[$standardId] = [
                                                            'standard_name' => '',
                                                            'clauses' => []
                                                        ];
                                                    }
                                                    $clausesByStandard[$standardId]['clauses'][] = $clause['nama_klausul'];
                                                    
                                                    // Get standard name
                                                    foreach ($standards as $standard) {
                                                        if ($standard['id'] == $standardId) {
                                                            $clausesByStandard[$standardId]['standard_name'] = $standard['nama_standar'];
                                                            break;
                                                        }
                                                    }
                                                    break;
                                                }
                                            }
                                        }
                                        
                                        if (!empty($clausesByStandard)):
                                            $colorIndex = 1;
                                            foreach ($clausesByStandard as $standardId => $data):
                                                $colorClass = $colorIndex <= 6 ? "standard-{$colorIndex}" : "standard-default";
                                                $clausesText = implode(', ', $data['clauses']);
                                                $colorIndex++;
                                        ?>
                                            <div class="clause-group <?= $colorClass ?>">
                                                <div class="standard-name"><?= esc($data['standard_name']) ?></div>
                                                <div class="clause-list">
                                                    <?= esc(strlen($clausesText) > 50 ? substr($clausesText, 0, 47) . '...' : $clausesText) ?>
                                                </div>
                                            </div>
                                        <?php 
                                            endforeach;
                                        else: 
                                        ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($row['jenis_dokumen'] ?? '-') ?></td>
                                    <td>
                                        <div class="kode-dokumen-simple">
                                            <?php 
                                            $kodeDokumenText = '';
                                            if (!empty($row['kode_dokumen_kode']) && !empty($row['kode_dokumen_nama'])) {
                                                $kodeDokumenText = $row['kode_dokumen_kode'] . ' - ' . $row['kode_dokumen_nama'];
                                            } elseif (!empty($row['kode_jenis_dokumen'])) {
                                                $kodeDokumenText = $row['kode_jenis_dokumen'];
                                                if (!empty($row['title'])) {
                                                    $kodeDokumenText .= ' - ' . $row['title'];
                                                }
                                            } elseif (!empty($row['title'])) {
                                                $kodeDokumenText = $row['title'];
                                            }
                                            if (!empty($kodeDokumenText)):
                                            ?>
                                                <span class="text-truncate-custom" title="<?= esc($kodeDokumenText) ?>">
                                                    <?= esc($kodeDokumenText) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><?= esc($row['number'] ?? '-') ?></td>
                                    <td>
                                        <span class="text-truncate-custom" title="<?= esc($row['title'] ?? '-') ?>">
                                            <?= esc($row['title'] ?? '-') ?>
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
                                    <td class="text-center">
                                        <?php if (!empty($row['filepath']) && file_exists(ROOTPATH . '..' . DIRECTORY_SEPARATOR . $row['filepath'])): ?>
                                            <a href="<?= base_url('document-list/serveFile?id=' . $row['id'] . '&action=download') ?>" 
                                               class="text-decoration-none" 
                                               title="Download <?= esc($row['filename'] ?? basename($row['filepath'])) ?>"
                                               data-filename="<?= esc($row['filename'] ?? basename($row['filepath'])) ?>">
                                                <i class="bi bi-download text-success fs-5"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted" data-filename="">
                                                <i class="bi bi-file-earmark-x"></i>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($row['revision'] ?? '-') ?></td>
                                    <td><?= esc($row['date_published'] ?? '-') ?></td>
                                    <td><?= esc($row['approved_by_name'] ?? '-') ?></td>
                                    <td><?= esc($row['approvedate'] ?? '-') ?></td>
                                    <?php if ($hasAnyPrivilege): ?>
                                        <td class="aksi-column text-center">
                                            <div class="d-flex align-items-center justify-content-center gap-2">
                                                <?php if ($documentPrivilege['can_update'] && $canEditDocument): ?>
                                                    <button class="btn btn-link p-0 text-primary" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editModal<?= esc($row['id']) ?>" 
                                                            title="Edit">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <?php if ($documentPrivilege['can_delete'] && $canDeleteDocument): ?>
                                                    <form action="<?= base_url('document-list/delete') ?>" method="post" onsubmit="return confirmDelete(event, this);">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="id" value="<?= esc($row['id']) ?>">
                                                        <button type="submit" class="btn btn-link p-0 text-danger" title="Delete">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="alert alert-info text-center" style="display: none;" id="no-data-message">
                        <i class="bi bi-info-circle"></i> No documents found matching your criteria.
                    </div>
                </div>
                <div class="pagination-container"></div>
            </div>
        </div>
    </div>
</div>
<!-- DOCUMENT EDIT MODAL -->
<?php 
foreach ($document as $row): 
    $documentId = esc($row['id']);
    $documentCreatorId = $row['createdby_id'] ?? 0;
    $documentCreatorUnitId = $row['creator_unit_id'] ?? 0;
    $documentCreatorUnitParentId = $row['creator_unit_parent_id'] ?? 0;
    $documentCreatorAccessLevel = $row['creator_access_level'] ?? 2;
    $documentCreatorName = $row['creator_fullname'] ?? $row['createdby'] ?? 'Unknown User';
    
    $canViewDocument = false;
    $showCreatorName = false;
    $canEditDocument = false;
    
    if ($documentCreatorId == $currentUserId) {
        $canViewDocument = true;
        $showCreatorName = true;
        $canEditDocument = true;
    }
    elseif ($currentUserAccessLevel < $documentCreatorAccessLevel) {
        $sameUnit = ($documentCreatorUnitId == $currentUserUnitId);
        $sameUnitParent = ($documentCreatorUnitParentId == $currentUserUnitParentId);
        $creatorIsSubordinate = ($documentCreatorUnitParentId == $currentUserUnitId);
        $inSameHierarchy = $sameUnit || $sameUnitParent || $creatorIsSubordinate;
        
        if ($inSameHierarchy) {
            $canViewDocument = true;
            $showCreatorName = true;
            $canEditDocument = true;
        }
    }
    elseif ($currentUserAccessLevel == 2) {
        $canViewDocument = false;
    }
    
    if (!$canViewDocument || !$canEditDocument || !$documentPrivilege['can_update']) continue;
    if ($documentCreatorId == 0) continue;
?>
<div class="modal fade" id="editModal<?= $documentId ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $documentId ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <form action="<?= base_url('document-list/update') ?>" method="post" class="edit-form" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $documentId ?>">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel<?= $documentId ?>">Edit Document</h5>
                </div>
                <div class="modal-body">
                    <div class="row g-4 justify-content-center">
                        <div class="col-12">
                            <label class="form-label">Standard</label>
                            <div class="filter-dropdown w-100 no-choices">
                                <div class="filter-toggle" onclick="event.preventDefault(); event.stopPropagation(); toggleEditDropdown('editFilterStandar_<?= $documentId ?>', event)">
                                    <span id="editFilterStandarText_<?= $documentId ?>">
                                        <?php
                                        // Display the selected standards
                                        $selectedStandardIds = array_filter(explode(',', $row['standar_ids'] ?? ''));
                                        $selectedStandardNames = [];
                                        foreach ($standards as $s) {
                                            if (in_array($s['id'], $selectedStandardIds)) {
                                                $selectedStandardNames[] = esc($s['nama_standar']);
                                            }
                                        }
                                        
                                        if (count($selectedStandardNames) > 1) {
                                            echo count($selectedStandardNames) . ' standards selected';
                                        } elseif (count($selectedStandardNames) === 1) {
                                            $fullText = $selectedStandardNames[0];
                                            echo strlen($fullText) > 30 ? substr($fullText, 0, 27) . '...' : $fullText;
                                        } else {
                                            echo 'Select Standard...';
                                        }
                                        ?>
                                    </span>
                                </div>
                                <div class="filter-dropdown-content" id="editFilterStandarContent_<?= $documentId ?>">
                                    <div class="clear-selection" onclick="clearEditStandardSelection('<?= $documentId ?>')">
                                        <i class="bi bi-x-circle me-1"></i> Clear Selection
                                    </div>
                                    <div class="dropdown-search">
                                        <input type="text" placeholder="Search standard..." onkeyup="filterEditStandardOptions('<?= $documentId ?>')" id="editStandardSearchInput_<?= $documentId ?>">
                                    </div>
                                    <div class="option-group" id="editStandardCheckboxGroup_<?= $documentId ?>">
                                        <?php foreach ($standards as $s): ?>
                                            <div class="option-item" data-text="<?= strtolower(esc($s['nama_standar'])) ?>">
                                                <input type="checkbox" 
                                                       id="edit_standar_<?= $documentId ?>_<?= $s['id'] ?>" 
                                                       value="<?= $s['id'] ?>" 
                                                       name="standar_ids[]"
                                                       class="edit-standar-checkbox"
                                                       onchange="updateEditStandardText('<?= $documentId ?>'); updateEditClauseFilter('<?= $documentId ?>');"
                                                       <?= in_array($s['id'], array_filter(explode(',', $row['standar_ids'] ?? ''))) ? 'checked' : '' ?>>
                                                <label for="edit_standar_<?= $documentId ?>_<?= $s['id'] ?>"><?= esc($s['nama_standar']) ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Clause</label>
                            <div class="filter-dropdown w-100 no-choices">
                                <div class="filter-toggle" onclick="event.preventDefault(); event.stopPropagation(); toggleEditDropdown('editFilterKlausul_<?= $documentId ?>', event)" id="editClauseToggle_<?= $documentId ?>">
                                    <span id="editFilterKlausulText_<?= $documentId ?>">
                                        <?php
                                        // Display the selected clause names
                                        $selectedClauseIds = array_filter(explode(',', $row['klausul_ids'] ?? ''));
                                        $selectedClauseNames = [];
                                        foreach ($clausesData as $clause) {
                                            if (in_array($clause['id'], $selectedClauseIds)) {
                                                $selectedClauseNames[] = $clause['nama_klausul'];
                                            }
                                        }
                                        if (count($selectedClauseNames) > 1) {
                                            echo count($selectedClauseNames) . ' clauses selected';
                                        } elseif (count($selectedClauseNames) === 1) {
                                            $fullText = $selectedClauseNames[0];
                                            echo strlen($fullText) > 30 ? substr($fullText, 0, 27) . '...' : $fullText;
                                        } else {
                                            echo 'Select Clause...';
                                        }
                                        ?>
                                    </span>
                                </div>
                                <div class="filter-dropdown-content" id="editFilterKlausulContent_<?= $documentId ?>">
                                    <div class="clear-selection" onclick="clearEditClauseSelection('<?= $documentId ?>')">
                                        <i class="bi bi-x-circle me-1"></i> Clear Selection
                                    </div>
                                    <div class="dropdown-search">
                                        <input type="text" placeholder="Search clause..." onkeyup="filterEditClauseOptions('<?= $documentId ?>')" id="editClauseSearchInput_<?= $documentId ?>">
                                    </div>
                                    <div class="option-group" id="editClauseCheckboxGroup_<?= $documentId ?>">
                                        <?php
                                        // Pre-populate clauses for all selected standards
                                        $selectedStandardIds = array_filter(explode(',', $row['standar_ids'] ?? ''));
                                        if (!empty($selectedStandardIds)):
                                            foreach ($clausesData as $clause):
                                                if (in_array($clause['standar_id'], $selectedStandardIds)):
                                        ?>
                                                    <div class="option-item" data-text="<?= strtolower(esc($clause['nama_klausul'] . ' ' . $clause['nama_standar'])) ?>">
                                                        <input type="checkbox" 
                                                               id="edit_klausul_<?= $documentId ?>_<?= $clause['id'] ?>" 
                                                               value="<?= $clause['id'] ?>" 
                                                               name="clauses[]"
                                                               class="edit-klausul-checkbox"
                                                               onchange="updateEditClauseText('<?= $documentId ?>')"
                                                               <?= in_array($clause['id'], array_filter(explode(',', $row['klausul_ids'] ?? ''))) ? 'checked' : '' ?>>
                                                        <label for="edit_klausul_<?= $documentId ?>_<?= $clause['id'] ?>"><?= esc($clause['nama_klausul']) ?> (<?= esc($clause['nama_standar']) ?>)</label>
                                                    </div>
                                        <?php
                                                endif;
                                            endforeach;
                                        else:
                                        ?>
                                            <div class="disabled-message">Select standard first</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
    
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endforeach; ?>

<!-- JS External Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

<!-- Custom JavaScript -->
<script>
$(document).ready(function() {
    // Privilege check from PHP
    const clausesData = <?= json_encode($clausesData ?? []) ?>;
    const documentPrivilege = <?= json_encode($documentPrivilege) ?>;
    const hasAnyPrivilege = <?= json_encode($hasAnyPrivilege) ?>;
    const currentUserId = <?= json_encode($currentUserId) ?>;
    const currentUserAccessLevel = <?= json_encode($currentUserAccessLevel) ?>;
    
    // Calculate number of columns based on privilege
    const totalColumns = hasAnyPrivilege ? 16 : 15; // 16 if action column exists, 15 if not
    
    // Initialize DataTables with dynamic configuration
    const tableConfig = {
        dom: 'rt<"d-flex justify-content-between align-items-center mt-3"<"d-flex align-items-center"l><"pagination-wrapper"p>>',
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        language: {
            lengthMenu: "Show _MENU_ entries",
            paginate: {
                previous: "Previous",
                next: "Next"
            },
            zeroRecords: "",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "Showing 0 to 0 of 0 entries",
            infoFiltered: "(filtered from _MAX_ total entries)"
        },
        columnDefs: [
            { searchable: true, targets: [1, 2, 3, 4, 5, 6, 7, 8, 9, 12, 13, 14] }, // Enable search on specific columns
            { className: 'text-center', targets: [0, 10, 11] } // Center align for No, File, and Revision
        ],
        buttons: [
            {
                extend: 'excel',
                title: 'Document_List',
                exportOptions: { 
                    columns: hasAnyPrivilege ? [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14] : ':not(:last-child)',
                    format: {
                        body: function (data, row, column, node) {
                            // Handle the Document File column (column 10)
                            if (column === 10) {
                                var $node = $(node);
                                var $link = $node.find('a');
                                var $span = $node.find('span');
                                
                                // Get filename from data-filename attribute
                                var filename = '';
                                if ($link.length > 0) {
                                    filename = $link.attr('data-filename') || '';
                                } else if ($span.length > 0) {
                                    filename = $span.attr('data-filename') || '';
                                }
                                
                                if (filename && filename.trim() !== '') {
                                    return filename.trim();
                                } else if ($link.length > 0) {
                                    // Fallback: try to get from title attribute
                                    var title = $link.attr('title');
                                    if (title && title.includes('Download ')) {
                                        return title.replace('Download ', '');
                                    }
                                    return "File Available";
                                } else {
                                    // No file available
                                    return "No file";
                                }
                            }
                            
                            // Handle Standard column (column 3) - extract text from colored divs
                            if (column === 3) {
                                var $node = $(node);
                                var standards = [];
                                $node.find('.standard-name').each(function() {
                                    var text = $(this).text().trim();
                                    if (text && text !== '-') {
                                        standards.push(text);
                                    }
                                });
                                if (standards.length > 0) {
                                    return standards.join('; ');
                                }
                                // Fallback: clean HTML and get text
                                var cleanText = data.replace(/<[^>]*>/g, '').trim();
                                return cleanText && cleanText !== '-' ? cleanText : '-';
                            }
                            
                            // Handle Clause column (column 4) - extract text from colored divs
                            if (column === 4) {
                                var $node = $(node);
                                var clauses = [];
                                $node.find('.clause-group').each(function() {
                                    var standardName = $(this).find('.standard-name').text().trim();
                                    var clauseList = $(this).find('.clause-list').text().trim();
                                    if (standardName && clauseList && standardName !== '-' && clauseList !== '-') {
                                        clauses.push(standardName + ': ' + clauseList);
                                    }
                                });
                                if (clauses.length > 0) {
                                    return clauses.join('; ');
                                }
                                // Fallback: clean HTML and get text
                                var cleanText = data.replace(/<[^>]*>/g, '').trim();
                                return cleanText && cleanText !== '-' ? cleanText : '-';
                            }
                            
                            // For other columns, clean HTML tags and return text content
                            return data.replace(/<[^>]*>/g, '').trim();
                        }
                    },
                    modifier: {
                        // Export only visible rows (filtered data)
                        search: 'applied',
                        order: 'applied'
                    }
                },
                customize: function(xlsx) {
                    // Get the worksheet
                    var sheet = xlsx.xl.worksheets['sheet1.xml'];
                    
                    // Add auto-width to columns
                    var cols = sheet.getElementsByTagName('col');
                    for (var i = 0; i < cols.length; i++) {
                        cols[i].setAttribute('width', '20');
                    }
                    
                    // Set header row style
                    $('row:first c', sheet).attr('s', '42');
                }
            }
        ],
        drawCallback: function() {
            const paginationHtml = $('.dataTables_paginate').html();
            const info = this.api().page.info();
            const infoText = `Showing ${info.start + 1} to ${info.end} of ${info.recordsDisplay} entries`;
            
            // Create pagination container structure with info below
            $('.pagination-container').html(`
                <div class="d-flex justify-content-between align-items-center w-100">
                    <div class="table-info-bottom">
                        <small class="text-muted">${infoText}</small>
                    </div>
                    <div class="pagination-wrapper">
                        ${paginationHtml ? `<div class="dataTables_paginate">${paginationHtml}</div>` : ''}
                    </div>
                </div>
            `);
            
            // Hide original pagination from DataTables
            $('.dataTables_paginate').not('.pagination-container .dataTables_paginate').hide();

            // Clear info container above table
            $('.datatable-info-container').html('');

            // Styling for wrapper elements
            $('.dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_info, .dataTables_wrapper .dataTables_paginate').css({
                'position': 'sticky',
                'left': '0',
                'background': 'white',
                'z-index': '10'
            });
        }
    };

    // If action column exists, set as non-sortable
    if (hasAnyPrivilege) {
        tableConfig.columnDefs.push({ orderable: false, targets: [-1] });
    }

    const table = $('#dokumenTable').DataTable(tableConfig);

    // Move export buttons to container
    table.buttons().container().appendTo('.dt-buttons-container');
    
    // Move Excel button to container next to Filter
    const excelButton = $('.dt-buttons-container .buttons-excel').detach();
    excelButton.removeClass('dt-button').addClass('btn btn-success');
    $('#excel-button-container').html(excelButton);
    
    // Move length control to container ABOVE the table
    $('.dt-length-container').html(`
        <div class="d-flex align-items-center">
            <label class="me-2 mb-0">Show:</label>
            <select class="form-select form-select-sm" id="customLength" style="width: 80px;">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
            <label class="ms-2 mb-0">entries</label>
        </div>
    `);
    
    const searchHtml = `
        <div class="d-flex align-items-center">
            <label class="me-2 mb-0">Search:</label>
            <input type="search" class="form-control form-control-sm" id="customSearch" style="width: 200px;" placeholder="Search Document...">
        </div>
    `;
    $('.dt-search-container').html(searchHtml);
    
    // Connect custom search with DataTables
    $('#customSearch').on('keyup', function() {
        const searchTerm = this.value.trim();
        table.search(searchTerm).draw();

        const visibleRows = table.rows({ filter: 'applied' }).data().length;
        if (visibleRows === 0) {
            $('#no-data-message').show();
            $('#dokumenTable').hide();
            // Update info text for no results
            $('.table-info-bottom').html('<small class="text-muted">Showing 0 to 0 of 0 entries</small>');
        } else {
            $('#no-data-message').hide();
            $('#dokumenTable').show();
        }
    });

    // Connect custom length selector with DataTables
    $('#customLength').on('change', function() {
        table.page.len(parseInt(this.value)).draw();
    });

    // Handle pagination clicks in new container
    $(document).on('click', '.pagination-container .paginate_button', function(e) {
        e.preventDefault();
        const pageNum = $(this).data('dt-idx');
        if (pageNum !== undefined) {
            table.page(pageNum).draw('page');
        } else if ($(this).hasClass('previous')) {
            table.page('previous').draw('page');
        } else if ($(this).hasClass('next')) {
            table.page('next').draw('page');
        }
    });

    // FILTER FUNCTIONALITY
    // Toggle dropdown function
    window.toggleDropdown = function(filterId, event) {
        event.stopPropagation();
        event.preventDefault();
        console.log('Toggling dropdown:', filterId);
        const dropdown = document.querySelector(`.filter-dropdown:has(#${filterId}Content)`);
        if (!dropdown) {
            console.error('Dropdown not found for ID:', filterId);
            return;
        }
        
        const isCurrentlyShown = dropdown.classList.contains('show');
        
        // Close all dropdowns except those in modals
        document.querySelectorAll('.filter-dropdown:not(.modal .filter-dropdown)').forEach(d => {
            d.classList.remove('show');
        });
        
        // Open the clicked dropdown if it wasn't already open
        if (!isCurrentlyShown) {
            dropdown.classList.add('show');
            console.log('Dropdown opened:', filterId);
            
            // Focus on search input if available
            const searchInput = dropdown.querySelector('.dropdown-search input');
            if (searchInput) {
                setTimeout(() => searchInput.focus(), 100);
            }
        } else {
            dropdown.classList.remove('show');
            console.log('Dropdown closed:', filterId);
        }
    };

    // Function to close specific dropdown
    window.closeDropdown = function(filterId) {
        const dropdown = document.querySelector(`.filter-dropdown:has(#${filterId}Content)`);
        if (dropdown) {
            dropdown.classList.remove('show');
            console.log('Dropdown closed:', filterId);
        }
    };

    // Special function for toggle dropdown in edit modal
    window.toggleEditDropdown = function(filterId, event) {
        event.stopPropagation();
        event.preventDefault();
        
        console.log('Toggling edit dropdown:', filterId);
        
        const dropdown = document.querySelector(`.filter-dropdown:has(#${filterId}Content)`);
        if (!dropdown) {
            console.error('Edit dropdown not found for ID:', filterId);
            return;
        }
        
        const isCurrentlyShown = dropdown.classList.contains('show');
        
        // Close all other dropdowns in the same modal
        const modal = dropdown.closest('.modal');
        if (modal) {
            modal.querySelectorAll('.filter-dropdown').forEach(d => {
                if (d !== dropdown) {
                    d.classList.remove('show');
                }
            });
        }
        
        // Toggle the clicked dropdown
        if (!isCurrentlyShown) {
            dropdown.classList.add('show');
            console.log('Edit dropdown opened:', filterId);
            
            // Focus on search input if available
            const searchInput = dropdown.querySelector('.dropdown-search input');
            if (searchInput) {
                setTimeout(() => searchInput.focus(), 100);
            }
        } else {
            dropdown.classList.remove('show');
            console.log('Edit dropdown closed:', filterId);
        }
    };

    // Function to close specific edit dropdown
    window.closeEditDropdown = function(filterId) {
        const dropdown = document.querySelector(`.filter-dropdown:has(#${filterId}Content)`);
        if (dropdown) {
            dropdown.classList.remove('show');
            console.log('Edit dropdown closed:', filterId);
        }
    };

    // Close dropdowns when clicking outside, but exclude modal dropdowns
    document.addEventListener('click', function(event) {
        // Close non-modal dropdowns when clicking outside
        if (!event.target.closest('.filter-dropdown') && !event.target.closest('.modal')) {
            document.querySelectorAll('.filter-dropdown:not(.modal .filter-dropdown)').forEach(d => {
                d.classList.remove('show');
            });
            console.log('Closed non-modal dropdowns due to outside click');
        }
    });

    // Auto close dropdown after radio selection for non-modal dropdowns (excluding standard filter)
    document.addEventListener('change', function(event) {
        if (event.target.type === 'radio' && !event.target.closest('.modal')) {
            // Don't auto-close for standard filter since it now uses checkboxes
            const dropdown = event.target.closest('.filter-dropdown');
            if (dropdown) {
                setTimeout(() => {
                    dropdown.classList.remove('show');
                }, 100);
            }
        }
    });

    // Prevent modal dropdowns from being closed by outside clicks within modal
    $(document).on('click', '.modal .filter-dropdown', function(e) {
        e.stopPropagation();
    });

    // Update clause filter based on selected standard
    window.updateClauseFilter = function() {
        const selectedStandardCheckboxes = document.querySelectorAll('input[name="standar_filter"]:checked');
        const selectedStandardIds = Array.from(selectedStandardCheckboxes).map(cb => cb.value);
        const clauseGroup = document.getElementById('clauseCheckboxGroup');
        const clauseToggle = document.getElementById('clauseToggle');
        
        console.log('Updating clause filter for standards:', selectedStandardIds);
        
        // Clear existing clause checkboxes
        clauseGroup.innerHTML = '';
        
        if (selectedStandardIds.length === 0) {
            clauseGroup.innerHTML = '<div class="disabled-message">Select standard first</div>';
            clauseToggle.style.opacity = '0.6';
            clauseToggle.style.cursor = 'not-allowed';
            updateClauseText();
            return;
        }
        
        clauseToggle.style.opacity = '1';
        clauseToggle.style.cursor = 'pointer';
        
        let hasAvailableClauses = false;
        
        clausesData.forEach(clause => {
            // Show clause if it belongs to any of the selected standards
            if (selectedStandardIds.includes(clause.standar_id.toString())) {
                hasAvailableClauses = true;
                const checkboxItem = document.createElement('div');
                checkboxItem.className = 'option-item';
                checkboxItem.setAttribute('data-text', clause.nama_klausul.toLowerCase() + ' ' + clause.nama_standar.toLowerCase());
                
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.id = `klausul_${clause.id}`;
                checkbox.value = clause.id;
                checkbox.name = 'klausul_filter';
                checkbox.className = 'klausul-checkbox';
                checkbox.onchange = updateClauseText;
                
                const label = document.createElement('label');
                label.setAttribute('for', `klausul_${clause.id}`);
                label.textContent = `${clause.nama_klausul} (${clause.nama_standar})`;
                
                checkboxItem.appendChild(checkbox);
                checkboxItem.appendChild(label);
                clauseGroup.appendChild(checkboxItem);
            }
        });
        
        if (!hasAvailableClauses) {
            const emptyMessage = document.createElement('div');
            emptyMessage.className = 'no-results';
            emptyMessage.textContent = 'No clauses available for the selected standards.';
            clauseGroup.appendChild(emptyMessage);
        }
        
        updateClauseText();
    };

    // Update clause filter for edit modals
    window.updateEditClauseFilter = function(modalId) {
        console.log('Updating edit clause filter for modal:', modalId);
        
        // Find the checked checkboxes in this specific modal
        const modal = $(`#editModal${modalId}`);
        const selectedStandardCheckboxes = modal.find('input[name="standar_ids[]"]:checked');
        const selectedStandardIds = selectedStandardCheckboxes.map(function() { return $(this).val(); }).get();
        
        const clauseGroup = document.getElementById(`editClauseCheckboxGroup_${modalId}`);
        const clauseToggle = document.getElementById(`editClauseToggle_${modalId}`);
        if (!clauseGroup || !clauseToggle) {
            console.error('Clause elements not found for modal:', modalId);
            return;
        }
        
        // Clear existing clause checkboxes
        clauseGroup.innerHTML = '';
        
        if (selectedStandardIds.length === 0) {
            clauseGroup.innerHTML = '<div class="disabled-message">Select standard first</div>';
            clauseToggle.style.opacity = '0.6';
            clauseToggle.style.cursor = 'not-allowed';
            updateEditClauseText(modalId);
            return;
        }
        
        clauseToggle.style.opacity = '1';
        clauseToggle.style.cursor = 'pointer';
        
        // Get existing clause IDs from the table row
        const row = document.querySelector(`tr[data-standar]`);
        const existingClauseIds = row ? row.dataset.klausul.split(',') : [];
        
        let hasAvailableClauses = false;
        
        // Build clause checkboxes for all selected standards
        clausesData.forEach(clause => {
            if (selectedStandardIds.includes(clause.standar_id.toString())) {
                hasAvailableClauses = true;
                const checkboxItem = document.createElement('div');
                checkboxItem.className = 'option-item';
                checkboxItem.setAttribute('data-text', clause.nama_klausul.toLowerCase() + ' ' + clause.nama_standar.toLowerCase());
                
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.id = `edit_klausul_${modalId}_${clause.id}`;
                checkbox.value = clause.id;
                checkbox.name = `clauses[]`;
                checkbox.className = 'edit-klausul-checkbox';
                
                // Set checked status based on existing data
                if (existingClauseIds.includes(clause.id.toString())) {
                    checkbox.checked = true;
                }
                
                // Add change event
                checkbox.addEventListener('change', function() {
                    updateEditClauseText(modalId);
                });
                
                const label = document.createElement('label');
                label.setAttribute('for', `edit_klausul_${modalId}_${clause.id}`);
                label.textContent = `${clause.nama_klausul} (${clause.nama_standar})`;
                label.style.cursor = 'pointer';
                
                checkboxItem.appendChild(checkbox);
                checkboxItem.appendChild(label);
                clauseGroup.appendChild(checkboxItem);
            }
        });
        
        if (!hasAvailableClauses) {
            const emptyMessage = document.createElement('div');
            emptyMessage.className = 'no-results';
            emptyMessage.textContent = 'No clauses available for the selected standards.';
            clauseGroup.appendChild(emptyMessage);
        }
        
        updateEditClauseText(modalId);
    };

        // Add this JavaScript code to fix the Reset Filter functionality
    // Place this code in the $(document).ready() function after the existing filter code

    // Reset Filter button functionality
    $('#btnResetFilter').on('click', function() {
        console.log('Reset filter clicked');
        
        // 1. Reset Standard filter
        const checkedStandard = document.querySelector('input[name="standar_filter"]:checked');
        if (checkedStandard) {
            checkedStandard.checked = false;
        }
        document.getElementById('filterStandarText').textContent = 'Select Standard...';
        
        // 2. Reset Clause filter
        const checkedClauses = document.querySelectorAll('input[name="klausul_filter"]:checked');
        checkedClauses.forEach(checkbox => {
            checkbox.checked = false;
        });
        document.getElementById('filterKlausulText').textContent = 'Select Clause...';
        
        // Reset clause filter to disabled state
        const clauseGroup = document.getElementById('clauseCheckboxGroup');
        const clauseToggle = document.getElementById('clauseToggle');
        clauseGroup.innerHTML = '<div class="disabled-message">Select standard first</div>';
        clauseToggle.style.opacity = '0.6';
        clauseToggle.style.cursor = 'not-allowed';
        
        // 3. Reset Owner filter to "All"
        const checkedOwner = document.querySelector('input[name="pemilik_filter"]:checked');
        if (checkedOwner) {
            checkedOwner.checked = false;
        }
        const allOwnerRadio = document.getElementById('pemilik_all');
        if (allOwnerRadio) {
            allOwnerRadio.checked = true;
        }
        document.getElementById('filterPemilikText').textContent = 'All Document Owners';
        
        // 4. Reset Type filter to "All"
        const checkedType = document.querySelector('input[name="jenis_filter"]:checked');
        if (checkedType) {
            checkedType.checked = false;
        }
        const allTypeRadio = document.getElementById('jenis_all');
        if (allTypeRadio) {
            allTypeRadio.checked = true;
        }
        document.getElementById('filterJenisText').textContent = 'All Document Types';
        
        // 5. Close all dropdowns
        document.querySelectorAll('.filter-dropdown:not(.modal .filter-dropdown)').forEach(d => {
            d.classList.remove('show');
        });
        
        // 6. Clear all search inputs in dropdowns
        const searchInputs = [
            'standardSearchInput',
            'clauseSearchInput', 
            'ownerSearchInput',
            'typeSearchInput'
        ];
        
        searchInputs.forEach(inputId => {
            const input = document.getElementById(inputId);
            if (input) {
                input.value = '';
            }
        });
        
        // 7. Reset dropdown option visibility (clear any search filters)
        document.querySelectorAll('.option-item').forEach(item => {
            item.style.display = 'flex';
        });
        
        // Hide any "no results" messages
        document.querySelectorAll('.no-results').forEach(msg => {
            msg.style.display = 'none';
        });
        
        // 8. Reset DataTable filter and redraw
        // Clear any existing DataTable search filters
        $.fn.dataTable.ext.search = [];
        table.search('').draw();
        
        // 9. Reset custom search input
        $('#customSearch').val('');
        
        // 10. Hide no-data message and show table
        $('#no-data-message').hide();
        $('#dokumenTable').show();
    });

    // Alternative method: Create a comprehensive reset function
    function resetAllFilters() {
        // Reset all filter states
        clearStandardSelection();
        clearClauseSelection(); 
        clearOwnerSelection();
        clearTypeSelection();
        
        // Clear search inputs
        $('#customSearch').val('');
        
        // Reset DataTable
        $.fn.dataTable.ext.search = [];
        table.search('').draw();
        
        // Show all data
        $('#no-data-message').hide();
        $('#dokumenTable').show();
        
        console.log('Comprehensive reset completed');
    }

    // You can also bind the alternative method if preferred:
    // $('#btnResetFilter').on('click', resetAllFilters);

    // Update standard text display
    window.updateStandardText = function() {
        const selectedStandardCheckboxes = document.querySelectorAll('input[name="standar_filter"]:checked');
        const standardText = document.getElementById('filterStandarText');
        if (selectedStandardCheckboxes.length > 0) {
            if (selectedStandardCheckboxes.length === 1) {
                const label = document.querySelector(`label[for="${selectedStandardCheckboxes[0].id}"]`);
                const fullText = label.textContent;
                const truncatedText = fullText.length > 30 ? fullText.substring(0, 27) + '...' : fullText;
                standardText.textContent = truncatedText;
            } else {
                standardText.textContent = `${selectedStandardCheckboxes.length} standards selected`;
            }
        } else {
            standardText.textContent = 'Select Standard...';
        }
    };

    // Update standard text for edit modals
    window.updateEditStandardText = function(modalId) {
        const modal = $(`#editModal${modalId}`);
        const selectedStandardCheckboxes = modal.find('input[name="standar_ids[]"]:checked');
        const standardText = document.getElementById(`editFilterStandarText_${modalId}`);
        if (!standardText) {
            console.error('Standard text element not found for modal:', modalId);
            return;
        }
        if (selectedStandardCheckboxes.length > 0) {
            if (selectedStandardCheckboxes.length === 1) {
                const label = modal.find(`label[for="${selectedStandardCheckboxes.first().attr('id')}"]`);
                if (label.length > 0) {
                    const fullText = label.text();
                    const truncatedText = fullText.length > 30 ? fullText.substring(0, 27) + '...' : fullText;
                    standardText.textContent = truncatedText;
                }
            } else {
                standardText.textContent = `${selectedStandardCheckboxes.length} standards selected`;
            }
        } else {
            standardText.textContent = 'Select Standard...';
        }
    };

    // Update clause text display
    window.updateClauseText = function() {
        const selectedClauseCheckboxes = document.querySelectorAll('input[name="klausul_filter"]:checked');
        const clauseText = document.getElementById('filterKlausulText');
        if (selectedClauseCheckboxes.length > 0) {
            if (selectedClauseCheckboxes.length === 1) {
                const label = document.querySelector(`label[for="${selectedClauseCheckboxes[0].id}"]`);
                const fullText = label.textContent;
                const truncatedText = fullText.length > 30 ? fullText.substring(0, 27) + '...' : fullText;
                clauseText.textContent = truncatedText;
            } else {
                clauseText.textContent = `${selectedClauseCheckboxes.length} clauses selected`;
            }
        } else {
            clauseText.textContent = 'Select Clause...';
        }
    };

    // Update clause text for edit modals
    window.updateEditClauseText = function(modalId) {
        const modal = $(`#editModal${modalId}`);
        const selectedClauseCheckboxes = modal.find('input[name="clauses[]"]:checked');
        const clauseText = document.getElementById(`editFilterKlausulText_${modalId}`);
        if (!clauseText) {
            console.error('Clause text element not found for modal:', modalId);
            return;
        }
        if (selectedClauseCheckboxes.length > 0) {
            if (selectedClauseCheckboxes.length === 1) {
                const label = modal.find(`label[for="${selectedClauseCheckboxes.first().attr('id')}"]`);
                if (label.length > 0) {
                    const fullText = label.text();
                    const truncatedText = fullText.length > 30 ? fullText.substring(0, 27) + '...' : fullText;
                    clauseText.textContent = truncatedText;
                }
            } else {
                clauseText.textContent = `${selectedClauseCheckboxes.length} clauses selected`;
            }
        } else {
            clauseText.textContent = 'Select Clause...';
        }
    };

    // Clear standard selection
    window.clearStandardSelection = function() {
        const checkedStandards = document.querySelectorAll('input[name="standar_filter"]:checked');
        checkedStandards.forEach(checkbox => {
            checkbox.checked = false;
        });
        document.getElementById('filterStandarText').textContent = 'Select Standard...';
        document.getElementById('filterStandarContent').classList.remove('show');
        updateClauseFilter();
    };

    // Clear standard selection for edit modals
    window.clearEditStandardSelection = function(modalId) {
        const modal = $(`#editModal${modalId}`);
        const checkedStandards = modal.find('input[name="standar_ids[]"]:checked');
        
        checkedStandards.each(function() {
            $(this).prop('checked', false);
        });
        
        const standardText = document.getElementById(`editFilterStandarText_${modalId}`);
        if (standardText) {
            standardText.textContent = 'Select Standard...';
        }
        
        const standardContent = document.getElementById(`editFilterStandarContent_${modalId}`);
        if (standardContent) {
            $(standardContent).parent('.filter-dropdown').removeClass('show');
        }
        
        updateEditClauseFilter(modalId);
    };

    // Clear clause selection
    window.clearClauseSelection = function() {
        const checkedClauses = document.querySelectorAll('input[name="klausul_filter"]:checked');
        checkedClauses.forEach(checkbox => {
            checkbox.checked = false;
        });
        document.getElementById('filterKlausulText').textContent = 'Select Clause...';
        document.getElementById('filterKlausulContent').classList.remove('show');
    };

    // Clear clause selection for edit modals
    window.clearEditClauseSelection = function(modalId) {
        const modal = $(`#editModal${modalId}`);
        const checkedClauses = modal.find('input[name="clauses[]"]:checked');
        
        checkedClauses.each(function() {
            $(this).prop('checked', false);
        });
            
            const clauseText = document.getElementById(`editFilterKlausulText_${modalId}`);
            if (clauseText) {
                clauseText.textContent = 'Select Clause...';
            }
            
            const clauseContent = document.getElementById(`editFilterKlausulContent_${modalId}`);
            if (clauseContent) {
                $(clauseContent).parent('.filter-dropdown').removeClass('show');
            }
        };
        // Search functionality for standards
        window.filterStandardOptions = function() {
            const searchTerm = document.getElementById('standardSearchInput').value.toLowerCase();
            const optionItems = document.querySelectorAll('#standardCheckboxGroup .option-item');
            let hasVisibleItems = false;
            optionItems.forEach(item => {
                const text = item.getAttribute('data-text') || '';
                if (text.includes(searchTerm)) {
                    item.style.display = 'flex';
                    hasVisibleItems = true;
                } else {
                    item.style.display = 'none';
                }
            });
            let noResultsMsg = document.querySelector('#standardCheckboxGroup .no-results');
            if (!hasVisibleItems && optionItems.length > 0) {
                if (!noResultsMsg) {
                    noResultsMsg = document.createElement('div');
                    noResultsMsg.className = 'no-results';
                    noResultsMsg.textContent = 'No standards found';
                    document.getElementById('standardCheckboxGroup').appendChild(noResultsMsg);
                }
                noResultsMsg.style.display = 'block';
            } else if (noResultsMsg) {
                noResultsMsg.style.display = 'none';
            }
        };
        // Search functionality for clauses
        window.filterClauseOptions = function() {
            const searchTerm = document.getElementById('clauseSearchInput').value.toLowerCase();
            const optionItems = document.querySelectorAll('#clauseCheckboxGroup .option-item');
            let hasVisibleItems = false;
            optionItems.forEach(item => {
                const text = item.getAttribute('data-text') || '';
                if (text.includes(searchTerm)) {
                    item.style.display = 'flex';
                    hasVisibleItems = true;
                } else {
                    item.style.display = 'none';
                }
            });
            let noResultsMsg = document.querySelector('#clauseCheckboxGroup .no-results');
            if (!hasVisibleItems && optionItems.length > 0) {
                if (!noResultsMsg) {
                    noResultsMsg = document.createElement('div');
                    noResultsMsg.className = 'no-results';
                    noResultsMsg.textContent = 'No clauses found';
                    document.getElementById('clauseCheckboxGroup').appendChild(noResultsMsg);
                }
                noResultsMsg.style.display = 'block';
            } else if (noResultsMsg) {
                noResultsMsg.style.display = 'none';
            }
        };
        // Search functionality for standards in edit modals
        window.filterEditStandardOptions = function(modalId) {
            const searchTerm = document.getElementById(`editStandardSearchInput_${modalId}`).value.toLowerCase();
            const optionItems = document.querySelectorAll(`#editStandardCheckboxGroup_${modalId} .option-item`);
            let hasVisibleItems = false;
            optionItems.forEach(item => {
                const text = item.getAttribute('data-text') || '';
                if (text.includes(searchTerm)) {
                    item.style.display = 'flex';
                    hasVisibleItems = true;
                } else {
                    item.style.display = 'none';
                }
            });
            let noResultsMsg = document.querySelector(`#editStandardCheckboxGroup_${modalId} .no-results`);
            if (!hasVisibleItems && optionItems.length > 0) {
                if (!noResultsMsg) {
                    noResultsMsg = document.createElement('div');
                    noResultsMsg.className = 'no-results';
                    noResultsMsg.textContent = 'No standards found';
                    document.getElementById(`editStandardCheckboxGroup_${modalId}`).appendChild(noResultsMsg);
                }
                noResultsMsg.style.display = 'block';
            } else if (noResultsMsg) {
                noResultsMsg.style.display = 'none';
            }
        };
        // Search functionality for clauses in edit modals
        window.filterEditClauseOptions = function(modalId) {
            const searchTerm = document.getElementById(`editClauseSearchInput_${modalId}`).value.toLowerCase();
            const optionItems = document.querySelectorAll(`#editClauseCheckboxGroup_${modalId} .option-item`);
            let hasVisibleItems = false;
            optionItems.forEach(item => {
                const text = item.getAttribute('data-text') || '';
                if (text.includes(searchTerm)) {
                    item.style.display = 'flex';
                    hasVisibleItems = true;
                } else {
                    item.style.display = 'none';
                }
            });
            let noResultsMsg = document.querySelector(`#editClauseCheckboxGroup_${modalId} .no-results`);
            if (!hasVisibleItems && optionItems.length > 0) {
                if (!noResultsMsg) {
                    noResultsMsg = document.createElement('div');
                    noResultsMsg.className = 'no-results';
                    noResultsMsg.textContent = 'No clauses found';
                    document.getElementById(`editClauseCheckboxGroup_${modalId}`).appendChild(noResultsMsg);
                }
                noResultsMsg.style.display = 'block';
            } else if (noResultsMsg) {
                noResultsMsg.style.display = 'none';
            }
        };

        // ========== OWNER FILTER FUNCTIONS ==========
        // Update owner text display
        window.updateOwnerText = function() {
            const selectedOwnerRadio = document.querySelector('input[name="pemilik_filter"]:checked');
            const ownerText = document.getElementById('filterPemilikText');
            
            if (selectedOwnerRadio) {
                const label = document.querySelector(`label[for="${selectedOwnerRadio.id}"]`);
                ownerText.textContent = label.textContent;
            } else {
                ownerText.textContent = 'All Document Owners';
            }
        };

        // Clear owner selection
        window.clearOwnerSelection = function() {
            // Uncheck all owner radios
            const checkedOwner = document.querySelector('input[name="pemilik_filter"]:checked');
            if (checkedOwner) {
                checkedOwner.checked = false;
            }
            
            // Check the "All" option
            const allOwnerRadio = document.getElementById('pemilik_all');
            if (allOwnerRadio) {
                allOwnerRadio.checked = true;
            }
            
            document.getElementById('filterPemilikText').textContent = 'All Document Owners';
            document.getElementById('filterPemilikContent').parentElement.classList.remove('show');
        };

        // Search functionality for owners
        window.filterOwnerOptions = function() {
            const searchTerm = document.getElementById('ownerSearchInput').value.toLowerCase();
            const optionItems = document.querySelectorAll('#ownerRadioGroup .option-item');
            let hasVisibleItems = false;

            optionItems.forEach(item => {
                const text = item.getAttribute('data-text') || '';
                if (text.includes(searchTerm)) {
                    item.style.display = 'flex';
                    hasVisibleItems = true;
                } else {
                    item.style.display = 'none';
                }
            });

            // Handle no results message
            let noResultsMsg = document.querySelector('#ownerRadioGroup .no-results');
            if (!hasVisibleItems && optionItems.length > 0) {
                if (!noResultsMsg) {
                    noResultsMsg = document.createElement('div');
                    noResultsMsg.className = 'no-results';
                    noResultsMsg.textContent = 'No owners found';
                    document.getElementById('ownerRadioGroup').appendChild(noResultsMsg);
                }
                noResultsMsg.style.display = 'block';
            } else if (noResultsMsg) {
                noResultsMsg.style.display = 'none';
            }
        };

        // ========== TYPE FILTER FUNCTIONS ==========
        // Update type text display
        window.updateTypeText = function() {
            const selectedTypeRadio = document.querySelector('input[name="jenis_filter"]:checked');
            const typeText = document.getElementById('filterJenisText');
            
            if (selectedTypeRadio) {
                const label = document.querySelector(`label[for="${selectedTypeRadio.id}"]`);
                typeText.textContent = label.textContent;
            } else {
                typeText.textContent = 'All Document Types';
            }
        };

        // Clear type selection
        window.clearTypeSelection = function() {
            // Uncheck all type radios
            const checkedType = document.querySelector('input[name="jenis_filter"]:checked');
            if (checkedType) {
                checkedType.checked = false;
            }
            
            // Check the "All" option
            const allTypeRadio = document.getElementById('jenis_all');
            if (allTypeRadio) {
                allTypeRadio.checked = true;
            }
            
            document.getElementById('filterJenisText').textContent = 'All Document Types';
            document.getElementById('filterJenisContent').parentElement.classList.remove('show');
        };

        // Search functionality for types
        window.filterTypeOptions = function() {
            const searchTerm = document.getElementById('typeSearchInput').value.toLowerCase();
            const optionItems = document.querySelectorAll('#typeRadioGroup .option-item');
            let hasVisibleItems = false;

            optionItems.forEach(item => {
                const text = item.getAttribute('data-text') || '';
                if (text.includes(searchTerm)) {
                    item.style.display = 'flex';
                    hasVisibleItems = true;
                } else {
                    item.style.display = 'none';
                }
            });

            // Handle no results message
            let noResultsMsg = document.querySelector('#typeRadioGroup .no-results');
            if (!hasVisibleItems && optionItems.length > 0) {
                if (!noResultsMsg) {
                    noResultsMsg = document.createElement('div');
                    noResultsMsg.className = 'no-results';
                    noResultsMsg.textContent = 'No document types found';
                    document.getElementById('typeRadioGroup').appendChild(noResultsMsg);
                }
                noResultsMsg.style.display = 'block';
            } else if (noResultsMsg) {
                noResultsMsg.style.display = 'none';
            }
        };

        // ========== UPDATED FILTER APPLICATION ==========
        // Apply filters - Updated to use the new dropdown values
// Replace the existing filter application section with this corrected code

        // Apply filters - CORRECTED VERSION
        $('#btnFilter').on('click', function() {
            console.log('Filter button clicked');
            
            // Get filter values
            const selectedStandardCheckboxes = document.querySelectorAll('input[name="standar_filter"]:checked');
            const selectedClauseCheckboxes = document.querySelectorAll('input[name="klausul_filter"]:checked');
            const selectedOwnerRadio = document.querySelector('input[name="pemilik_filter"]:checked');
            const selectedTypeRadio = document.querySelector('input[name="jenis_filter"]:checked');
            
            const selectedStandards = Array.from(selectedStandardCheckboxes).map(cb => cb.value);
            const selectedClauses = Array.from(selectedClauseCheckboxes).map(cb => cb.value);
            const selectedOwner = selectedOwnerRadio ? selectedOwnerRadio.value : '';
            const selectedType = selectedTypeRadio ? selectedTypeRadio.value : '';

            console.log('Filter values:', {
                standards: selectedStandards,
                clauses: selectedClauses,
                owner: selectedOwner,
                type: selectedType
            });

            // Clear any existing search filters
            $.fn.dataTable.ext.search = [];

            // Add new filter function
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                const row = table.row(dataIndex).node();
                const rowStandar = $(row).data('standar') ? $(row).data('standar').toString().split(',') : [];
                const rowKlausul = $(row).data('klausul') ? $(row).data('klausul').toString().split(',') : [];
                const rowPemilik = $(row).data('pemilik') || '';
                
                // Get document type from the table data (column index 5)
                const rowDocumentType = data[5] || ''; // Column 5 is Document Type

                // Standard filter - Check if any selected standard matches the row
                const standarMatch = selectedStandards.length === 0 || 
                    selectedStandards.some(standard => rowStandar.includes(standard));
                
                // Clause filter
                const klausulMatch = selectedClauses.length === 0 || 
                    selectedClauses.some(clause => rowKlausul.includes(clause));
                
                // Owner filter - FIXED: Check for exact match and handle "All" option
                let pemilikMatch = true;
                if (selectedOwner && selectedOwner !== '') {
                    // If "All Document Owners" is selected or empty, show all
                    if (selectedOwner === 'All Document Owners' || selectedOwner === 'all') {
                        pemilikMatch = true;
                    } else {
                        // Exact match for specific owner
                        pemilikMatch = rowPemilik === selectedOwner;
                    }
                }
                
                // Type filter - FIXED: Check against type ID from data attributes or table content
                let jenisMatch = true;
                if (selectedType && selectedType !== '') {
                    // If "All Document Types" is selected, show all
                    if (selectedType === 'All Document Types' || selectedType === 'all') {
                        jenisMatch = true;
                    } else {
                        // Find the selected type name from the kategori_dokumen data
                        const selectedTypeLabel = document.querySelector(`label[for="jenis_${selectedType}"]`);
                        const selectedTypeName = selectedTypeLabel ? selectedTypeLabel.textContent : '';
                        
                        // Match against the document type column content
                        jenisMatch = rowDocumentType.includes(selectedTypeName);
                    }
                }

                console.log('Row check:', {
                    rowIndex: dataIndex,
                    standarMatch,
                    klausulMatch, 
                    pemilikMatch,
                    jenisMatch,
                    rowPemilik,
                    selectedOwner,
                    rowDocumentType,
                    selectedType,
                    rowStandar,
                    selectedStandards
                });

                return standarMatch && klausulMatch && pemilikMatch && jenisMatch;
            });

            // Apply the filter
            table.draw();
            
            // Check if there are visible rows
            checkVisibleRows();
            
            // Close all dropdowns after filtering
            document.querySelectorAll('.filter-dropdown:not(.modal .filter-dropdown)').forEach(d => {
                d.classList.remove('show');
            });
        });

        // Function to check if there are visible rows and show/hide no-data message
        function checkVisibleRows() {
            const visibleRows = table.rows({ filter: 'applied' }).data().length;
            console.log('Visible rows after filter:', visibleRows);
            
            if (visibleRows === 0) {
                $('#no-data-message').show();
                $('#dokumenTable').hide();
                $('.table-info-bottom').html('<small class="text-muted">Showing 0 to 0 of 0 entries</small>');
            } else {
                $('#no-data-message').hide();
                $('#dokumenTable').show();
            }
        }
        // Modal handling
        $(document).on('show.bs.modal', '.modal', function() {
            const modalId = $(this).attr('id').replace('editModal', '');
            const modal = $(this);
            
            console.log('Modal opened:', modalId);
        
            
            setTimeout(() => {
                // Update standard text
                updateEditStandardText(modalId);
                
                // Check if standards are selected and update clause filter accordingly
                const selectedStandardCheckboxes = modal.find(`input[name="standar_ids[]"]:checked`);
                if (selectedStandardCheckboxes.length > 0) {
                    updateEditClauseFilter(modalId);
                } else {
                    const clauseGroup = document.getElementById(`editClauseCheckboxGroup_${modalId}`);
                    const clauseToggle = document.getElementById(`editClauseToggle_${modalId}`);
                    if (clauseGroup && clauseToggle) {
                        clauseGroup.innerHTML = '<div class="disabled-message">Select standard first</div>';
                        clauseToggle.style.opacity = '0.6';
                        clauseToggle.style.cursor = 'not-allowed';
                    }
                }
                
                updateEditClauseText(modalId);
                // Set proper z-index for dropdowns in modal
                modal.find('.filter-dropdown').css({
                    'position': 'relative',
                    'z-index': '1000006'
                });
                modal.find('.filter-dropdown-content').css({
                    'z-index': '1000007',
                    'position': 'absolute'
                });
                
                // Bind click events for modal dropdowns
                modal.find('.filter-toggle').off('click').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const dropdownContent = $(this).siblings('.filter-dropdown-content');
                    const dropdown = $(this).parent('.filter-dropdown');
                    
                    // Close other dropdowns in this modal
                    modal.find('.filter-dropdown').not(dropdown).removeClass('show');
                    
                    // Toggle current dropdown
                    dropdown.toggleClass('show');
                    
                    // Focus search input if available
                    if (dropdown.hasClass('show')) {
                        const searchInput = dropdown.find('.dropdown-search input');
                        if (searchInput.length) {
                            setTimeout(() => searchInput.focus(), 100);
                        }
                    }
                });
                
                console.log('Modal initialized:', modalId);
            }, 100);
        });
        // Close modal dropdowns when clicking outside
        $(document).on('click', function(event) {
            // Close non-modal dropdowns
            if (!$(event.target).closest('.filter-dropdown').length && !$(event.target).closest('.modal').length) {
                $('.filter-dropdown:not(.modal .filter-dropdown)').removeClass('show');
            }
            
            // Close modal dropdowns when clicking outside modal or on modal backdrop
            if (!$(event.target).closest('.modal-content').length) {
                $('.modal .filter-dropdown').removeClass('show');
            }
        });
        
        // Handle clicks within modal
        $(document).on('click', '.modal', function(e) {
            // If clicking on modal backdrop (not modal content), close dropdowns
            if (e.target === this) {
                $('.modal .filter-dropdown').removeClass('show');
            }
        });
        
        // Handle clicks within modal content
        $(document).on('click', '.modal-content', function(e) {
            // If clicking outside any dropdown within modal content, close all dropdowns
            if (!$(e.target).closest('.filter-dropdown').length) {
                $('.modal .filter-dropdown').removeClass('show');
            }
        });
        
        // Prevent dropdown from closing when clicking inside it
        $(document).on('click', '.modal .filter-dropdown', function(e) {
            e.stopPropagation();
        });
        
        // Auto close dropdown after selection
        $(document).on('change', '.modal input[type="radio"]', function(e) {
            // Close the dropdown after radio selection
            const dropdown = $(this).closest('.filter-dropdown');
            setTimeout(() => {
                dropdown.removeClass('show');
            }, 100);
        });
        
        // Auto close dropdown after checkbox selection (optional - you can remove this if you want checkboxes to stay open)
        $(document).on('change', '.modal input[type="checkbox"]', function(e) {
            // Optional: uncomment the line below if you want dropdown to close after each checkbox selection
            // const dropdown = $(this).closest('.filter-dropdown');
            // setTimeout(() => dropdown.removeClass('show'), 100);
        });
        // Form submission handling - FIXED VERSION
        if (documentPrivilege.can_update) {
            $('.edit-form').on('submit', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const formData = new FormData(this);
                const modalId = '#editModal' + formData.get('id');
                $(modalId).modal('hide');
                console.log('Form submission data:', Object.fromEntries(formData));
                
                Swal.fire({
                    title: 'Saving...',
                    text: 'Processing data',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                $.ajax({
                    url: '<?= base_url('document-list/update') ?>',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    
                    success: function(response) {
                        console.log('Update response:', response);
                        
                        // FIXED: Handle both success dan error response di success callback
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: response.swal.icon,
                                title: response.swal.title,
                                text: response.swal.text,
                                confirmButtonText: 'OK',
                                confirmButtonColor: response.swal.confirmButtonColor || '#28a745'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            // Status error tapi masuk ke success callback
                            Swal.fire({
                                icon: response.swal.icon || 'error',
                                title: response.swal.title || 'Error',
                                text: response.swal.text || response.message || 'Terjadi kesalahan',
                                confirmButtonText: 'OK',
                                confirmButtonColor: response.swal.confirmButtonColor || '#dc3545'
                            }).then(() => {
                                location.reload();
                            });
                        }
                    },
                    
                    error: function(xhr, status, error) {
                        console.error('Update error:', xhr.responseJSON, status, error);
                        
                        let response = xhr.responseJSON;
                        
                        // FIXED: Handle response dari controller dengan benar
                        if (response && response.swal) {
                            Swal.fire({
                                icon: response.swal.icon || 'error',
                                title: response.swal.title || 'Error',
                                text: response.swal.text || response.message || 'Terjadi kesalahan',
                                confirmButtonText: response.swal.confirmButtonText || 'OK',
                                confirmButtonColor: response.swal.confirmButtonColor || '#dc3545'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            // FIXED: Fallback untuk error tanpa response.swal
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: response?.message || error || 'Failed to update document',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#dc3545'
                            }).then(() => {
                                location.reload();
                            });
                        }
                    }
                });
            });
        }
        // Delete confirmation
        if (documentPrivilege.can_delete) {
            window.confirmDelete = function(event, form) {
                event.preventDefault();
                event.stopPropagation();
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This action cannot be undone!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, deleted it!',
                    cancelButtonText: 'cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Deleting...',
                            text: 'Please wait a moment',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        form.submit();
                    }
                });
                return false;
            };
        }
        
        // Initialize clause filter on page load
        updateClauseFilter();
        // Nonaktifkan Choices.js untuk dropdown kustom
        document.querySelectorAll('.no-choices').forEach(element => {
            if (element.querySelector('select')) {
                element.querySelector('select').classList.add('no-choices');
            }
        });
    });
</script>
<!-- Flash Messages -->
<?php if (session()->getFlashdata('success')): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: '<?= esc(session()->getFlashdata('success')) ?>',
            confirmButtonColor: '#6f42c1'
        });
    </script>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: '<?= esc(session()->getFlashdata('error')) ?>',
            confirmButtonColor: '#dc3545'
        });
    </script>
<?php endif; ?>
<?php if (session()->getFlashdata('warning')): ?>
    <script>
        Swal.fire({
            icon: 'warning',
            title: 'Warning',
            text: '<?= esc(session()->getFlashdata('warning')) ?>',
            confirmButtonColor: '#ffc107'
        });
    </script>
<?php endif; ?>
<?= $this->endSection(); ?>



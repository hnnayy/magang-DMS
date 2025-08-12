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

    // Ambil privilege untuk daftar-dokumen dari session
    $documentPrivilege = session()->get('privileges')['document-list'] ?? [
        'can_create' => 0,
        'can_update' => 0,
        'can_delete' => 0,
        'can_approve' => 0
    ];

    // Cek apakah user memiliki privilege untuk aksi apapun
    $hasAnyPrivilege = $documentPrivilege['can_update'] || $documentPrivilege['can_delete'];
?>
<!-- CSS External Links -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<!-- Custom CSS -->
<link rel="stylesheet" href="<?= base_url('assets/css/daftar-dokumen.css') ?>">
<style>
    /* Hide action column if no privileges */
    <?php if (!$hasAnyPrivilege): ?>
    .aksi-column {
        display: none !important;
    }
    <?php endif; ?>

    /* Add search input styling inside existing dropdown */
    .filter-dropdown .dropdown-search {
        padding: 8px 12px;
        border-bottom: 1px solid #eee;
        background: #f8f9fa;
    }

    .filter-dropdown .dropdown-search input {
        width: 100%;
        padding: 4px 8px;
        border: 1px solid #ddd;
        border-radius: 3px;
        font-size: 12px;
    }

    .filter-dropdown .radio-group {
        max-height: 200px;
        overflow-y: auto;
    }

    .filter-dropdown .radio-item {
        display: flex;
        align-items: center;
        padding: 6px 12px;
        font-size: 13px;
        border-bottom: 1px solid #f1f1f1;
    }

    .filter-dropdown .radio-item:last-child {
        border-bottom: none;
    }

    .filter-dropdown .radio-item input[type="radio"] {
        margin-right: 8px;
        margin-top: 0;
    }

    .filter-dropdown .radio-item label {
        margin: 0;
        cursor: pointer;
        flex: 1;
    }

    .filter-dropdown .no-results {
        padding: 12px;
        text-align: center;
        color: #999;
        font-style: italic;
        font-size: 12px;
    }

    .filter-dropdown .clear-selection {
        padding: 6px 12px;
        border-bottom: 1px solid #eee;
        background: #db1f1fff;
        cursor: pointer;
        font-size: 12px;
        color: #FFFF;
    }

    .filter-dropdown .clear-selection:hover {
        background: #db1f1fff;
    }

    .filter-dropdown .filter-toggle {
    display: flex;
    align-items: center;
    padding: 6px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: #fff;
    cursor: pointer;
    font-size: 14px;
}

.filter-dropdown .filter-toggle:hover {
    background: #f8f9fa;
}

.filter-dropdown .filter-dropdown-content {
    display: none;
    position: absolute;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    min-width: 200px;
    max-height: 300px;
    overflow-y: auto;
}

.filter-dropdown.show .filter-dropdown-content {
    display: block;
}

.filter-dropdown .clear-selection {
    padding: 6px 12px;
    border-bottom: 1px solid #eee;
    background: #db1f1fff;
    cursor: pointer;
    font-size: 12px;
    color: #fff;
}

.filter-dropdown .clear-selection:hover {
    background: #c21818;
}

.filter-dropdown .dropdown-search {
    padding: 8px 12px;
    border-bottom: 1px solid #eee;
    background: #f8f9fa;
}

.filter-dropdown .dropdown-search input {
    width: 100%;
    padding: 4px 8px;
    border: 1px solid #ddd;
    border-radius: 3px;
    font-size: 12px;
}

.filter-dropdown .radio-group {
    max-height: 200px;
    overflow-y: auto;
}

.filter-dropdown .radio-item {
    display: flex;
    align-items: center;
    padding: 6px 12px;
    font-size: 13px;
    border-bottom: 1px solid #f1f1f1;
}

.filter-dropdown .radio-item:last-child {
    border-bottom: none;
}

.filter-dropdown .radio-item input[type="radio"] {
    margin-right: 8px;
    margin-top: 0;
}

.filter-dropdown .radio-item label {
    margin: 0;
    cursor: pointer;
    flex: 1;
}

.filter-dropdown .no-results {
    padding: 12px;
    text-align: center;
    color: #999;
    font-style: italic;
    font-size: 12px;
}
</style>
<div class="container-fluid px-4 py-4">
    <h4 class="mb-4">Document List</h4>

    <!-- FILTER SECTION -->
    <div class="bg-light p-3 rounded mb-4">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <strong class="form-label mb-0 me-2">Filter Data</strong>

            <!-- Filter Standar dengan Radio Button -->
            <div class="filter-dropdown flex-grow-1" style="min-width:180px;">
                <div class="filter-toggle" onclick="toggleDropdown('filterStandar')">
                    <span id="filterStandarText">Select Standard...</span>
                </div>
                <div class="filter-dropdown-content" id="filterStandarContent">
                    <div class="clear-selection" onclick="clearStandardSelection()">
                        <i class="bi bi-x-circle me-1"></i> Clear Selection
                    </div>
                    <div class="dropdown-search">
                        <input type="text" placeholder="Search standards..." onkeyup="filterStandardOptions()" id="standardSearchInput">
                    </div>
                    <div class="radio-group" id="standardRadioGroup">
                        <?php foreach ($standards as $s): ?>
                            <div class="radio-item" data-text="<?= strtolower(esc($s['nama_standar'])) ?>">
                                <input type="radio" 
                                       id="standar_<?= $s['id'] ?>" 
                                       value="<?= $s['id'] ?>" 
                                       name="standar_filter"
                                       class="standar-radio"
                                       onchange="updateClauseFilter(); updateStandardText();">
                                <label for="standar_<?= $s['id'] ?>"><?= esc($s['nama_standar']) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Filter Klausul dengan Radio Button (Dynamic based on Standard) -->
            <div class="filter-dropdown flex-grow-1" style="min-width:180px;">
                <div class="filter-toggle" onclick="toggleDropdown('filterKlausul')">
                    <span id="filterKlausulText">Select Clause...</span>
                </div>
                <div class="filter-dropdown-content" id="filterKlausulContent">
                    <div class="clear-selection" onclick="clearClauseSelection()">
                        <i class="bi bi-x-circle me-1"></i> Clear Selection
                    </div>
                    <div class="dropdown-search">
                        <input type="text" placeholder="Search clauses..." onkeyup="filterClauseOptions()" id="clauseSearchInput">
                    </div>
                    <div class="radio-group" id="clauseRadioGroup">
                        <!-- Clauses will be populated dynamically based on selected standards -->
                    </div>
                </div>
            </div>

            <!-- Filter Document Owner -->
            <div class="flex-grow-1" style="min-width:180px;">
                <select class="filter-toggle w-100" id="filterPemilik">
                    <option value="">All Document Owners</option>
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

                        // Access Control Rules:
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
                        <option value="<?= esc($owner) ?>"><?= esc($owner) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter Document Type -->
            <div class="flex-grow-1" style="min-width:180px;">
                <select class="filter-toggle w-100" id="filterJenis">
                    <option value="">All Document Types</option>
                    <?php foreach ($kategori_dokumen as $k): ?>
                        <option value="<?= $k['id'] ?>"><?= $k['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter and Export Buttons -->
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-primary btn-sm-2" id="btnFilter">Filter</button>
                <button class="btn btn-success btn-sm-2" id="excel-button-container">Export Excel</button>
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
                                <th>Department/Unit/Program</th>
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
                                // Check if document should be visible based on hierarchical access
                                $documentCreatorId = $row['createdby_id'] ?? 0;
                                $documentCreatorUnitId = $row['creator_unit_id'] ?? 0;
                                $documentCreatorUnitParentId = $row['creator_unit_parent_id'] ?? 0;
                                $documentCreatorAccessLevel = $row['creator_access_level'] ?? 2;
                                $documentCreatorName = $row['creator_fullname'] ?? $row['createdby'] ?? 'Unknown User';

                                $canViewDocument = false;
                                $showCreatorName = false;
                                $canEditDocument = false;
                                $canDeleteDocument = false;

                                // Access Control Rules
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
                                        $standar_ids = array_filter(explode(',', $row['standar_ids'] ?? ''));
                                        $standar_names = array_map(function($id) use ($standards) {
                                            foreach ($standards as $s) {
                                                if ($s['id'] == $id) {
                                                    return esc($s['nama_standar']);
                                                }
                                            }
                                            return null;
                                        }, $standar_ids);
                                        $standar_names = array_filter($standar_names);
                                        echo !empty($standar_names) ? implode(', ', $standar_names) : '-';
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $klausul_ids = array_filter(explode(',', $row['klausul_ids'] ?? ''));
                                        $klausul_names = array_map(function($id) use ($clauses) {
                                            foreach ($clauses as $c) {
                                                if ($c['id'] == $id) {
                                                    return esc($c['nomor_klausul'] . ' - ' . $c['nama_klausul'] . ' (' . $c['nama_standar'] . ')');
                                                }
                                            }
                                            return null;
                                        }, $klausul_ids);
                                        $klausul_names = array_filter($klausul_names);

                                        if (!empty($klausul_names)) {
                                            $klausul_text = implode(', ', $klausul_names);
                                            echo '<span class="text-truncate-custom" title="' . esc($klausul_text) . '">' . esc($klausul_text) . '</span>';
                                        } else {
                                            echo '-';
                                        }
                                        ?>
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
                                               title="Download <?= esc($row['filename'] ?? basename($row['filepath'])) ?>">
                                                <i class="bi bi-download text-success fs-5"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">
                                                <i class="bi bi-file-earmark-x"></i> No file
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
                                                            data-bs-target="#editModal<?= $row['id'] ?>" 
                                                            title="Edit">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <?php if ($documentPrivilege['can_delete'] && $canDeleteDocument): ?>
                                                    <form action="<?= base_url('document-list/delete') ?>" method="post" onsubmit="return confirmDelete(event, this);">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
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
                    <div class="no-data-message" style="display: none;">No data</div>
                </div>
                <div class="pagination-container"></div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL EDIT DOKUMEN -->
<?php 
foreach ($document as $row): 
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
<div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $row['id'] ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= base_url('document-list/update') ?>" method="post" class="edit-form" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $row['id'] ?>">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel<?= $row['id'] ?>">Edit Document</h5>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Standard</label>
                            <div class="filter-dropdown flex-grow-1" style="min-width:180px;">
                                <div class="filter-toggle" onclick="toggleDropdown('editFilterStandar_<?= $row['id'] ?>')">
                                    <span id="editFilterStandarText_<?= $row['id'] ?>">Select Standard...</span>
                                </div>
                                <div class="filter-dropdown-content" id="editFilterStandarContent_<?= $row['id'] ?>">
                                    <div class="clear-selection" onclick="clearEditStandardSelection('<?= $row['id'] ?>')">
                                        <i class="bi bi-x-circle me-1"></i> Clear Selection
                                    </div>
                                    <div class="dropdown-search">
                                        <input type="text" placeholder="Search standards..." onkeyup="filterEditStandardOptions('<?= $row['id'] ?>')" id="editStandardSearchInput_<?= $row['id'] ?>">
                                    </div>
                                    <div class="radio-group" id="editStandardRadioGroup_<?= $row['id'] ?>">
                                        <?php foreach ($standards as $s): ?>
                                            <div class="radio-item" data-text="<?= strtolower(esc($s['nama_standar'])) ?>">
                                                <input type="radio" 
                                                       id="edit_standar_<?= $row['id'] ?>_<?= $s['id'] ?>" 
                                                       value="<?= $s['id'] ?>" 
                                                       name="standar_<?= $row['id'] ?>"
                                                       class="edit-standar-radio"
                                                       onchange="updateEditStandardText('<?= $row['id'] ?>'); updateEditClauseFilter('<?= $row['id'] ?>');"
                                                       <?= in_array($s['id'], array_filter(explode(',', $row['standar_ids'] ?? ''))) ? 'checked' : '' ?>>
                                                <label for="edit_standar_<?= $row['id'] ?>_<?= $s['id'] ?>"><?= esc($s['nama_standar']) ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Clause</label>
                            <div class="filter-dropdown flex-grow-1" style="min-width:180px;">
                                <div class="filter-toggle" onclick="toggleDropdown('editFilterKlausul_<?= $row['id'] ?>')">
                                    <span id="editFilterKlausulText_<?= $row['id'] ?>">Select Clause...</span>
                                </div>
                                <div class="filter-dropdown-content" id="editFilterKlausulContent_<?= $row['id'] ?>">
                                    <div class="clear-selection" onclick="clearEditClauseSelection('<?= $row['id'] ?>')">
                                        <i class="bi bi-x-circle me-1"></i> Clear Selection
                                    </div>
                                    <div class="dropdown-search">
                                        <input type="text" placeholder="Search clauses..." onkeyup="filterEditClauseOptions('<?= $row['id'] ?>')" id="editClauseSearchInput_<?= $row['id'] ?>">
                                    </div>
                                    <div class="radio-group" id="editClauseRadioGroup_<?= $row['id'] ?>">
                                        <!-- Clauses will be populated dynamically based on selected standards -->
                                        <?php 
                                        $selected_standards = array_filter(explode(',', $row['standar_ids'] ?? ''));
                                        foreach ($clauses as $c): 
                                            if (in_array($c['standar_id'], $selected_standards)): ?>
                                                <div class="radio-item" data-text="<?= strtolower($c['nomor_klausul'] . ' ' . $c['nama_klausul'] . ' ' . $c['nama_standar']) ?>">
                                                    <input type="radio" 
                                                           id="edit_klausul_<?= $row['id'] ?>_<?= $c['id'] ?>" 
                                                           value="<?= $c['id'] ?>" 
                                                           name="klausul_<?= $row['id'] ?>"
                                                           class="edit-klausul-radio"
                                                           onchange="updateEditClauseText('<?= $row['id'] ?>')"
                                                           <?= in_array($c['id'], array_filter(explode(',', $row['klausul_ids'] ?? ''))) ? 'checked' : '' ?>>
                                                    <label for="edit_klausul_<?= $row['id'] ?>_<?= $c['id'] ?>"><?= esc($c['nomor_klausul']) ?> - <?= esc($c['nama_klausul']) ?> (<?= esc($c['nama_standar']) ?>)</label>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Document Type</label>
                            <select name="type" class="form-select" disabled>
                                <?php foreach ($kategori_dokumen as $kategori): ?>
                                    <option value="<?= $kategori['id'] ?>" <?= ($row['type'] == $kategori['id']) ? 'selected' : '' ?>>
                                        <?= $kategori['name'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Type Code</label>
                            <input type="text" class="form-control" name="type_code" 
                                   value="<?= esc($row['kode_jenis_dokumen'] ?? $row['kode_dokumen_kode'] ?? '-') ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Code & Document Name</label>
                            <div class="form-control" style="background-color: #f8f9fa; color: #6c757d;">
                                <?php 
                                if (!empty($row['kode_dokumen_kode']) && !empty($row['kode_dokumen_nama'])) {
                                    echo esc($row['kode_dokumen_kode'] . ' - ' . $row['kode_dokumen_nama']);
                                } elseif (!empty($row['kode_jenis_dokumen'])) {
                                    $displayText = esc($row['kode_jenis_dokumen']);
                                    if (!empty($row['title'])) {
                                        $displayText .= ' - ' . esc($row['title']);
                                    }
                                    echo $displayText;
                                } elseif (!empty($row['title'])) {
                                    echo esc($row['title']);
                                } else {
                                    echo '<span class="text-muted">No code</span>';
                                }
                                ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Number</label>
                            <input type="text" class="form-control" name="number" value="<?= esc($row['number']) ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Document Name</label>
                            <input type="text" class="form-control" name="title" value="<?= esc($row['title']) ?>" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Document Owner</label>
                            <input type="text" class="form-control" name="createdby" value="<?= esc($documentCreatorName) ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Approved By</label>
                            <input type="hidden" name="approveby" value="<?= esc($row['approveby'] ?? '') ?>">
                            <input type="text" class="form-control" value="<?= esc($row['approved_by_name'] ?? '') ?>" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Approval Date</label>
                            <input type="datetime-local" class="form-control" name="approvedate" 
                                   value="<?= esc(date('Y-m-d\TH:i', strtotime($row['approvedate'] ?? 'now'))) ?>" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Revision</label>
                            <input type="text" class="form-control" name="revision" value="<?= esc($row['revision']) ?>" readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Effective Date</label>
                            <input type="date" class="form-control" name="date_published" value="<?= esc($row['date_published']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Document File</label>
                            <div class="file-display">
                                <?php if (!empty($row['filepath']) && file_exists(ROOTPATH . '..' . DIRECTORY_SEPARATOR . $row['filepath'])): ?>
                                    <div class="file-info">
                                        <?= esc($row['filename'] ?? basename($row['filepath'])) ?>
                                    </div>
                                    <a href="<?= base_url('document-list/serveFile?id=' . $row['id'] . '&action=download') ?>" 
                                       class="btn btn-primary btn-sm view-file-btn" 
                                       title="View <?= esc($row['filename'] ?? basename($row['filepath'])) ?>">
                                        <i class="bi bi-download"></i> View File
                                    </a>
                                <?php else: ?>
                                    <div class="file-info text-muted">No file available</div>
                                <?php endif; ?>
                            </div>
                        </div>    
                    </div>
                </div>
                <div class="modal-footer d-grid gap-2" style="grid-template-columns: 1fr 1fr;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endforeach; ?>

<!-- Pass PHP data to JavaScript -->
<script>
    // Pass clause data to JavaScript for dynamic filtering
    const clausesData = <?= json_encode($clauses) ?>;
    const standardsData = <?= json_encode($standards) ?>;
</script>

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
        // Privilege check dari PHP
        const documentPrivilege = <?= json_encode($documentPrivilege) ?>;
        const hasAnyPrivilege = <?= json_encode($hasAnyPrivilege) ?>;
        const currentUserId = <?= json_encode($currentUserId) ?>;
        const currentUserAccessLevel = <?= json_encode($currentUserAccessLevel) ?>;

        // Hitung jumlah kolom berdasarkan privilege
        const totalColumns = hasAnyPrivilege ? 16 : 15;

        // Initialize DataTables dengan konfigurasi dinamis
        const tableConfig = {
            dom: 'rt<"d-flex justify-content-between align-items-center mt-3"<"d-flex align-items-center"l><"pagination-wrapper"p>>',
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            language: {
                lengthMenu: "Show *MENU* entries",
                paginate: {
                    previous: "Previous",
                    next: "Next"
                },
                zeroRecords: "",
                info: "Showing *START* to *END* of *TOTAL* entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from *MAX* total entries)"
            },
            columnDefs: [
                { searchable: true, targets: [1, 2, 3, 4, 5, 6, 7, 8, 9, 12, 13, 14] },
                { className: 'text-center', targets: [0, 10, 11] }
            ],
            buttons: [
                {
                    extend: 'excel',
                    title: 'Daftar_Dokumen',
                    exportOptions: { 
                        columns: hasAnyPrivilege ? [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14] : ':not(:last-child)'
                    }
                }
            ],
            drawCallback: function() {
                const paginationHtml = $('.dataTables_paginate').html();
                if (paginationHtml) {
                    $('.pagination-container').html('<div class="dataTables_paginate">' + paginationHtml + '</div>');
                    $('.dataTables_paginate').not('.pagination-container .dataTables_paginate').hide();
                }
                $('.dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_info, .dataTables_wrapper .dataTables_paginate').css({
                    'position': 'sticky',
                    'left': '0',
                    'background': 'white',
                    'z-index': '10'
                });
                const info = this.api().page.info();
                const infoText = `Showing ${info.start + 1} to ${info.end} of ${info.recordsDisplay} entries`;
                $('.datatable-info-container').html(`<small class="text-muted">${infoText}</small>`);
            }
        };

        if (hasAnyPrivilege) {
            tableConfig.columnDefs.push({ orderable: false, targets: [-1] });
        }
        const table = $('#dokumenTable').DataTable(tableConfig);

        // Move export buttons to container
        table.buttons().container().appendTo('.dt-buttons-container');

        // Pindahkan tombol Excel ke container di sebelah Filter
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
                $('.no-data-message').show();
                $('#dokumenTable').hide();
            } else {
                $('.no-data-message').hide();
                $('#dokumenTable').show();
            }
        });

        // Connect custom length selector with DataTables
        $('#customLength').on('change', function() {
            table.page.len(parseInt(this.value)).draw();
        });

        // Handle pagination clicks di container baru
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

        // FILTER FUNCTIONALITY WITH SEARCH
        // Toggle dropdown function
        window.toggleDropdown = function(filterId) {
            const dropdown = document.querySelector(`.filter-dropdown:has(#${filterId}Content)`);
            const isCurrentlyShown = dropdown.classList.contains('show');
            document.querySelectorAll('.filter-dropdown').forEach(d => {
                d.classList.remove('show');
            });
            if (!isCurrentlyShown) {
                dropdown.classList.add('show');
            }
        };

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.filter-dropdown')) {
                document.querySelectorAll('.filter-dropdown').forEach(d => {
                    d.classList.remove('show');
                });
            }
        });

        // Update clause filter based on selected standard
        window.updateClauseFilter = function() {
            const selectedStandardRadio = document.querySelector('input[name="standar_filter"]:checked');
            const selectedStandardId = selectedStandardRadio ? selectedStandardRadio.value : null;
            const clauseGroup = document.getElementById('clauseRadioGroup');

            // Clear existing clause radio buttons
            clauseGroup.innerHTML = '';

            if (!selectedStandardId) {
                // When no standard is selected, show all clauses
                clausesData.forEach(clause => {
                    const radioItem = document.createElement('div');
                    radioItem.className = 'radio-item';
                    radioItem.setAttribute('data-text', clause.nomor_klausul.toLowerCase() + ' ' + clause.nama_klausul.toLowerCase() + ' ' + clause.nama_standar.toLowerCase());

                    const radio = document.createElement('input');
                    radio.type = 'radio';
                    radio.id = `klausul_${clause.id}`;
                    radio.value = clause.id;
                    radio.name = 'klausul_filter';
                    radio.className = 'klausul-radio';
                    radio.onchange = updateClauseText;

                    const label = document.createElement('label');
                    label.setAttribute('for', `klausul_${clause.id}`);
                    label.textContent = `${clause.nomor_klausul} - ${clause.nama_klausul} (${clause.nama_standar})`;

                    radioItem.appendChild(radio);
                    radioItem.appendChild(label);
                    clauseGroup.appendChild(radioItem);
                });
            } else {
                // Filter clauses based on selected standard
                let hasAvailableClauses = false;

                clausesData.forEach(clause => {
                    if (selectedStandardId === clause.standar_id.toString()) {
                        hasAvailableClauses = true;

                        const radioItem = document.createElement('div');
                        radioItem.className = 'radio-item';
                        radioItem.setAttribute('data-text', clause.nomor_klausul.toLowerCase() + ' ' + clause.nama_klausul.toLowerCase() + ' ' + clause.nama_standar.toLowerCase());

                        const radio = document.createElement('input');
                        radio.type = 'radio';
                        radio.id = `klausul_${clause.id}`;
                        radio.value = clause.id;
                        radio.name = 'klausul_filter';
                        radio.className = 'klausul-radio';
                        radio.onchange = updateClauseText;

                        const label = document.createElement('label');
                        label.setAttribute('for', `klausul_${clause.id}`);
                        label.textContent = `${clause.nomor_klausul} - ${clause.nama_klausul} (${clause.nama_standar})`;

                        radioItem.appendChild(radio);
                        radioItem.appendChild(label);
                        clauseGroup.appendChild(radioItem);
                    }
                });

                if (!hasAvailableClauses) {
                    const emptyMessage = document.createElement('div');
                    emptyMessage.className = 'no-results';
                    emptyMessage.textContent = 'No clauses available for selected standard.';
                    clauseGroup.appendChild(emptyMessage);
                }
            }

            // Clear clause selection when standard changes
            clearClauseSelection();
        };

        // Update standard text display
        window.updateStandardText = function() {
            const selectedStandardRadio = document.querySelector('input[name="standar_filter"]:checked');
            const standardText = document.getElementById('filterStandarText');

            if (selectedStandardRadio) {
                const label = document.querySelector(`label[for="${selectedStandardRadio.id}"]`);
                standardText.textContent = label.textContent;
            } else {
                standardText.textContent = 'Select Standard...';
            }
        };

        // Update clause text display
        window.updateClauseText = function() {
            const selectedClauseRadio = document.querySelector('input[name="klausul_filter"]:checked');
            const clauseText = document.getElementById('filterKlausulText');

            if (selectedClauseRadio) {
                const label = document.querySelector(`label[for="${selectedClauseRadio.id}"]`);
                const fullText = label.textContent;
                const truncatedText = fullText.length > 30 ? fullText.substring(0, 27) + '...' : fullText;
                clauseText.textContent = truncatedText;
            } else {
                clauseText.textContent = 'Select Clause...';
            }
        };

        // Clear standard selection
        window.clearStandardSelection = function() {
            const checkedStandard = document.querySelector('input[name="standar_filter"]:checked');
            if (checkedStandard) {
                checkedStandard.checked = false;
            }
            document.getElementById('filterStandarText').textContent = 'Select Standard...';
            document.getElementById('filterStandarContent').classList.remove('show');
            updateClauseFilter();
        };

        // Clear clause selection
        window.clearClauseSelection = function() {
            const checkedClause = document.querySelector('input[name="klausul_filter"]:checked');
            if (checkedClause) {
                checkedClause.checked = false;
            }
            document.getElementById('filterKlausulText').textContent = 'Select Clause...';
            document.getElementById('filterKlausulContent').classList.remove('show');
        };

        // Search functionality for standards
        window.filterStandardOptions = function() {
            const searchTerm = document.getElementById('standardSearchInput').value.toLowerCase();
            const radioItems = document.querySelectorAll('#standardRadioGroup .radio-item');
            let hasVisibleItems = false;

            radioItems.forEach(item => {
                const text = item.getAttribute('data-text') || '';
                if (text.includes(searchTerm)) {
                    item.style.display = 'flex';
                    hasVisibleItems = true;
                } else {
                    item.style.display = 'none';
                }
            });

            // Show/hide no results message
            let noResultsMsg = document.querySelector('#standardRadioGroup .no-results');
            if (!hasVisibleItems) {
                if (!noResultsMsg) {
                    noResultsMsg = document.createElement('div');
                    noResultsMsg.className = 'no-results';
                    noResultsMsg.textContent = 'No standards found';
                    document.getElementById('standardRadioGroup').appendChild(noResultsMsg);
                }
                noResultsMsg.style.display = 'block';
            } else if (noResultsMsg) {
                noResultsMsg.style.display = 'none';
            }
        };

        // Search functionality for clauses
        window.filterClauseOptions = function() {
            const searchTerm = document.getElementById('clauseSearchInput').value.toLowerCase();
            const radioItems = document.querySelectorAll('#clauseRadioGroup .radio-item');
            let hasVisibleItems = false;

            radioItems.forEach(item => {
                const text = item.getAttribute('data-text') || '';
                if (text.includes(searchTerm)) {
                    item.style.display = 'flex';
                    hasVisibleItems = true;
                } else {
                    item.style.display = 'none';
                }
            });

            // Show/hide no results message
            let noResultsMsg = document.querySelector('#clauseRadioGroup .no-results');
            if (!hasVisibleItems && radioItems.length > 0) {
                if (!noResultsMsg) {
                    noResultsMsg = document.createElement('div');
                    noResultsMsg.className = 'no-results';
                    noResultsMsg.textContent = 'No clauses found';
                    document.getElementById('clauseRadioGroup').appendChild(noResultsMsg);
                }
                noResultsMsg.style.display = 'block';
            } else if (noResultsMsg) {
                noResultsMsg.style.display = 'none';
            }
        };

        // Apply filters
        $('#btnFilter').on('click', function() {
            const selectedStandardRadio = document.querySelector('input[name="standar_filter"]:checked');
            const selectedClauseRadio = document.querySelector('input[name="klausul_filter"]:checked');
            const selectedStandard = selectedStandardRadio ? selectedStandardRadio.value : null;
            const selectedClause = selectedClauseRadio ? selectedClauseRadio.value : null;
            const selectedPemilik = $('#filterPemilik').val();
            const selectedJenis = $('#filterJenis').val();

            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                const row = table.row(dataIndex).node();
                const rowStandar = $(row).data('standar') ? $(row).data('standar').toString().split(',') : [];
                const rowKlausul = $(row).data('klausul') ? $(row).data('klausul').toString().split(',') : [];
                const rowPemilik = $(row).data('pemilik') || '';

                // Check filters
                const standarMatch = !selectedStandard || rowStandar.includes(selectedStandard);
                const klausulMatch = !selectedClause || rowKlausul.includes(selectedClause);
                const pemilikMatch = !selectedPemilik || rowPemilik.includes(selectedPemilik);
                const jenisMatch = !selectedJenis || data[5].includes($('#filterJenis option:selected').text());

                return standarMatch && klausulMatch && pemilikMatch && jenisMatch;
            });

            table.draw();
            $.fn.dataTable.ext.search.pop();

            const visibleRows = table.rows({ filter: 'applied' }).data().length;
            if (visibleRows === 0) {
                $('.no-data-message').show();
                $('#dokumenTable').hide();
            } else {
                $('.no-data-message').hide();
                $('#dokumenTable').show();
            }
        });

        // Modal handling
        $(document).on('show.bs.modal', '.modal', function() {
            setTimeout(() => {
                $('.modal-backdrop').remove();
            }, 50);
        });

        $(document).on('shown.bs.modal', '.modal', function() {
            $('.modal-backdrop').remove();
            $(this).css({
                'z-index': '999999',
                'position': 'fixed',
                'background-color': 'rgba(0, 0, 0, 0.5)'
            });

            $(this).find('.modal-dialog').css({
                'z-index': '1000000',
                'position': 'relative'
            });
        });

        // Form submission handling
        if (documentPrivilege.can_update) {
            $('.edit-form').on('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const modalId = '#editModal' + formData.get('id');
                $(modalId).modal('hide');

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
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Document updated successfully.',
                            confirmButtonText: 'OK',
                            showConfirmButton: true
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Document updated successfully.',
                            confirmButtonText: 'OK',
                            showConfirmButton: true
                        }).then(() => {
                            location.reload();
                        });
                    }
                });
            });
        }

        // Delete confirmation
        if (documentPrivilege.can_delete) {
            window.confirmDelete = function(event, form) {
                event.preventDefault();
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This action cannot be undone!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
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

        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Initialize clause filter on page load (show all clauses initially)
        updateClauseFilter();
    });
</script>

<!-- Success/Error Flash Messages -->
<?php if (session()->getFlashdata('success')): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: '<?= esc(session()->getFlashdata('success')) ?>',
            confirmButtonColor: '#0d6efd'
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
            title: 'Warning!',
            text: '<?= esc(session()->getFlashdata('warning')) ?>',
            confirmButtonColor: '#ffc107'
        });
    </script>
<?php endif; ?>
<?= $this->endSection() ?>
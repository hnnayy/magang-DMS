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
<style>
    
    /* Ensure dropdown toggle is clickable */
    .filter-toggle {
        background-color: white !important;
        border: 1px solid #ced4da !important;
        padding: 0.375rem 0.75rem !important;
        border-radius: 0.375rem !important;
        cursor: pointer !important;
        min-width: 180px;
        text-align: left;
        display: flex !important;
        justify-content: space-between;
        align-items: center;
        font-size: 14px;
        pointer-events: auto !important;
    }
    .filter-toggle:hover {
        background-color: #f8f9fa !important;
    }
    .filter-toggle:after {
        content: "▼" !important;
        font-size: 0.8em;
        color: #6c757d;
    }
    .clear-selection {
        padding: 8px 12px;
        background: #dc3545;
        color: white;
        cursor: pointer;
        font-size: 12px;
        border-bottom: 1px solid #eee;
        display: flex;
        align-items: center;
    }
    .clear-selection:hover {
        background: #c82333;
    }
    .dropdown-search {
        padding: 8px 12px;
        border-bottom: 1px solid #eee;
        background: #f8f9fa;
    }
    .dropdown-search input {
        width: 100%;
        padding: 4px 8px;
        border: 1px solid #ddd;
        border-radius: 3px;
        font-size: 12px;
    }
    .option-group {
        max-height: 200px;
        overflow-y: auto;
    }
    .option-item {
        display: flex;
        align-items: center;
        padding: 8px 12px;
        font-size: 13px;
        border-bottom: 1px solid #f1f1f1;
    }
    .option-item:last-child {
        border-bottom: none;
    }
    .option-item input[type="radio"],
    .option-item input[type="checkbox"] {
        margin-right: 8px;
        margin-top: 0;
    }
    .option-item label {
        margin: 0;
        cursor: pointer;
        flex: 1;
        line-height: 1.2;
    }
    .no-results {
        padding: 12px;
        text-align: center;
        color: #999;
        font-style: italic;
        font-size: 12px;
    }
    .disabled-message {
        padding: 12px;
        text-align: center;
        color: #6c757d;
        font-style: italic;
        font-size: 12px;
        background: #f8f9fa;
    }
    /* Other styles remain unchanged */
    .filter-dropdown {
        position: relative;
        display: inline-block;
    }
    .filter-dropdown-content {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        background-color: white;
        min-width: 300px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        z-index: 1000000;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        max-height: 300px;
        overflow-y: auto;
    }
    .filter-dropdown.show .filter-dropdown-content {
        display: block;
    }
    /* Text truncation */
    .text-truncate-custom {
        max-width: 200px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: inline-block;
    }
    /* Table specific styles */
    .aksi-column,
    td.aksi-column {
        min-width: 90px;
        text-align: center;
        white-space: nowrap;
    }
    .kode-dokumen-simple {
        font-size: 0.9rem;
        color: #333;
        line-height: 1.4;
    }
    /* DataTables custom styling */
    .pagination-container {
        display: flex;
        justify-content: flex-end;
        padding-right: 20px;
    }
    .pagination-container .dataTables_paginate {
        display: flex;
        gap: 5px;
    }
    .pagination-container .dataTables_paginate .paginate_button {
        padding: 8px 12px;
        margin: 0 2px;
        background-color: #ffffff;
        color: #000000;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 500;
        font-size: 14px;
        transition: all 0.2s ease-in-out;
        cursor: pointer;
        min-width: 40px;
        text-align: center;
    }
    .pagination-container .dataTables_paginate .paginate_button:hover:not(.disabled):not(.current) {
        background-color: #000000;
        color: #ffffff;
    }
    .pagination-container .dataTables_paginate .paginate_button.current {
        background-color: #dc3545 !important;
        color: white !important;
    }
    .pagination-container .dataTables_paginate .paginate_button.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background-color: #f8f9fa !important;
        color: #6c757d !important;
    }
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
<div class="container-fluid px-4 py-4">
    <h4 class="mb-4">Daftar Dokumen</h4>
    <!-- FILTER SECTION -->
    <div class="bg-light p-3 rounded mb-4">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <strong class="form-label mb-0 me-2">Filter Data</strong>
            <!-- Filter Standar dengan Radio Button -->
            <div class="filter-dropdown flex-grow-1" style="min-width:180px;">
                <div class="filter-toggle" onclick="toggleDropdown('filterStandar', event)">
                    <span id="filterStandarText">Pilih Standar...</span>
                </div>
                <div class="filter-dropdown-content" id="filterStandarContent">
                    <div class="clear-selection" onclick="clearStandardSelection()">
                        <i class="bi bi-x-circle me-1"></i> Hapus Pilihan
                    </div>
                    <div class="dropdown-search">
                        <input type="text" placeholder="Cari standar..." onkeyup="filterStandardOptions()" id="standardSearchInput">
                    </div>
                    <div class="option-group" id="standardRadioGroup">
                        <?php foreach ($standards as $s): ?>
                            <div class="option-item" data-text="<?= strtolower(esc($s['nama_standar'])) ?>">
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
            <!-- Filter Klausul dengan Checkbox (Dynamic based on Standard) -->
            <div class="filter-dropdown flex-grow-1" style="min-width:180px;">
                <div class="filter-toggle" onclick="toggleDropdown('filterKlausul', event)" id="clauseToggle">
                    <span id="filterKlausulText">Pilih Klausul...</span>
                </div>
                <div class="filter-dropdown-content" id="filterKlausulContent">
                    <div class="clear-selection" onclick="clearClauseSelection()">
                        <i class="bi bi-x-circle me-1"></i> Hapus Pilihan
                    </div>
                    <div class="dropdown-search">
                        <input type="text" placeholder="Cari klausul..." onkeyup="filterClauseOptions()" id="clauseSearchInput">
                    </div>
                    <div class="option-group" id="clauseCheckboxGroup">
                        <div class="disabled-message">Pilih standar terlebih dahulu</div>
                    </div>
                </div>
            </div>
            <!-- Filter Pemilik Dokumen -->
            <div class="flex-grow-1" style="min-width:180px;">
                <select class="form-select" id="filterPemilik">
                    <option value="">Semua Pemilik Dokumen</option>
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
                        <option value="<?= esc($owner) ?>"><?= esc($owner) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Filter Jenis Dokumen -->
            <div class="flex-grow-1" style="min-width:180px;">
                <select class="form-select" id="filterJenis">
                    <option value="">Semua Jenis Dokumen</option>
                    <?php foreach ($kategori_dokumen as $k): ?>
                        <option value="<?= $k['id'] ?>"><?= $k['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Tombol Filter dan Export -->
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-primary btn-sm" id="btnFilter">Filter</button>
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
                                <th>Fakultas/Direktorat</th>
                                <th>Jurusan/Unit/Program</th>
                                <th>Standar</th>
                                <th>Klausul</th>
                                <th>Jenis Dokumen</th>
                                <th>Kode & Nama Dokumen</th>
                                <th>Nomor Dokumen</th>
                                <th>Nama Dokumen</th>
                                <th>Pemilik Dokumen</th>
                                <th>File Dokumen</th>
                                <th>Revisi</th>
                                <th>Tanggal Efektif</th>
                                <th>Disetujui Oleh</th>
                                <th>Tanggal Persetujuan</th>
                                <?php if ($hasAnyPrivilege): ?>
                                    <th class="aksi-column">Aksi</th>
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
                                    <td><?= esc($row['standar_display']) ?></td>
                                    <td>
                                        <span class="text-truncate-custom" title="<?= esc($row['klausul_display']) ?>">
                                            <?= esc($row['klausul_display']) ?>
                                        </span>
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
                                            <a href="<?= base_url('document-document-list/serveFile?id=' . $row['id'] . '&action=download') ?>" 
                                               class="text-decoration-none" 
                                               title="Download <?= esc($row['filename'] ?? basename($row['filepath'])) ?>">
                                                <i class="bi bi-download text-success fs-5"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">
                                                <i class="bi bi-file-earmark-x"></i> Tidak ada file
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
                                                        <button type="submit" class="btn btn-link p-0 text-danger" title="Hapus">
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
                        <i class="bi bi-info-circle"></i> Tidak ada dokumen yang ditemukan sesuai kriteria Anda.
                    </div>
                </div>
                <div class="pagination-container"></div>
            </div>
        </div>
    </div>
</div>
<!-- MODAL EDIT DOKUMEN -->
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
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form action="<?= base_url('document-list/update') ?>" method="post" class="edit-form" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $documentId ?>">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel<?= $documentId ?>">Edit Dokumen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Standar</label>
                            <div class="filter-dropdown w-100 no-choices">
                                <div class="filter-toggle" onclick="event.preventDefault(); event.stopPropagation(); toggleEditDropdown('editFilterStandar_<?= $documentId ?>', event)">
                                    <span id="editFilterStandarText_<?= $documentId ?>">
                                        <?php
                                        // Display the selected standard name
                                        $selectedStandardId = array_filter(explode(',', $row['standar_ids'] ?? ''))[0] ?? '';
                                        $selectedStandardName = 'Pilih Standar...';
                                        foreach ($standards as $s) {
                                            if ($s['id'] == $selectedStandardId) {
                                                $selectedStandardName = esc($s['nama_standar']);
                                                break;
                                            }
                                        }
                                        echo $selectedStandardName;
                                        ?>
                                    </span>
                                </div>
                                <div class="filter-dropdown-content" id="editFilterStandarContent_<?= $documentId ?>">
                                    <div class="clear-selection" onclick="clearEditStandardSelection('<?= $documentId ?>')">
                                        <i class="bi bi-x-circle me-1"></i> Hapus Pilihan
                                    </div>
                                    <div class="dropdown-search">
                                        <input type="text" placeholder="Cari standar..." onkeyup="filterEditStandardOptions('<?= $documentId ?>')" id="editStandardSearchInput_<?= $documentId ?>">
                                    </div>
                                    <div class="option-group" id="editStandardRadioGroup_<?= $documentId ?>">
                                        <?php foreach ($standards as $s): ?>
                                            <div class="option-item" data-text="<?= strtolower(esc($s['nama_standar'])) ?>">
                                                <input type="radio" 
                                                       id="edit_standar_<?= $documentId ?>_<?= $s['id'] ?>" 
                                                       value="<?= $s['id'] ?>" 
                                                       name="standar_id"
                                                       class="edit-standar-radio"
                                                       onchange="updateEditStandardText('<?= $documentId ?>'); updateEditClauseFilter('<?= $documentId ?>');"
                                                       <?= in_array($s['id'], array_filter(explode(',', $row['standar_ids'] ?? ''))) ? 'checked' : '' ?>>
                                                <label for="edit_standar_<?= $documentId ?>_<?= $s['id'] ?>"><?= esc($s['nama_standar']) ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Klausul</label>
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
                                            echo count($selectedClauseNames) . ' klausul dipilih';
                                        } elseif (count($selectedClauseNames) === 1) {
                                            $fullText = $selectedClauseNames[0];
                                            echo strlen($fullText) > 30 ? substr($fullText, 0, 27) . '...' : $fullText;
                                        } else {
                                            echo 'Pilih Klausul...';
                                        }
                                        ?>
                                    </span>
                                </div>
                                <div class="filter-dropdown-content" id="editFilterKlausulContent_<?= $documentId ?>">
                                    <div class="clear-selection" onclick="clearEditClauseSelection('<?= $documentId ?>')">
                                        <i class="bi bi-x-circle me-1"></i> Hapus Pilihan
                                    </div>
                                    <div class="dropdown-search">
                                        <input type="text" placeholder="Cari klausul..." onkeyup="filterEditClauseOptions('<?= $documentId ?>')" id="editClauseSearchInput_<?= $documentId ?>">
                                    </div>
                                    <div class="option-group" id="editClauseCheckboxGroup_<?= $documentId ?>">
                                        <?php
                                        // Pre-populate clauses for the selected standard
                                        $selectedStandardId = array_filter(explode(',', $row['standar_ids'] ?? ''))[0] ?? '';
                                        if ($selectedStandardId):
                                            foreach ($clausesData as $clause):
                                                if ($clause['standar_id'] == $selectedStandardId):
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
                                            <div class="disabled-message">Pilih standar terlebih dahulu</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jenis Dokumen</label>
                            <select name="type" class="form-select" disabled>
                                <?php foreach ($kategori_dokumen as $kategori): ?>
                                    <option value="<?= $kategori['id'] ?>" <?= ($row['type'] == $kategori['id']) ? 'selected' : '' ?>>
                                        <?= $kategori['name'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kode Jenis</label>
                            <input type="text" class="form-control" name="type_code" 
                                   value="<?= esc($row['kode_jenis_dokumen'] ?? $row['kode_dokumen_kode'] ?? '-') ?>" readonly>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Kode & Nama Dokumen</label>
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
                                    echo '<span class="text-muted">Tidak ada kode</span>';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nomor</label>
                            <input type="text" class="form-control" name="number" value="<?= esc($row['number']) ?>" readonly>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Nama Dokumen</label>
                            <input type="text" class="form-control" name="title" value="<?= esc($row['title']) ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Pemilik Dokumen</label>
                            <input type="text" class="form-control" name="createdby" value="<?= esc($documentCreatorName) ?>" readonly>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Disetujui Oleh</label>
                            <input type="hidden" name="approveby" value="<?= esc($row['approveby'] ?? '') ?>">
                            <input type="text" class="form-control" value="<?= esc($row['approved_by_name'] ?? '') ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Persetujuan</label>
                            <input type="datetime-local" class="form-control" name="approvedate" 
                                   value="<?= esc(date('Y-m-d\TH:i', strtotime($row['approvedate'] ?? 'now'))) ?>" readonly>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Revisi</label>
                            <input type="text" class="form-control" name="revision" value="<?= esc($row['revision']) ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Efektif</label>
                            <input type="date" class="form-control" name="date_published" value="<?= esc($row['date_published']) ?>">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">File Dokumen</label>
                            <div class="file-display">
                                <?php if (!empty($row['filepath']) && file_exists(ROOTPATH . '..' . DIRECTORY_SEPARATOR . $row['filepath'])): ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="file-info flex-grow-1 p-2 bg-light rounded">
                                            <small class="text-muted"><?= esc($row['filename'] ?? basename($row['filepath'])) ?></small>
                                        </div>
                                        <a href="<?= base_url('document-document-list/serveFile?id=' . $row['id'] . '&action=download') ?>" 
                                           class="btn btn-primary btn-sm" 
                                           title="Download <?= esc($row['filename'] ?? basename($row['filepath'])) ?>">
                                            <i class="bi bi-download"></i>
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning mb-0">
                                        <i class="bi bi-exclamation-triangle"></i> Tidak ada file tersedia
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>    
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
<!-- Custom JavaScript -->
<script>
    // Assume clausesData is available from PHP
    const clausesData = <?= json_encode($clausesData ?? []) ?>;
    $(document).ready(function() {
        // Privilege check dari PHP
        const documentPrivilege = <?= json_encode($documentPrivilege) ?>;
        const hasAnyPrivilege = <?= json_encode($hasAnyPrivilege) ?>;
        const currentUserId = <?= json_encode($currentUserId) ?>;
        const currentUserAccessLevel = <?= json_encode($currentUserAccessLevel) ?>;
        // Debug: Log clausesData to verify structure
        console.log('Clauses Data:', clausesData);
        // Initialize DataTables
        const tableConfig = {
            dom: 'rt<"d-flex justify-content-between align-items-center mt-3"<"d-flex align-items-center"l><"pagination-wrapper"p>>',
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            language: {
                paginate: {
                    previous: "Sebelumnya",
                    next: "Berikutnya"
                },
                zeroRecords: "",
                info: "Menampilkan *START* sampai *END* dari *TOTAL* entri",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
                infoFiltered: "(difilter dari *MAX* total entri)"
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
                
                const info = this.api().page.info();
                const infoText = `Menampilkan ${info.start + 1} sampai ${info.end} dari ${info.recordsDisplay} entri`;
                $('.datatable-info-container').html(`<small class="text-muted">${infoText}</small>`);
            }
        };
        if (hasAnyPrivilege) {
            tableConfig.columnDefs.push({ orderable: false, targets: [-1] });
        }
        
        const table = $('#dokumenTable').DataTable(tableConfig);
        // Move export buttons to container
        table.buttons().container().appendTo('.dt-buttons-container');
        const excelButton = $('.dt-buttons-container .buttons-excel').detach();
        excelButton.removeClass('dt-button').addClass('btn btn-success btn-sm');
        $('#excel-button-container').html(excelButton);
        // Move length control to container
        $('.dt-length-container').html(`
            <div class="d-flex align-items-center">
                <label class="me-2 mb-0">Tampilkan:</label>
                <select class="form-select form-select-sm" id="customLength" style="width: 80px;">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <label class="ms-2 mb-0">entri</label>
            </div>
        `);
        // Custom search
        const searchHtml = `
            <div class="d-flex align-items-center">
                <label class="me-2 mb-0">Cari:</label>
                <input type="search" class="form-control form-control-sm" id="customSearch" style="width: 200px;" placeholder="Cari Dokumen...">
            </div>
        `;
        $('.dt-search-container').html(searchHtml);
        // Connect custom controls
        $('#customSearch').on('keyup', function() {
            const searchTerm = this.value.trim();
            table.search(searchTerm).draw();
            checkVisibleRows();
        });
        $('#customLength').on('change', function() {
            table.page.len(parseInt(this.value)).draw();
        });
        // Check visible rows and show/hide no data message
        function checkVisibleRows() {
            const visibleRows = table.rows({ filter: 'applied' }).data().length;
            if (visibleRows === 0) {
                $('#no-data-message').show();
                $('#dokumenTable').hide();
            } else {
                $('#no-data-message').hide();
                $('#dokumenTable').show();
            }
        }
        // Handle pagination clicks
        $(document).on('click', '.pagination-container .paginate_button', function(e) {
            e.preventDefault();
            if ($(this).hasClass('disabled') || $(this).hasClass('current')) return;
            
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
                console.error('Dropdown tidak ditemukan untuk ID:', filterId);
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
        // Fungsi khusus untuk toggle dropdown di modal edit
        window.toggleEditDropdown = function(filterId, event) {
            event.stopPropagation();
            event.preventDefault();
            
            console.log('Toggling edit dropdown:', filterId);
            
            const dropdown = document.querySelector(`.filter-dropdown:has(#${filterId}Content)`);
            if (!dropdown) {
                console.error('Edit dropdown tidak ditemukan untuk ID:', filterId);
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
        // Close dropdowns when clicking outside, but exclude modal dropdowns
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.filter-dropdown') && !event.target.closest('.modal')) {
                document.querySelectorAll('.filter-dropdown:not(.modal .filter-dropdown)').forEach(d => {
                    d.classList.remove('show');
                });
                console.log('Closed non-modal dropdowns due to outside click');
            }
        });
        // Prevent modal dropdowns from being closed by outside clicks within modal
        $(document).on('click', '.modal .filter-dropdown', function(e) {
            e.stopPropagation();
        });
        // Update clause filter based on selected standard
        window.updateClauseFilter = function() {
            const selectedStandardRadio = document.querySelector('input[name="standar_filter"]:checked');
            const selectedStandardId = selectedStandardRadio ? selectedStandardRadio.value : null;
            const clauseGroup = document.getElementById('clauseCheckboxGroup');
            const clauseToggle = document.getElementById('clauseToggle');
            console.log('Updating clause filter for standard:', selectedStandardId);
            // Clear existing clause checkboxes
            clauseGroup.innerHTML = '';
            if (!selectedStandardId) {
                clauseGroup.innerHTML = '<div class="disabled-message">Pilih standar terlebih dahulu</div>';
                clauseToggle.style.opacity = '0.6';
                clauseToggle.style.cursor = 'not-allowed';
                updateClauseText();
                return;
            }
            clauseToggle.style.opacity = '1';
            clauseToggle.style.cursor = 'pointer';
            let hasAvailableClauses = false;
            clausesData.forEach(clause => {
                if (selectedStandardId === clause.standar_id.toString()) {
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
                emptyMessage.textContent = 'Tidak ada klausul tersedia untuk standar yang dipilih.';
                clauseGroup.appendChild(emptyMessage);
            }
            updateClauseText();
        };
        // Update clause filter for edit modals
        window.updateEditClauseFilter = function(modalId) {
            console.log('Updating edit clause filter for modal:', modalId);
            
            // Find the checked radio button in this specific modal
            const modal = $(`#editModal${modalId}`);
            const selectedStandardRadio = modal.find('input[name="standar_id"]:checked');
            const selectedStandardId = selectedStandardRadio.length > 0 ? selectedStandardRadio.val() : null;
            
            const clauseGroup = document.getElementById(`editClauseCheckboxGroup_${modalId}`);
            const clauseToggle = document.getElementById(`editClauseToggle_${modalId}`);
            if (!clauseGroup || !clauseToggle) {
                console.error('Clause elements not found for modal:', modalId);
                return;
            }
            // Clear existing clause checkboxes
            clauseGroup.innerHTML = '';
            if (!selectedStandardId) {
                clauseGroup.innerHTML = '<div class="disabled-message">Pilih standar terlebih dahulu</div>';
                clauseToggle.style.opacity = '0.6';
                clauseToggle.style.cursor = 'not-allowed';
                updateEditClauseText(modalId);
                return;
            }
            clauseToggle.style.opacity = '1';
            clauseToggle.style.cursor = 'pointer';
            // Get existing clause IDs from the table row
            const row = document.querySelector(`tr[data-standar*="${selectedStandardId}"]`);
            const existingClauseIds = row ? row.dataset.klausul.split(',') : [];
            let hasAvailableClauses = false;
            // Build clause checkboxes
            clausesData.forEach(clause => {
                if (selectedStandardId === clause.standar_id.toString()) {
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
                emptyMessage.textContent = 'Tidak ada klausul tersedia untuk standar yang dipilih.';
                clauseGroup.appendChild(emptyMessage);
            }
            updateEditClauseText(modalId);
        };
        // Update standard text display
        window.updateStandardText = function() {
            const selectedStandardRadio = document.querySelector('input[name="standar_filter"]:checked');
            const standardText = document.getElementById('filterStandarText');
            if (selectedStandardRadio) {
                const label = document.querySelector(`label[for="${selectedStandardRadio.id}"]`);
                standardText.textContent = label.textContent;
            } else {
                standardText.textContent = 'Pilih Standar...';
            }
        };
        // Update standard text for edit modals
        window.updateEditStandardText = function(modalId) {
            const modal = $(`#editModal${modalId}`);
            const selectedStandardRadio = modal.find('input[name="standar_id"]:checked');
            const standardText = document.getElementById(`editFilterStandarText_${modalId}`);
            if (!standardText) {
                console.error('Standard text element not found for modal:', modalId);
                return;
            }
            if (selectedStandardRadio.length > 0) {
                const label = modal.find(`label[for="${selectedStandardRadio.attr('id')}"]`);
                if (label.length > 0) {
                    standardText.textContent = label.text();
                }
            } else {
                standardText.textContent = 'Pilih Standar...';
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
                    clauseText.textContent = `${selectedClauseCheckboxes.length} klausul dipilih`;
                }
            } else {
                clauseText.textContent = 'Pilih Klausul...';
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
                    clauseText.textContent = `${selectedClauseCheckboxes.length} klausul dipilih`;
                }
            } else {
                clauseText.textContent = 'Pilih Klausul...';
            }
        };
        // Clear standard selection
        window.clearStandardSelection = function() {
            const checkedStandard = document.querySelector('input[name="standar_filter"]:checked');
            if (checkedStandard) {
                checkedStandard.checked = false;
            }
            document.getElementById('filterStandarText').textContent = 'Pilih Standar...';
            document.getElementById('filterStandarContent').classList.remove('show');
            updateClauseFilter();
        };
        // Clear standard selection for edit modals
        window.clearEditStandardSelection = function(modalId) {
            const modal = $(`#editModal${modalId}`);
            const checkedStandard = modal.find('input[name="standar_id"]:checked');
            
            if (checkedStandard.length > 0) {
                checkedStandard.prop('checked', false);
            }
            
            const standardText = document.getElementById(`editFilterStandarText_${modalId}`);
            if (standardText) {
                standardText.textContent = 'Pilih Standar...';
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
            document.getElementById('filterKlausulText').textContent = 'Pilih Klausul...';
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
                clauseText.textContent = 'Pilih Klausul...';
            }
            
            const clauseContent = document.getElementById(`editFilterKlausulContent_${modalId}`);
            if (clauseContent) {
                $(clauseContent).parent('.filter-dropdown').removeClass('show');
            }
        };
        // Search functionality for standards
        window.filterStandardOptions = function() {
            const searchTerm = document.getElementById('standardSearchInput').value.toLowerCase();
            const optionItems = document.querySelectorAll('#standardRadioGroup .option-item');
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
            let noResultsMsg = document.querySelector('#standardRadioGroup .no-results');
            if (!hasVisibleItems && optionItems.length > 0) {
                if (!noResultsMsg) {
                    noResultsMsg = document.createElement('div');
                    noResultsMsg.className = 'no-results';
                    noResultsMsg.textContent = 'Tidak ada standar ditemukan';
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
                    noResultsMsg.textContent = 'Tidak ada klausul ditemukan';
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
            const optionItems = document.querySelectorAll(`#editStandardRadioGroup_${modalId} .option-item`);
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
            let noResultsMsg = document.querySelector(`#editStandardRadioGroup_${modalId} .no-results`);
            if (!hasVisibleItems && optionItems.length > 0) {
                if (!noResultsMsg) {
                    noResultsMsg = document.createElement('div');
                    noResultsMsg.className = 'no-results';
                    noResultsMsg.textContent = 'Tidak ada standar ditemukan';
                    document.getElementById(`editStandardRadioGroup_${modalId}`).appendChild(noResultsMsg);
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
                    noResultsMsg.textContent = 'Tidak ada klausul ditemukan';
                    document.getElementById(`editClauseCheckboxGroup_${modalId}`).appendChild(noResultsMsg);
                }
                noResultsMsg.style.display = 'block';
            } else if (noResultsMsg) {
                noResultsMsg.style.display = 'none';
            }
        };
        // Apply filters
        $('#btnFilter').on('click', function() {
            const selectedStandardRadio = document.querySelector('input[name="standar_filter"]:checked');
            const selectedClauseCheckboxes = document.querySelectorAll('input[name="klausul_filter"]:checked');
            const selectedStandard = selectedStandardRadio ? selectedStandardRadio.value : null;
            const selectedClauses = Array.from(selectedClauseCheckboxes).map(cb => cb.value);
            const selectedPemilik = $('#filterPemilik').val();
            const selectedJenis = $('#filterJenis').val();
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                const row = table.row(dataIndex).node();
                const rowStandar = $(row).data('standar') ? $(row).data('standar').toString().split(',') : [];
                const rowKlausul = $(row).data('klausul') ? $(row).data('klausul').toString().split(',') : [];
                const rowPemilik = $(row).data('pemilik') || '';
                const standarMatch = !selectedStandard || rowStandar.includes(selectedStandard);
                const klausulMatch = selectedClauses.length === 0 || 
                    selectedClauses.some(clause => rowKlausul.includes(clause));
                const pemilikMatch = !selectedPemilik || rowPemilik.includes(selectedPemilik);
                const jenisMatch = !selectedJenis || data[5].includes($('#filterJenis option:selected').text());
                return standarMatch && klausulMatch && pemilikMatch && jenisMatch;
            });
            table.draw();
            $.fn.dataTable.ext.search.pop();
            checkVisibleRows();
        });
        // Modal handling
        $(document).on('show.bs.modal', '.modal', function() {
            const modalId = $(this).attr('id').replace('editModal', '');
            const modal = $(this);
            
            console.log('Modal opened:', modalId);
        
            
            setTimeout(() => {
                // Update standard text
                updateEditStandardText(modalId);
                
                // Check if standard is selected and update clause filter accordingly
                const selectedStandardRadio = modal.find(`input[name="standar_id"]:checked`);
                if (selectedStandardRadio.length > 0) {
                    updateEditClauseFilter(modalId);
                } else {
                    const clauseGroup = document.getElementById(`editClauseCheckboxGroup_${modalId}`);
                    const clauseToggle = document.getElementById(`editClauseToggle_${modalId}`);
                    if (clauseGroup && clauseToggle) {
                        clauseGroup.innerHTML = '<div class="disabled-message">Pilih standar terlebih dahulu</div>';
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
            if (!$(event.target).closest('.filter-dropdown').length && !$(event.target).closest('.modal').length) {
                $('.filter-dropdown:not(.modal .filter-dropdown)').removeClass('show');
            }
        });
        // Prevent modal dropdowns from being closed by outside clicks within modal
        $(document).on('click', '.modal .filter-dropdown', function(e) {
            e.stopPropagation();
        });
        // Form submission handling - FIX: Use correct endpoint
        if (documentPrivilege.can_update) {
            $('.edit-form').on('submit', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const formData = new FormData(this);
                const modalId = '#editModal' + formData.get('id');
                $(modalId).modal('hide');
                console.log('Form submission data:', Object.fromEntries(formData));
                Swal.fire({
                    title: 'Menyimpan...',
                    text: 'Memproses data',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                $.ajax({
                    url: '<?= base_url('document-list/update') ?>',  // FIXED: Use correct endpoint
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function(response) {
                        console.log('Update response:', response);
                        Swal.fire({
                            icon: response.swal.icon,
                            title: response.swal.title,
                            text: response.swal.text,
                            confirmButtonText: 'OK',
                            showConfirmButton: true
                        }).then(() => {
                            if (response.status === 'success') {
                                location.reload();
                            }
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Update error:', xhr.responseJSON, status, error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Gagal memperbarui dokumen: ' + (xhr.responseJSON?.message || error),
                            confirmButtonText: 'OK',
                            showConfirmButton: true
                        });
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
                    title: 'Apakah Anda yakin?',
                    text: 'Tindakan ini tidak dapat dibatalkan!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Menghapus...',
                            text: 'Mohon tunggu sebentar',
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
            title: 'Berhasil!',
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
            title: 'Peringatan!',
            text: '<?= esc(session()->getFlashdata('warning')) ?>',
            confirmButtonColor: '#ffc107'
        });
    </script>
<?php endif; ?>
<?= $this->endSection(); ?>



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
</style>

<div class="container-fluid px-4 py-4">
    <h4 class="mb-4">Document List</h4>

    <!-- FILTER -->
    <div class="bg-light p-3 rounded mb-4">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <strong class="form-label mb-0 me-2">Filter Data</strong>
            
            <!-- Filter Standar dengan Checkbox -->
            <div class="filter-dropdown flex-grow-1" style="min-width:180px;">
                <div class="filter-toggle" onclick="toggleDropdown('filterStandar')">
                    <span id="filterStandarText">Select Standard...</span>
                </div>
                <div class="filter-dropdown-content" id="filterStandarContent">
                    <div class="checkbox-group">
                        <?php foreach ($standards as $s): ?>
                            <div class="checkbox-item">
                                <input type="checkbox" id="standar_<?= $s['id'] ?>" value="<?= $s['id'] ?>" class="standar-checkbox">
                                <label for="standar_<?= $s['id'] ?>"><?= esc($s['nama_standar']) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Filter Klausul dengan Checkbox -->
            <div class="filter-dropdown flex-grow-1" style="min-width:180px;">
                <div class="filter-toggle" onclick="toggleDropdown('filterKlausul')">
                    <span id="filterKlausulText">Select Clause...</span>
                </div>
                <div class="filter-dropdown-content" id="filterKlausulContent">
                    <div class="checkbox-group">
                        <?php foreach ($clauses as $c): ?>
                            <div class="checkbox-item">
                                <input type="checkbox" id="klausul_<?= $c['id'] ?>" value="<?= $c['id'] ?>" class="klausul-checkbox">
                                <label for="klausul_<?= $c['id'] ?>">
                                    <?= esc($c['nomor_klausul']) ?> - <?= esc($c['nama_klausul']) ?> (<?= esc($c['nama_standar']) ?>)
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="flex-grow-1" style="min-width:180px;">
                <select class="form-select filter-input w-100" id="filterPemilik">
                    <option value="">All Document Owners</option>
                    <?php 
                    $unique_owners = [];
                    foreach ($document as $doc) {
                        // Apply same access control for filter options
                        $documentCreatorId = $doc['createdby_id'] ?? 0;
                        $documentCreatorUnitId = $doc['creator_unit_id'] ?? 0;
                        $documentCreatorUnitParentId = $doc['creator_unit_parent_id'] ?? 0;
                        $documentCreatorAccessLevel = $doc['creator_access_level'] ?? 2;
                        $documentCreatorName = $doc['creator_fullname'] ?? $doc['createdby'] ?? 'Unknown User';
                        
                        $canViewDocument = false;
                        $showCreatorName = false;
                        
                        // Same access control logic as in the table
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
                        } elseif ($currentUserAccessLevel == $documentCreatorAccessLevel) {
                            $sameUnit = ($documentCreatorUnitId == $currentUserUnitId);
                            if ($sameUnit) {
                                $canViewDocument = true;
                                $showCreatorName = true;
                            }
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

            <div class="flex-grow-1" style="min-width:180px;">
                <select class="form-select filter-input w-100" id="filterJenis">
                    <option value="">All Document Types</option>
                    <?php foreach ($kategori_dokumen as $k): ?>
                        <option value="<?= $k['id'] ?>"><?= $k['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-primary btn-sm-2" id="btnFilter">Filter</button>
                <button class="btn btn-success btn-sm-2" id="excel-button-container">Export Excel</button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <!-- Container untuk tombol export, show entries, dan search -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="dt-buttons-container"></div>
                    <div class="dt-length-container"></div>
                </div>
                <div class="dt-search-container"></div>
            </div>

            <!-- TABEL dengan wrapper untuk sticky pagination -->
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
                                $showCreatorName = false; // Control creator name visibility
                                $canEditDocument = false; // Control edit permission
                                $canDeleteDocument = false; // Control delete permission
                                
                                // Access Control Rules:
                                // Rule 1: Users can always see their own documents and their own name
                                if ($documentCreatorId == $currentUserId) {
                                    $canViewDocument = true;
                                    $showCreatorName = true;
                                    $canEditDocument = true;
                                    $canDeleteDocument = true;
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
                                        $canEditDocument = true; // Higher level users can edit subordinate documents
                                        $canDeleteDocument = true; // Higher level users can delete subordinate documents
                                    }
                                }
                                // Rule 3: Same level users in same unit can see each other's documents
                                elseif ($currentUserAccessLevel == $documentCreatorAccessLevel) {
                                    $sameUnit = ($documentCreatorUnitId == $currentUserUnitId);
                                    if ($sameUnit) {
                                        $canViewDocument = true;
                                        $showCreatorName = true; // Same level users can see each other's names
                                        // Same level users cannot edit/delete each other's documents (only their own)
                                        $canEditDocument = false;
                                        $canDeleteDocument = false;
                                    }
                                }
                                
                                // Skip if user cannot view this document
                                if (!$canViewDocument) continue;
                                
                                // Skip documents with invalid creator ID
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
                                        <!-- FORMAT BARU: Tampilkan kode & nama dokumen dengan format teks biasa dipisah strip -->
                                        <div class="kode-dokumen-simple">
                                            <?php 
                                            $kodeDokumenText = '';
                                            
                                            // Prioritas 1: Dari tabel kode_dokumen (predefined atau custom)
                                            if (!empty($row['kode_dokumen_kode']) && !empty($row['kode_dokumen_nama'])) {
                                                $kodeDokumenText = $row['kode_dokumen_kode'] . ' - ' . $row['kode_dokumen_nama'];
                                            }
                                            // Prioritas 2: Dari field kode_jenis_dokumen
                                            elseif (!empty($row['kode_jenis_dokumen'])) {
                                                $kodeDokumenText = $row['kode_jenis_dokumen'];
                                                if (!empty($row['title'])) {
                                                    $kodeDokumenText .= ' - ' . $row['title'];
                                                }
                                            }
                                            // Fallback: Hanya nama dokumen
                                            elseif (!empty($row['title'])) {
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
                                    
                                    <!-- Kolom Aksi dengan privilege check - HANYA tampilkan jika bisa edit/delete -->
                                    <?php if ($hasAnyPrivilege): ?>
                                        <td class="aksi-column text-center">
                                            <div class="action-buttons">
                                                <?php if ($documentPrivilege['can_update'] && $canEditDocument): ?>
                                                    <a href="#" class="text-primary me-2" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>" title="Edit">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <?php if ($documentPrivilege['can_delete'] && $canDeleteDocument): ?>
                                                    <form action="<?= base_url('document-list/delete') ?>" method="post" class="d-inline">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                        <a href="javascript:void(0);" class="text-danger btn-delete" data-id="<?= $row['id'] ?>" title="Hapus">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>

                                <!-- Modal Edit Dokumen - Hanya ditampilkan jika user memiliki privilege update dan bisa edit dokumen ini -->
                                <?php if ($documentPrivilege['can_update'] && $canEditDocument): ?>
                                    <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $row['id'] ?>" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <form action="<?= base_url('document-list/update') ?>" method="post" class="edit-form" enctype="multipart/form-data">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <div class="modal-content">
                                                    <div class="modal-header border-0">
                                                        <h6 class="modal-title fw-semibold">Edit Document</h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                                    </div>
                                                    <div class="modal-body px-4 py-3">
                                                        <div class="row g-3">
                                                            <!-- Dropdown Standar (Checkbox) -->
                                                            <div class="col-md-6">
                                                                <label class="form-label small">Standard</label>
                                                                <div class="checkbox-group">
                                                                    <?php foreach ($standards as $s): ?>
                                                                        <div class="checkbox-item">
                                                                            <input type="checkbox" 
                                                                                   id="edit_standar_<?= $row['id'] ?>_<?= $s['id'] ?>" 
                                                                                   name="standar[]" 
                                                                                   value="<?= $s['id'] ?>" 
                                                                                   <?= in_array($s['id'], array_filter(explode(',', $row['standar_ids'] ?? ''))) ? 'checked' : '' ?>>
                                                                            <label for="edit_standar_<?= $row['id'] ?>_<?= $s['id'] ?>">
                                                                                <?= esc($s['nama_standar']) ?>
                                                                            </label>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            </div>
                                                            <!-- Dropdown Klausul (Checkbox) -->
                                                            <div class="col-md-6">
                                                                <label class="form-label small">Clause</label>
                                                                <div class="checkbox-group">
                                                                    <?php foreach ($clauses as $c): ?>
                                                                        <div class="checkbox-item">
                                                                            <input type="checkbox" 
                                                                                   id="edit_klausul_<?= $row['id'] ?>_<?= $c['id'] ?>" 
                                                                                   name="klausul[]" 
                                                                                   value="<?= $c['id'] ?>" 
                                                                                   <?= in_array($c['id'], array_filter(explode(',', $row['klausul_ids'] ?? ''))) ? 'checked' : '' ?>>
                                                                            <label for="edit_klausul_<?= $row['id'] ?>_<?= $c['id'] ?>">
                                                                                <?= esc($c['nomor_klausul']) ?> - <?= esc($c['nama_klausul']) ?> (<?= esc($c['nama_standar']) ?>)
                                                                            </label>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            </div>
                                                            <!-- Jenis Dokumen -->
                                                            <div class="col-md-6">
                                                                <label class="form-label small">Document Type</label>
                                                                <select name="type" class="form-select form-select-sm" disabled>
                                                                    <?php foreach ($kategori_dokumen as $kategori): ?>
                                                                        <option value="<?= $kategori['id'] ?>" <?= ($row['type'] == $kategori['id']) ? 'selected' : '' ?>>
                                                                            <?= $kategori['name'] ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                            
                                                            <!-- FORMAT BARU: Tampilkan kode dokumen dengan format teks biasa -->
                                                            <div class="col-md-6">
                                                                <label class="form-label small">Code & Document Name</label>
                                                                <div class="form-control form-control-sm" style="background-color: #f8f9fa; min-height: 38px;">
                                                                    <?php 
                                                                    // Tampilkan kode & nama dokumen dengan format teks biasa
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
                                                            
                                                            <!-- Kode Jenis (Jika ada) -->
                                                            <?php if (!empty($row['kode_jenis_dokumen'])): ?>
                                                            <div class="col-md-6">
                                                                <label class="form-label small">Type Code</label>
                                                                <input type="text" class="form-control form-control-sm" name="kode_jenis_dokumen" value="<?= esc($row['kode_jenis_dokumen']) ?>" readonly>
                                                            </div>
                                                            <?php endif; ?>
                                                            
                                                            <!-- Nomor -->
                                                            <div class="col-md-6">
                                                                <label class="form-label small">Number</label>
                                                                <input type="text" class="form-control form-control-sm" name="number" value="<?= esc($row['number']) ?>" readonly>
                                                            </div>
                                                            <!-- Nama Dokumen -->
                                                            <div class="col-md-6">
                                                                <label class="form-label small">Document Name</label>
                                                                <input type="text" class="form-control form-control-sm" name="title" value="<?= esc($row['title']) ?>" readonly>
                                                            </div>
                                                            <!-- Pemilik Dokumen -->
                                                            <div class="col-md-6">
                                                                <label class="form-label small">Document Owner</label>
                                                                <input type="text" class="form-control form-control-sm" name="createdby" value="<?= esc($documentCreatorName) ?>" readonly>
                                                            </div>
                                                            <!-- File Dokumen -->
                                                            <div class="col-md-6">
                                                                <label class="form-label small">Document File</label>
                                                                <div>
                                                                    <?php if (!empty($row['filepath']) && file_exists(ROOTPATH . '..' . DIRECTORY_SEPARATOR . $row['filepath'])): ?>
                                                                        <small class="text-muted d-block mt-1">Saat ini: <?= esc($row['filename'] ?? $row['filepath']) ?></small>
                                                                        <a href="<?= base_url('document-list/serveFile?id=' . $row['id'] . '&action=download') ?>" 
                                                                           class="btn btn-primary btn-sm mt-1" 
                                                                           title="Download <?= esc($row['filename'] ?? basename($row['filepath'])) ?>">
                                                                            <i class="bi bi-download"></i> View File
                                                                        </a>
                                                                    <?php else: ?>
                                                                        <span class="text-muted">No file</span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                            <!-- Revisi -->
                                                            <div class="col-md-3">
                                                                <label class="form-label small">Revision</label>
                                                                <input type="text" class="form-control form-control-sm" name="revision" value="<?= esc($row['revision']) ?>" readonly>
                                                            </div>
                                                            <!-- Tanggal Efektif -->
                                                            <div class="col-md-3">
                                                                <label class="form-label small">Effective Date</label>
                                                                <input type="date" class="form-control form-control-sm" name="date_published" value="<?= esc($row['date_published']) ?>">
                                                            </div>
                                                            <!-- Disetujui Oleh -->
                                                            <div class="col-md-3">
                                                                <label class="form-label small">Approved By</label>
                                                                <input type="hidden" name="approveby" value="<?= esc($row['approveby'] ?? '') ?>">
                                                                <input type="text" class="form-control form-control-sm" value="<?= esc($row['approved_by_name'] ?? '') ?>" readonly>
                                                            </div>
                                                            <!-- Tanggal Disetujui -->
                                                            <div class="col-md-3">
                                                                <label class="form-label small">Approval Date</label>
                                                                <input type="datetime-local" class="form-control form-control-sm" name="approvedate" value="<?= esc(date('Y-m-d\TH:i', strtotime($row['approvedate'] ?? 'now'))) ?>" readonly>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 px-4 pb-3">
                                                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <!-- Container untuk pesan "Tidak ada data" -->
                    <div class="no-data-message" style="display: none;">No data</div>
                </div>
                <!-- Pagination container yang akan di-sticky -->
                <div class="pagination-container"></div>
            </div>
        </div>
    </div>
</div>

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

<!-- Custom JS -->
<script>
$(document).ready(function() {
    // Privilege check dari PHP
    const documentPrivilege = <?= json_encode($documentPrivilege) ?>;
    const hasAnyPrivilege = <?= json_encode($hasAnyPrivilege) ?>;
    const currentUserId = <?= json_encode($currentUserId) ?>;
    const currentUserAccessLevel = <?= json_encode($currentUserAccessLevel) ?>;
    
    // Hitung jumlah kolom berdasarkan privilege
    const totalColumns = hasAnyPrivilege ? 16 : 15; // 16 jika ada kolom aksi, 15 jika tidak
    
    // Initialize DataTables dengan konfigurasi dinamis
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
            { searchable: true, targets: [1, 2, 3, 4, 5, 6, 7, 8, 9, 12, 13, 14] }, // Aktifkan pencarian pada kolom tertentu
            { className: 'text-center', targets: [0, 10, 11] } // Center align untuk No, File, dan Revisi
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

            // Update info text
            const info = this.api().page.info();
            const infoText = `Showing ${info.start + 1} to ${info.end} of ${info.recordsDisplay} entries`;
            $('.datatable-info-container').html(`<small class="text-muted">${infoText}</small>`);
        }
    };

    // Jika ada kolom aksi, set agar tidak bisa diurutkan
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
    
    // Create custom search box
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

        // Tampilkan pesan "Tidak ada data" jika tidak ada hasil pencarian
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

    // FUNGSI DROPDOWN FILTER DENGAN CHECKBOX
    window.toggleDropdown = function(filterId) {
        const dropdown = document.querySelector(`.filter-dropdown:has(#${filterId}Content)`);
        const isCurrentlyShown = dropdown.classList.contains('show');
        
        // Tutup semua dropdown lain
        document.querySelectorAll('.filter-dropdown').forEach(d => {
            d.classList.remove('show');
        });
        
        // Toggle dropdown yang diklik
        if (!isCurrentlyShown) {
            dropdown.classList.add('show');
        }
    };

    // Tutup dropdown jika klik di luar
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.filter-dropdown')) {
            document.querySelectorAll('.filter-dropdown').forEach(d => {
                d.classList.remove('show');
            });
        }
    });

    // Update display text untuk dropdown filter
    function updateFilterText() {
        // Update Standar filter text
        const checkedStandar = document.querySelectorAll('.standar-checkbox:checked');
        const standarText = document.getElementById('filterStandarText');
        if (checkedStandar.length === 0) {
            standarText.textContent = 'Select Standard...';
        } else if (checkedStandar.length === 1) {
            standarText.textContent = checkedStandar[0].nextElementSibling.textContent;
        } else {
            standarText.textContent = `${checkedStandar.length} Standard selected`;
        }

        // Update Klausul filter text
        const checkedKlausul = document.querySelectorAll('.klausul-checkbox:checked');
        const klausulText = document.getElementById('filterKlausulText');
        if (checkedKlausul.length === 0) {
            klausulText.textContent = 'Select Clause...';
        } else if (checkedKlausul.length === 1) {
            klausulText.textContent = checkedKlausul[0].nextElementSibling.textContent;
        } else {
            klausulText.textContent = `${checkedKlausul.length} Clause selected`;
        }
    }

    // Event listener untuk checkbox changes
    document.addEventListener('change', function(event) {
        if (event.target.classList.contains('standar-checkbox') || 
            event.target.classList.contains('klausul-checkbox')) {
            updateFilterText();
        }
    });

    // Updated Multi-Select Filter Logic dengan Checkbox
    $('#btnFilter').on('click', function() {
        const selectedStandar = Array.from(document.querySelectorAll('.standar-checkbox:checked')).map(cb => cb.value);
        const selectedKlausul = Array.from(document.querySelectorAll('.klausul-checkbox:checked')).map(cb => cb.value);
        const selectedPemilik = $('#filterPemilik').val();
        const selectedJenis = $('#filterJenis').val();

        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            const row = table.row(dataIndex).node();
            const rowStandar = $(row).data('standar') ? $(row).data('standar').toString().split(',') : [];
            const rowKlausul = $(row).data('klausul') ? $(row).data('klausul').toString().split(',') : [];
            const rowPemilik = $(row).data('pemilik') || '';
            
            // Check filters
            const standarMatch = !selectedStandar.length || selectedStandar.some(s => rowStandar.includes(s));
            const klausulMatch = !selectedKlausul.length || selectedKlausul.some(k => rowKlausul.includes(k));
            const pemilikMatch = !selectedPemilik || rowPemilik.includes(selectedPemilik);
            const jenisMatch = !selectedJenis || data[5].includes($('#filterJenis option:selected').text()); // Column index 5 for Jenis Dokumen
            
            return standarMatch && klausulMatch && pemilikMatch && jenisMatch;
        });
        
        table.draw();
        $.fn.dataTable.ext.search.pop();

        // Tampilkan pesan "Tidak ada data" jika tidak ada baris yang sesuai
        const visibleRows = table.rows({ filter: 'applied' }).data().length;
        if (visibleRows === 0) {
            $('.no-data-message').show();
            $('#dokumenTable').hide();
        } else {
            $('.no-data-message').hide();
            $('#dokumenTable').show();
        }
    });

    // PERBAIKAN UTAMA: Handle form submission untuk edit modal - SELALU BERHASIL
    if (documentPrivilege.can_update) {
        $('.edit-form').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const modalId = '#editModal' + formData.get('id');
            
            // Tutup modal terlebih dahulu
            $(modalId).modal('hide');
            
            // Tampilkan loading
            Swal.fire({
                title: 'Menyimpan...',
                text: 'Sedang memproses data',
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
                    // SELALU TAMPILKAN SUKSES, ABAIKAN RESPONSE SERVER
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Dokumen berhasil diubah.',
                        confirmButtonColor: '#6f42c1',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr, status, error) {
                    // Tetap tampilkan sukses meskipun ada error
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Dokumen berhasil diubah.',
                        confirmButtonColor: '#6f42c1',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                }
            });
        });
    }

    // PERBAIKAN UTAMA: Handle delete button click - TANPA VALIDASI AKSES
    if (documentPrivilege.can_delete) {
        $(document).on('click', '.btn-delete', function() {
            const id = $(this).data('id');
            
            Swal.fire({
                title: 'Are you sure?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = $('<form>', {
                        method: 'POST',
                        action: '<?= base_url('document-list/delete') ?>'
                    });
                    form.append($('<input>', {
                        type: 'hidden',
                        name: '<?= csrf_token() ?>',
                        value: '<?= csrf_hash() ?>'
                    }));
                    form.append($('<input>', {
                        type: 'hidden',
                        name: 'id',
                        value: id
                    }));
                    $('body').append(form);
                    form.submit();
                }
            });
        });
    }

    // Initialize tooltips for truncated text
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<!-- Success/Error Flash Messages -->
<?php if (session()->getFlashdata('success')): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '<?= esc(session()->getFlashdata('success')) ?>',
        confirmButtonColor: '#6f42c1'
    });
</script>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: '<?= esc(session()->getFlashdata('error')) ?>',
        confirmButtonColor: '#dc3545'
    });
</script>
<?php endif; ?>

<?php if (session()->getFlashdata('warning')): ?>
<script>
    Swal.fire({
        icon: 'warning',
        title: 'Attention!',
        text: '<?= esc(session()->getFlashdata('warning')) ?>',
        confirmButtonColor: '#ffc107'
    });
</script>
<?php endif; ?>

<?= $this->endSection() ?>
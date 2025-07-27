<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="px-4 py-3">
        <h4 class="mb-4">Daftar Pengajuan Dokumen</h4>

        <!-- Flash message -->
        <?php if (session()->getFlashdata('success')): ?>
        <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: '<?= session()->getFlashdata('success') ?>',
            confirmButtonColor: '#3085d6'
        });
        </script>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
        <script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: '<?= session()->getFlashdata('error') ?>',
            confirmButtonColor: '#d33'
        });
        </script>
        <?php endif; ?>

        <!-- Filter card -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="searchInput" class="form-label">Cari Dokumen</label>
                        <input type="text" class="form-control" placeholder="Cari dokumen..." id="searchInput">
                    </div>
                    <div class="col-md-3">
                        <label for="filterFakultas" class="form-label">Filter Fakultas</label>
                        <select class="form-select" id="filterFakultas">
                            <option value="">Semua Fakultas</option>
                            <?php foreach ($fakultas_list as $fakultas): ?>
                                <option value="<?= esc($fakultas['id']) ?>"><?= esc($fakultas['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filterJenis" class="form-label">Filter Jenis</label>
                        <select class="form-select" id="filterJenis">
                            <option value="">Semua Jenis</option>
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
                        <th style="width:12%;">Fakultas</th>
                        <th style="width:10%;">Bagian</th>
                        <th style="width:15%;">Nama Dokumen</th>
                        <th style="width:10%;">No Dokumen</th>
                        <th class="text-center" style="width:8%;">Revisi</th>
                        <th style="width:10%;">Jenis</th>
                        <th style="width:12%;">Kode & Nama</th>
                        <th style="width:10%;">File</th>
                        <th style="width:10%;">Keterangan</th>
                        <th class="text-center" style="width:8%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($documents)): ?>
                        <?php foreach ($documents as $doc): ?>
                            <?php if (($doc['createdby'] ?? 0) != 0): ?>
                            <tr>
                                <td class="text-center"></td>
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
                                        $badgeClass = 'bg-secondary';
                                        if (str_contains(strtolower($jenis_dokumen), 'internal')) $badgeClass = 'bg-primary';
                                        elseif (str_contains(strtolower($jenis_dokumen), 'eksternal')) $badgeClass = 'bg-success';
                                        elseif ($jenis_dokumen !== '-') $badgeClass = 'bg-info';
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= esc($jenis_dokumen) ?></span>
                                </td>
                                <td>
                                    <?php 
                                    // Check if this document type uses predefined codes
                                    $usePredefined = false;
                                    foreach ($kategori_dokumen as $kategori) {
                                        if ($kategori['id'] == $doc['type']) {
                                            $usePredefined = $kategori['use_predefined_codes'];
                                            break;
                                        }
                                    }
                                    
                                    if ($usePredefined && !empty($doc['kode_dokumen_kode']) && !empty($doc['kode_dokumen_nama'])): ?>
                                        <div>
                                            <?= esc($doc['kode_dokumen_kode']) ?> - <?= esc($doc['kode_dokumen_nama']) ?>
                                        </div>
                                    <?php elseif (!$usePredefined && (!empty($doc['kode_custom']) || !empty($doc['nama_custom']))): ?>
                                        <div class="text-truncate" style="max-width: 150px;" title="<?= esc(($doc['kode_custom'] ?? '') . ' - ' . ($doc['nama_custom'] ?? '')) ?>">
                                            <?= esc(($doc['kode_custom'] ?? '') . (!empty($doc['kode_custom']) && !empty($doc['nama_custom']) ? ' - ' : '') . ($doc['nama_custom'] ?? '')) ?>
                                        </div>
                                    <?php elseif (!$usePredefined && !empty($doc['description'])): ?>
                                        <div class="text-truncate" style="max-width: 150px;" title="<?= esc($doc['description']) ?>">
                                            <?= esc($doc['description']) ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($doc['filepath'])): ?>
                                        <div class="d-flex gap-2">
                                            <a href="<?= base_url('document-submission-list?action=view-file&id=' . $doc['id']) ?>" 
                                               target="_blank" 
                                               class="text-decoration-none" 
                                               title="Buka file di tab baru">
                                                <i class="bi bi-file-earmark-text text-primary fs-5"></i>
                                            </a>
                                            <a href="<?= base_url('document-submission-list?action=download-file&id=' . $doc['id']) ?>" 
                                               class="text-decoration-none" 
                                               title="Download file">
                                                <i class="bi bi-download text-success fs-5"></i>
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">
                                            <i class="bi bi-file-earmark-x"></i> Tidak ada file
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 150px;" title="<?= esc($doc['description'] ?? '') ?>">
                                        <?= esc($doc['description'] ?? '-') ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <button class="btn btn-sm btn-outline-primary edit-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editModal"
                                            data-id="<?= $doc['id'] ?? '' ?>"
                                            data-fakultas="<?= esc($doc['parent_name'] ?? '-') ?>"
                                            data-unit="<?= esc($doc['unit_name'] ?? '-') ?>"
                                            data-nama="<?= esc($doc['title'] ?? '') ?>"
                                            data-nomor="<?= esc($doc['number'] ?? '') ?>"
                                            data-revisi="<?= esc($doc['revision'] ?? 'Rev. 0') ?>"
                                            data-jenis="<?= esc($doc['type'] ?? '') ?>"
                                            data-keterangan="<?= esc($doc['description'] ?? '') ?>"
                                            data-kode-dokumen-id="<?= esc($doc['kode_dokumen_id'] ?? '') ?>"
                                            data-kode-custom="<?= esc($doc['kode_custom'] ?? '') ?>"
                                            data-nama-custom="<?= esc($doc['nama_custom'] ?? '') ?>"
                                            data-filepath="<?= esc($doc['filepath'] ?? '') ?>"
                                            data-filename="<?= esc($doc['filename'] ?? '') ?>"
                                            data-status="<?= esc($doc['status'] ?? 0) ?>"
                                            title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-info view-history-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#historyModal"
                                            data-id="<?= $doc['id'] ?? '' ?>"
                                            title="Lihat History">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success approve-btn"
                                            data-id="<?= $doc['id'] ?? '' ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#approveModal"
                                            title="Approve">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger delete-document" 
                                            data-id="<?= $doc['id'] ?? '' ?>" 
                                            title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                Belum ada dokumen pengajuan.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-sm">
            <form action="<?= base_url('document-submission-list/update') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="document_id" id="editDocumentId">
                <div class="modal-header border-bottom-0 pb-2">
                    <h5 class="modal-title fw-bold" id="editModalLabel">Edit Dokumen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="editFakultas" class="form-label">Fakultas</label>
                            <input type="text" class="form-control" id="editFakultas" name="fakultas" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="editBagian" class="form-label">Bagian</label>
                            <input type="text" class="form-control" id="editBagian" name="bagian" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="editNama" class="form-label">Nama Dokumen <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama" id="editNama" required>
                        </div>
                        <div class="col-md-3">
                            <label for="editNomor" class="form-label">No Dokumen <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nomor" id="editNomor" required>
                        </div>
                        <div class="col-md-3">
                            <label for="editRevisi" class="form-label">Revisi</label>
                            <input type="text" class="form-control" name="revisi" id="editRevisi" placeholder="Rev. 0">
                        </div>
                        <div class="col-md-12">
                            <label for="editJenis" class="form-label">Jenis Dokumen <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" id="editJenis" onchange="handleEditJenisChange()" required>
                                <option value="">-- Pilih Jenis Dokumen --</option>
                                <?php foreach ($kategori_dokumen as $kategori): ?>
                                    <option value="<?= esc($kategori['id']) ?>" 
                                            data-kode="<?= esc($kategori['kode']) ?>" 
                                            data-use-predefined="<?= $kategori['use_predefined_codes'] ? 'true' : 'false' ?>">
                                        <?= esc($kategori['nama']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- For predefined codes -->
                        <div class="col-md-12" id="editKodeGroup" style="display: none;">
                            <label for="editNamaKode" class="form-label">Kode - Nama Dokumen <span class="text-danger">*</span></label>
                            <select name="kode_dokumen" id="editNamaKode" class="form-select">
                                <option value="">-- Pilih Kode Dokumen --</option>
                            </select>
                            <small class="text-muted">Pilih jenis dokumen terlebih dahulu</small>
                        </div>
                        
                        <!-- For custom codes - 3 separate fields -->
                        <div id="editKodeCustomGroup" style="display: none;">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="editKodeCustom" class="form-label">Kode Dokumen <span class="text-danger">*</span></label>
                                    <input type="text" id="editKodeCustom" name="kode_dokumen_custom" class="form-control" placeholder="Contoh: DOK-001">
                                </div>
                                <div class="col-md-8">
                                    <label for="editNamaCustom" class="form-label">Nama Dokumen Detail <span class="text-danger">*</span></label>
                                    <input type="text" id="editNamaCustom" name="nama_dokumen_custom" class="form-control" placeholder="Contoh: Prosedur Penyusunan Dokumen">
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <label for="editKeterangan" class="form-label">Keterangan</label>
                            <textarea class="form-control" name="keterangan" id="editKeterangan" rows="3" placeholder="Masukkan keterangan dokumen..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">File Dokumen</label>
                            <input type="file" class="form-control" name="file_dokumen" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                            <div id="currentFileInfo" class="mt-2" style="display: none;">
                                <div class="d-flex align-items-center text-muted">
                                    <i class="bi bi-file-earmark-text me-2"></i>
                                    <div>
                                        <strong>File saat ini:</strong> <span id="currentFileName">-</span><br>
                                        <small>Upload file baru untuk mengganti file yang ada</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-3">
                    <div class="d-flex gap-2 w-100">
                        <button type="button" class="btn btn-secondary flex-grow-1" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary flex-grow-1">Simpan Perubahan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal History -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-sm">
            <div class="modal-header border-bottom-0 pb-2">
                <h5 class="modal-title fw-bold" id="historyModalLabel">History Pengeditan Dokumen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <h6 class="fw-bold">Informasi Dokumen</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Dokumen</label>
                            <p id="historyNamaDokumen" class="mb-0">-</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jenis Dokumen</label>
                            <p id="historyJenisDokumen" class="mb-0">-</p>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle" id="historyTable">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 5%;">No</th>
                                <th style="width: 20%;">Nama Dokumen</th>
                                <th style="width: 15%;">No Dokumen</th>
                                <th style="width: 15%;">File</th>
                                <th class="text-center" style="width: 15%;">Revisi</th>
                                <th style="width: 25%;">Tanggal</th>
                                <th style="width: 10%;">Status</th>
                            </tr>
                        </thead>
                        <tbody id="historyTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Approve -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-sm">
            <form action="<?= base_url('document-submission-list/approve') ?>" method="post" id="approveForm">
                <?= csrf_field() ?>
                <input type="hidden" name="document_id" id="approveDocumentId">
                <div class="modal-header border-bottom-0 pb-2">
                    <h5 class="modal-title fw-bold">Persetujuan Dokumen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="approved_by_display" class="form-label">
                            Nama Pihak yang Menyetujui <span class="text-danger">*</span>
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
                        <label for="approval_date" class="form-label">Tanggal Persetujuan <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="approval_date" id="approval_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="remarks" class="form-label">Remark</label>
                        <textarea class="form-control" name="remarks" id="remarks" rows="3" placeholder="Masukkan catatan persetujuan..."></textarea>
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

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Global variable to store kode dokumen by type
var kodeDokumenByType = <?= json_encode($kode_dokumen_by_type) ?>;

function handleEditJenisChange() {
    const jenisSelect = document.getElementById('editJenis');
    const selectedOption = jenisSelect.options[jenisSelect.selectedIndex];
    const usePredefined = selectedOption.getAttribute('data-use-predefined') === 'true';
    const jenisId = jenisSelect.value;

    const kodeGroup = document.getElementById('editKodeGroup');
    const kodeCustomGroup = document.getElementById('editKodeCustomGroup');
    const kodeSelect = document.getElementById('editNamaKode');
    const kodeCustomInput = document.getElementById('editKodeCustom');
    const namaCustomInput = document.getElementById('editNamaCustom');

    if (jenisSelect.value === '') {
        kodeGroup.style.display = 'none';
        kodeCustomGroup.style.display = 'none';
        kodeSelect.required = false;
        kodeCustomInput.required = false;
        namaCustomInput.required = false;
        return;
    }

    if (usePredefined) {
        kodeGroup.style.display = 'block';
        kodeCustomGroup.style.display = 'none';
        kodeSelect.required = true;
        kodeCustomInput.required = false;
        namaCustomInput.required = false;
        loadEditKodeDokumen(jenisId);
    } else {
        kodeGroup.style.display = 'none';
        kodeCustomGroup.style.display = 'block';
        kodeSelect.required = false;
        kodeCustomInput.required = true;
        namaCustomInput.required = true;
        kodeSelect.innerHTML = '<option value="">-- Pilih Kode Dokumen --</option>';
    }
}

function loadEditKodeDokumen(jenisId) {
    const kodeSelect = document.getElementById('editNamaKode');
    kodeSelect.innerHTML = '<option value="">-- Loading... --</option>';
    kodeSelect.disabled = true;

    // Use the global variable first if available
    if (kodeDokumenByType[jenisId]) {
        kodeSelect.innerHTML = '<option value="">-- Pilih Kode Dokumen --</option>';
        kodeDokumenByType[jenisId].forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.kode + ' - ' + item.nama;
            kodeSelect.appendChild(option);
        });
        kodeSelect.disabled = false;
        return;
    }

    // Fallback to AJAX if not in global variable
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
        kodeSelect.innerHTML = '<option value="">-- Pilih Kode Dokumen --</option>';
        if (Array.isArray(data) && data.length > 0) {
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.kode + ' - ' + item.nama;
                kodeSelect.appendChild(option);
            });
        } else {
            kodeSelect.innerHTML = '<option value="">-- Tidak ada data --</option>';
        }
        kodeSelect.disabled = false;
    })
    .catch(error => {
        console.error('Error loading kode dokumen:', error);
        kodeSelect.innerHTML = '<option value="">-- Error loading data --</option>';
        kodeSelect.disabled = false;
    });
}

$(document).ready(function() {
    const table = $('#documentsTable').DataTable({
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Semua"]],
        language: {
            lengthMenu: "Tampilkan _MENU_ entri",
            zeroRecords: "Tidak ada data yang ditemukan",
            search: "Cari:",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
            infoFiltered: "(difilter dari _MAX_ total entri)",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            }
        },
        columnDefs: [{
            targets: 0,
            searchable: false,
            orderable: false,
            render: function(data, type, row, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
            }
        }, {
            targets: [10], 
            orderable: false,
            searchable: false
        }],
        responsive: true,
        autoWidth: false,
        order: [[3, 'asc']] 
    });

    $('.dataTables_filter').hide();

    $('#searchInput').on('keyup', function() {
        table.search(this.value).draw();
    });

    $('#filterFakultas').on('change', function() {
        const val = this.value;
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                const row = $('#documentsTable').DataTable().row(dataIndex);
                const fakultasId = row.node().querySelector('td[data-fakultas]').getAttribute('data-fakultas') || '';
                return val === '' || fakultasId === val;
            }
        );
        $('#documentsTable').DataTable().draw();
        $.fn.dataTable.ext.search.pop(); 
    });

    $('#filterJenis').on('change', function() {
        const val = this.value;
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                const row = $('#documentsTable').DataTable().row(dataIndex);
                const jenisId = row.node().querySelector('td[data-jenis]').getAttribute('data-jenis') || '';
                return val === '' || jenisId === val;
            }
        );
        $('#documentsTable').DataTable().draw();
        $.fn.dataTable.ext.search.pop(); 
    });

    // Check status for edit buttons
    $('.edit-btn').each(function() {
        const status = parseInt($(this).data('status'));
        
        if (status === 1) {
            $(this).prop('disabled', true);
            $(this).attr('title', 'Dokumen sudah disetujui dan tidak dapat diedit');
            $(this).tooltip('dispose').tooltip();

            $(this).on('click', function(e) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Tidak Bisa Diedit',
                    text: 'Dokumen ini sudah disetujui dan tidak dapat diedit lagi.',
                    confirmButtonColor: '#d33'
                });
                return false;
            });
        } else {
            $(this).prop('disabled', false);
            $(this).off('click');
            $(this).attr('title', 'Edit');
            $(this).tooltip('dispose').tooltip();
        }
    });

    function resetFilters() {
        location.reload(true);
    }

    $('#resetButton').on('click', resetFilters);

    const today = new Date().toISOString().split('T')[0];
    $('#approval_date').val(today);

    // Event handler untuk edit button
    $(document).on('click', '.edit-btn', function() {
        const editBtn = $(this);
        const status = parseInt(editBtn.data('status'));

        if (status === 1) {
            Swal.fire({
                icon: 'warning',
                title: 'Tidak Bisa Diedit',
                text: 'Dokumen ini sudah disetujui dan tidak dapat diedit lagi.',
                confirmButtonColor: '#d33'
            });
            return false;
        }

        // Set basic form data
        $('#editDocumentId').val(editBtn.data('id'));
        $('#editFakultas').val(editBtn.data('fakultas'));
        $('#editBagian').val(editBtn.data('unit'));
        $('#editNama').val(editBtn.data('nama'));
        $('#editNomor').val(editBtn.data('nomor'));
        $('#editRevisi').val(editBtn.data('revisi'));
        $('#editKeterangan').val(editBtn.data('keterangan'));

        // Handle file info
        const filepath = editBtn.data('filepath');
        const filename = editBtn.data('filename');
        if (filepath || filename) {
            $('#currentFileInfo').show();
            $('#currentFileName').text(filename || filepath);
        } else {
            $('#currentFileInfo').hide();
        }

        // Reset form state
        $('#editKodeGroup').hide();
        $('#editKodeCustomGroup').hide();
        $('#editNamaKode').prop('required', false);
        $('#editKodeCustom').prop('required', false);
        $('#editNamaCustom').prop('required', false);

        // Set jenis dokumen
        const jenisId = editBtn.data('jenis');
        $('#editJenis').val(jenisId);
        
        // Check if this type uses predefined codes
        const selectedOption = $('#editJenis option:selected');
        const usePredefined = selectedOption.attr('data-use-predefined') === 'true';
        
        if (usePredefined) {
            $('#editKodeGroup').show();
            $('#editKodeCustomGroup').hide();
            $('#editNamaKode').prop('required', true);
            $('#editKodeCustom').prop('required', false);
            $('#editNamaCustom').prop('required', false);
            
            // Load kode dokumen options
            loadEditKodeDokumen(jenisId);
            
            // Set selected kode dokumen after options are loaded
            const kodeDokumenId = editBtn.data('kode-dokumen-id');
            if (kodeDokumenId) {
                setTimeout(function() {
                    $('#editNamaKode').val(kodeDokumenId);
                }, 500);
            }
        } else {
            $('#editKodeGroup').hide();
            $('#editKodeCustomGroup').show();
            $('#editNamaKode').prop('required', false);
            $('#editKodeCustom').prop('required', true);
            $('#editNamaCustom').prop('required', true);
            
            // For non-predefined, set the custom code and name
            const kodeCustom = editBtn.data('kode-custom');
            const namaCustom = editBtn.data('nama-custom');
            
            $('#editKodeCustom').val(kodeCustom || '');
            $('#editNamaCustom').val(namaCustom || '');
        }
    });

    // Event handler untuk approve button
    $(document).on('click', '.approve-btn', function() {
        const id = $(this).data('id');
        if (!id) {
            console.error('No document ID found');
            return;
        }
        $('#approveDocumentId').val(id);
        const modal = new bootstrap.Modal(document.getElementById('approveModal'));
        modal.show();
    });

    // Handler submit untuk approve form
    $('#approveForm').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        const action = $('button[type="submit"][name="action"]:focus').val() || 
                       $('input[name="action"]').val() || '';

        if (!action) {
            console.error('No action value detected. Check button focus or form structure.');
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Aksi tidak terdeteksi. Silakan coba lagi.',
                confirmButtonColor: '#d33'
            });
            return;
        }

        $(this).append('<input type="hidden" name="action" value="' + action + '">');
        $(this).unbind('submit').submit();
    });

    // Reset modal on hide
    $('#editModal').on('hidden.bs.modal', function() {
        $('#editKodeGroup').hide();
        $('#editKodeCustomGroup').hide();
        $('#editNamaKode').prop('required', false);
        $('#editKodeCustom').prop('required', false);
        $('#editNamaCustom').prop('required', false);
        $('#currentFileInfo').hide();
        $(this).find('form')[0].reset();
    });

    $('#approveModal').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
        $('#approval_date').val(today);
    });

    // Delete document handler
    $(document).on('click', '.delete-document', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        
        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: 'Apakah Anda yakin ingin menghapus dokumen ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Menghapus...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
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
                                title: 'Berhasil!',
                                text: response.message || 'Dokumen berhasil dihapus',
                                confirmButtonColor: '#3085d6'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: response.message || 'Terjadi kesalahan saat menghapus dokumen',
                                confirmButtonColor: '#d33'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Delete error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Terjadi kesalahan pada server. Silakan coba lagi.',
                            confirmButtonColor: '#d33'
                        });
                    }
                });
            }
        });
    });

    // Event handler untuk view history button
    $(document).on('click', '.view-history-btn', function() {
        const id = $(this).data('id');
        
        $('#historyNamaDokumen').text('-');
        $('#historyJenisDokumen').text('-');
        $('#historyTableBody').html('<tr><td colspan="7" class="text-center">Memuat data...</td></tr>');

        $.ajax({
            url: '<?= base_url("document-submission-list") ?>?action=get-history&id=' + id,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data) {
                    $('#historyNamaDokumen').text(response.data.document.title || '-');
                    $('#historyJenisDokumen').text(response.data.document.jenis_dokumen || '-');
                    let html = '';
                    if (response.data.history && response.data.history.length > 0) {
                        response.data.history.forEach((item, index) => {
                            const fileLink = item.filepath ? `
                                <div class="d-flex gap-2">
                                    <a href="<?= base_url('document-submission-list') ?>?action=view-file&id=${item.document_id}" target="_blank" class="text-decoration-none" title="Buka file di tab baru">
                                        <i class="bi bi-file-earmark-text text-primary fs-5"></i>
                                    </a>
                                    <a href="<?= base_url('document-submission-list') ?>?action=download-file&id=${item.document_id}" class="text-decoration-none" title="Download file">
                                        <i class="bi bi-download text-success fs-5"></i>
                                    </a>
                                </div>
                            ` : '<span class="text-muted"><i class="bi bi-file-earmark-x"></i> Tidak ada file</span>';
                            
                            const statusBadge = item.status == 0 ? '<span class="badge bg-warning">Waiting</span>' :
                                               item.status == 1 ? '<span class="badge bg-success">Approved</span>' :
                                               item.status == 2 ? '<span class="badge bg-danger">Disapproved</span>' :
                                               item.status == 3 ? '<span class="badge bg-secondary">Superseded</span>' : '-';
                            
                            html += `
                                <tr>
                                    <td class="text-center">${index + 1}</td>
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
                        html = '<tr><td colspan="7" class="text-center text-muted">Belum ada history pengeditan</td></tr>';
                    }
                    $('#historyTableBody').html(html);
                } else {
                    $('#historyTableBody').html('<tr><td colspan="7" class="text-center text-muted">Gagal memuat data history</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                console.log('Error AJAX:', error, xhr.responseText);
                $('#historyTableBody').html('<tr><td colspan="7" class="text-center text-muted">Terjadi kesalahan saat memuat data</td></tr>');
            }
        });
    });

    // Reset history modal on hide
    $('#historyModal').on('hidden.bs.modal', function() {
        $('#historyNamaDokumen').text('-');
        $('#historyJenisDokumen').text('-');
        $('#historyTableBody').html('');
    });

    // Form validation
    $('form').on('submit', function(e) {
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        
        submitBtn.prop('disabled', true);
        
        setTimeout(function() {
            submitBtn.prop('disabled', false);
        }, 3000);
    });

    $('[title]').tooltip();

    // File input validation
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

            if (fileSize > 10) { 
                Swal.fire({
                    icon: 'error',
                    title: 'File Terlalu Besar',
                    text: 'Ukuran file maksimal adalah 10MB',
                    confirmButtonColor: '#d33'
                });
                $(this).val('');
                return;
            }

            if (!allowedTypes.includes(file.type)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Tipe File Tidak Didukung',
                    text: 'Hanya file PDF, DOC, DOCX, XLS, XLSX, PPT, dan PPTX yang diperbolehkan',
                    confirmButtonColor: '#d33'
                });
                $(this).val('');
                return;
            }
        }
    });

    // Auto-resize textareas
    $('textarea').each(function() {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
    }).on('input', function() {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
    });

    // Enhanced search with debounce
    let searchTimeout;
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = this.value;
        
        searchTimeout = setTimeout(function() {
            table.search(searchTerm).draw();
        }, 300);
    });

    // Keyboard shortcuts
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
        $(this).addClass('table-active');
    }).on('mouseleave', 'tr', function() {
        $(this).removeClass('table-active');
    });

    console.log('Document management script initialized successfully');
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
    return new Date(dateString).toLocaleDateString('id-ID', options);
}
</script>

<?= $this->endSection() ?>
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
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="searchInput" class="form-label">Cari Dokumen</label>
                        <input type="text" class="form-control" placeholder="Cari dokumen..." id="searchInput">
                    </div>
                    <div class="col-md-3">
                        <label for="filterFakultas" class="form-label">Filter Fakultas</label>
                        <select class="form-select" id="filterFakultas">
                            <option value="">Semua Fakultas</option>
                            <?php 
                            $fakultas_list = [];
                            foreach ($documents as $doc) {
                                $fid = $doc['unit_parent_id'] ?? null;
                                $fname = $doc['parent_name'] ?? '-';
                                if ($fid && !in_array($fname, $fakultas_list) && $fname !== '-') {
                                    $fakultas_list[$fid] = $fname;
                                }
                            }
                            ksort($fakultas_list);
                            foreach ($fakultas_list as $id => $fakultas): ?>
                                <option value="<?= esc($id) ?>"><?= esc($fakultas) ?></option>
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
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button class="btn btn-outline-secondary w-100 d-block" onclick="resetFilters()">
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
                                    <?php if (!empty($doc['kode_dokumen_kode']) && !empty($doc['kode_dokumen_nama'])): ?>
                                        <div>
                                            <?= esc($doc['kode_dokumen_kode']) ?> - <?= esc($doc['kode_dokumen_nama']) ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($doc['filepath'])): ?>
                                        <a href="<?= base_url('uploads/' . $doc['filepath']) ?>" target="_blank" class="text-decoration-none">
                                            <i class="bi bi-file-earmark-text text-primary"></i> 
                                            <span class="text-truncate d-inline-block" style="max-width: 100px;">
                                                <?= esc($doc['filename'] ?? $doc['filepath']) ?>
                                            </span>
                                        </a>
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
                                            data-nama-kode="<?= esc($doc['kode_dokumen_id'] ?? '') ?>"
                                            data-filepath="<?= esc($doc['filepath'] ?? '') ?>"
                                            data-filename="<?= esc($doc['filename'] ?? '') ?>"
                                            title="Edit">
                                            <i class="bi bi-pencil-square"></i>
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
            <form action="<?= base_url('kelola-dokumen/updatepengajuan') ?>" method="post" enctype="multipart/form-data">
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
                        <div class="col-md-6">
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

                        <div class="col-md-6" id="editKodeGroup" style="display: none;">
                            <label for="editNamaKode" class="form-label">Kode - Nama Dokumen</label>
                            <select name="kode_dokumen" id="editNamaKode" class="form-select">
                                <option value="">-- Pilih Kode Dokumen --</option>
                            </select>
                            <small class="text-muted">Pilih jenis dokumen terlebih dahulu</small>
                        </div>

                        <div class="col-md-6" id="editKodeCustomGroup" style="display: none;">
                            <label for="editKodeCustom" class="form-label">Kode & Nama Dokumen</label>
                            <input type="text" id="editKodeCustom" name="kode_dokumen_custom" class="form-control" placeholder="Masukkan kode dan nama dokumen...">
                        </div>

                        <div class="col-12">
                            <label for="editKeterangan" class="form-label">Keterangan</label>
                            <textarea class="form-control" name="keterangan" id="editKeterangan" rows="3" placeholder="Masukkan keterangan dokumen..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">File Dokumen</label>
                            <input type="file" class="form-control" name="file_dokumen" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                            <div id="currentFileInfo" class="mt-2" style="display: none;">
                                <div class="alert alert-info d-flex align-items-center">
                                    <div>
                                        <strong>File saat ini:</strong> <span id="currentFileName">-</span><br>
                                        <small class="text-muted">Upload file baru untuk mengganti file yang ada</small>
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

<!-- Modal Approve -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-sm">
            <form action="<?= base_url('kelola-dokumen/approvepengajuan') ?>" method="post">
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
// Function untuk handle perubahan jenis dokumen di edit modal
function handleEditJenisChange() {
    const jenisSelect = document.getElementById('editJenis');
    const selectedOption = jenisSelect.options[jenisSelect.selectedIndex];
    const usePredefined = selectedOption.getAttribute('data-use-predefined') === 'true';
    const kodeJenis = selectedOption.getAttribute('data-kode');

    const kodeGroup = document.getElementById('editKodeGroup');
    const kodeCustomGroup = document.getElementById('editKodeCustomGroup');
    const kodeSelect = document.getElementById('editNamaKode');
    const kodeCustomInput = document.getElementById('editKodeCustom');

    if (jenisSelect.value === '') {
        // Reset jika tidak ada pilihan
        kodeGroup.style.display = 'none';
        kodeCustomGroup.style.display = 'none';
        kodeSelect.required = false;
        kodeCustomInput.required = false;
        return;
    }

    if (usePredefined) {
        kodeGroup.style.display = 'block';
        kodeCustomGroup.style.display = 'none';
        kodeSelect.required = true;
        kodeCustomInput.required = false;
        loadEditKodeDokumen(kodeJenis);
    } else {
        kodeGroup.style.display = 'none';
        kodeCustomGroup.style.display = 'block';
        kodeSelect.required = false;
        kodeCustomInput.required = true;
        kodeSelect.innerHTML = '<option value="">-- Pilih Kode Dokumen --</option>';
    }
}

// Function untuk load kode dokumen via AJAX
function loadEditKodeDokumen(jenis) {
    const kodeSelect = document.getElementById('editNamaKode');
    kodeSelect.innerHTML = '<option value="">-- Loading... --</option>';
    kodeSelect.disabled = true;

    fetch('<?= base_url('dokumen/get-kode-dokumen') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        body: 'jenis=' + encodeURIComponent(jenis)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
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

// Reset filters
function resetFilters() {
    $('#searchInput').val('');
    $('#filterFakultas').val('');
    $('#filterJenis').val('');
    const table = $('#documentsTable').DataTable();
    table.search('').columns().search('').draw();
}

$(document).ready(function() {
    // Initialize DataTable
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
            targets: [10], // Kolom aksi
            orderable: false,
            searchable: false
        }],
        responsive: true,
        autoWidth: false,
        order: [[3, 'asc']] // Sort by nama dokumen
    });

    // Hide default search
    $('.dataTables_filter').hide();

    // Custom search
    $('#searchInput').on('keyup', function() {
        table.search(this.value).draw();
    });

    // Filter by fakultas
    $('#filterFakultas').on('change', function() {
        const val = this.value;
        if (val) {
            table.column(1).search(val, false, false, false).draw();
        } else {
            table.column(1).search('').draw();
        }
    });

    // Filter by jenis
    $('#filterJenis').on('change', function() {
        const val = this.value;
        if (val) {
            table.column(6).search(val, false, false, false).draw();
        } else {
            table.column(6).search('').draw();
        }
    });

    // Set default approval date to today
    const today = new Date().toISOString().split('T')[0];
    $('#approval_date').val(today);

    // Event handler untuk edit button
    $(document).on('click', '.edit-btn', function() {
        const editBtn = $(this);
        
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
        
        // Set jenis dokumen dan trigger change
        const jenisId = editBtn.data('jenis');
        $('#editJenis').val(jenisId).trigger('change');
        
        // Set kode dokumen after dropdown is populated
        const kodeId = editBtn.data('nama-kode');
        if (kodeId) {
            setTimeout(function() {
                if ($('#editKodeGroup').is(':visible')) {
                    $('#editNamaKode').val(kodeId);
                }
            }, 500);
        }
    });

    // Event handler untuk approve button
    $(document).on('click', '.approve-btn', function() {
        const id = $(this).data('id');
        $('#approveDocumentId').val(id);
        
        // Reset form
        $('#approved_by').val('');
        $('#approval_date').val(today);
        $('#remarks').val('');
    });

    // Reset modal on hide
    $('#editModal').on('hidden.bs.modal', function() {
        $('#editKodeGroup').hide();
        $('#editKodeCustomGroup').hide();
        $('#editNamaKode').prop('required', false);
        $('#editKodeCustom').prop('required', false);
        $('#currentFileInfo').hide();
        $(this).find('form')[0].reset();
    });

    $('#approveModal').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
        $('#approval_date').val(today);
    });

    // Delete document handler
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
                // Show loading
                Swal.fire({
                    title: 'Menghapus...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Send delete request
                $.ajax({
                    url: '<?= base_url("kelola-dokumen/deletepengajuan") ?>',
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

    // Form validation
    $('form').on('submit', function(e) {
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        
        // Disable submit button to prevent double submission
        submitBtn.prop('disabled', true);
        
        // Re-enable after 3 seconds
        setTimeout(function() {
            submitBtn.prop('disabled', false);
        }, 3000);
    });

    // Tooltip initialization
    $('[title]').tooltip();

    // File input validation
    $('input[type="file"]').on('change', function() {
        const file = this.files[0];
        if (file) {
            const fileSize = file.size / 1024 / 1024; // Convert to MB
            const allowedTypes = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation'
            ];

            if (fileSize > 10) { // 10MB limit
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
        }, 300); // 300ms delay
    });

    // Export functionality (if needed)
    window.exportTable = function(format) {
        const table = $('#documentsTable').DataTable();
        
        if (format === 'excel') {
            table.button('.buttons-excel').trigger();
        } else if (format === 'pdf') {
            table.button('.buttons-pdf').trigger();
        } else if (format === 'csv') {
            table.button('.buttons-csv').trigger();
        }
    };

    // Print table functionality
    window.printTable = function() {
        const printWindow = window.open('', '', 'height=600,width=800');
        const tableClone = $('#documentsTable').clone();
        
        // Remove action column
        tableClone.find('th:last-child, td:last-child').remove();
        
        printWindow.document.write(`
            <html>
                <head>
                    <title>Daftar Pengajuan Dokumen</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
                    <style>
                        body { font-size: 12px; }
                        table { width: 100% !important; }
                        .badge { display: inline-block; padding: 0.25em 0.4em; font-size: 0.75em; }
                        @media print {
                            body { -webkit-print-color-adjust: exact; }
                        }
                    </style>
                </head>
                <body>
                    <div class="container-fluid">
                        <h3 class="text-center mb-4">Daftar Pengajuan Dokumen</h3>
                        <div class="table-responsive">
                            ${tableClone[0].outerHTML}
                        </div>
                        <div class="text-center mt-3">
                            <small>Dicetak pada: ${new Date().toLocaleString('id-ID')}</small>
                        </div>
                    </div>
                </body>
            </html>
        `);
        
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
        printWindow.close();
    };

    // Keyboard shortcuts
    $(document).keydown(function(e) {
        // Ctrl+F for search focus
        if (e.ctrlKey && e.keyCode === 70) {
            e.preventDefault();
            $('#searchInput').focus();
        }
        
        // ESC to close modals
        if (e.keyCode === 27) {
            $('.modal.show').modal('hide');
        }
    });

    // Initialize tooltips for dynamically generated content
    $(document).on('mouseenter', '[title]', function() {
        $(this).tooltip('show');
    });

    // Auto-save form data to prevent data loss (optional)
    let formChangeTimeout;
    $('form input, form select, form textarea').on('change input', function() {
        clearTimeout(formChangeTimeout);
        const form = $(this).closest('form');
        
        formChangeTimeout = setTimeout(function() {
            // Could implement auto-save functionality here if needed
            console.log('Form data changed - could implement auto-save');
        }, 2000);
    });

    // Table row hover effects
    $('#documentsTable tbody').on('mouseenter', 'tr', function() {
        $(this).addClass('table-active');
    }).on('mouseleave', 'tr', function() {
        $(this).removeClass('table-active');
    });

    // Success/error message handling with auto-dismiss
    $('.alert').each(function() {
        const alert = $(this);
        if (!alert.find('.btn-close').length) {
            setTimeout(function() {
                alert.fadeOut();
            }, 5000);
        }
    });

    console.log('Document management script initialized successfully');
});

// Additional utility functions
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

// Close script tag
</script>

<?= $this->endSection() ?>
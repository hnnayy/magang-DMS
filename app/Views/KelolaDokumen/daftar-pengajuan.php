<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pengajuan Dokumen</title>
    <link href="assets/css/daftar_pengajuan.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="px-4 py-3 w-90">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">
                    Daftar Pengajuan Dokumen
                </h4>
            </div>

            <!-- Flash Messages -->
            <div id="flashMessages">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    Dokumen berhasil disetujui dan dipindahkan ke daftar dokumen.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" placeholder="Cari dokumen..." id="searchInput">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="filterFakultas">
                                <option value="">Semua Fakultas</option>
                                <option value="FT">Fakultas Teknik</option>
                                <option value="FE">Fakultas Ekonomi</option>
                                <option value="FMIPA">Fakultas MIPA</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="filterJenis">
                                <option value="">Semua Jenis</option>
                                <option value="internal">Internal</option>
                                <option value="eksternal">Eksternal</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-outline-secondary w-100" onclick="resetFilters()">
                                <i class="bi bi-arrow-counterclockwise"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documents Table -->
            <div class="table-responsive shadow-sm rounded bg-white p-3">
                <table class="table table-bordered table-hover align-middle" id="documentsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">No</th>
                            <th>Fakultas</th>
                            <th>Bagian</th>
                            <th>Nama Dokumen</th>
                            <th>No Dokumen</th>
                            <th class="text-center">Revisi</th>
                            <th>Jenis</th>
                            <th>Kode & Nama</th>
                            <th>File</th>
                            <th>Keterangan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Sample Data -->
                        <tr>
                            <td class="text-center">1</td>
                            <td>Fakultas Teknik</td>
                            <td>Teknik Informatika</td>
                            <td>Prosedur Operasional Standar Laboratorium</td>
                            <td>POS-TI-001</td>
                            <td class="text-center">Rev. 2</td>
                            <td><span class="badge bg-primary">Internal</span></td>
                            <td>LAB-001 - Prosedur Lab Komputer</td>
                            <td><i class="bi bi-file-pdf text-danger"></i> dokumen.pdf</td>
                            <td>Dokumen SOP untuk laboratorium komputer</td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <button class="btn btn-sm btn-outline-primary edit-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editModal" 
                                            data-id="1"
                                            data-fakultas="Fakultas Teknik"
                                            data-bagian="Teknik Informatika"
                                            data-nama="Prosedur Operasional Standar Laboratorium"
                                            data-nomor="POS-TI-001"
                                            data-revisi="Rev. 2"
                                            data-jenis="internal"
                                            data-kode="LAB-001 - Prosedur Lab Komputer"
                                            data-keterangan="Dokumen SOP untuk laboratorium komputer"
                                            title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-success approve-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#approveModal"
                                            data-id="1"
                                            title="Approve">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" 
                                            title="Hapus" 
                                            onclick="deleteDocument(1)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-center">2</td>
                            <td>Fakultas Ekonomi</td>
                            <td>Manajemen</td>
                            <td>Standar Operasional Prosedur Keuangan</td>
                            <td>SOP-FE-002</td>
                            <td class="text-center">Rev. 1</td>
                            <td><span class="badge bg-success">Eksternal</span></td>
                            <td>FIN-002 - Prosedur Keuangan</td>
                            <td><i class="bi bi-file-word text-primary"></i> dokumen.docx</td>
                            <td>SOP untuk pengelolaan keuangan fakultas</td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <button class="btn btn-sm btn-outline-primary edit-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editModal" 
                                            data-id="2"
                                            data-fakultas="Fakultas Ekonomi"
                                            data-bagian="Manajemen"
                                            data-nama="Standar Operasional Prosedur Keuangan"
                                            data-nomor="SOP-FE-002"
                                            data-revisi="Rev. 1"
                                            data-jenis="eksternal"
                                            data-kode="FIN-002 - Prosedur Keuangan"
                                            data-keterangan="SOP untuk pengelolaan keuangan fakultas"
                                            title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-success approve-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#approveModal"
                                            data-id="2"
                                            title="Approve">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" 
                                            title="Hapus" 
                                            onclick="deleteDocument(2)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Menampilkan 1-10 dari 15 dokumen
                </div>
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1">Previous</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Modal Edit Dokumen -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <form id="editDocumentForm" onsubmit="handleEditSubmit(event)">
                    <input type="hidden" name="document_id" id="editDocumentId">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">
                            <i class="bi bi-pencil-square me-2"></i>Edit Dokumen
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Fakultas/Direktorat</label>
                                <input type="text" class="form-control" name="fakultas" id="editFakultas" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Bagian/Unit</label>
                                <input type="text" class="form-control" name="bagian" id="editBagian" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nama Dokumen</label>
                                <input type="text" class="form-control" name="nama" id="editNama" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nomor Dokumen</label>
                                <input type="text" class="form-control" name="nomor" id="editNomor" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Revisi</label>
                                <input type="text" class="form-control" name="revisi" id="editRevisi" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Jenis Dokumen</label>
                                <select class="form-select" name="jenis" id="editJenis" required>
                                    <option value="">-- Pilih Jenis --</option>
                                    <option value="internal">Internal</option>
                                    <option value="eksternal">Eksternal</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kode & Nama Dokumen</label>
                                <input type="text" class="form-control" name="kode_nama" id="editKode" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Keterangan</label>
                                <textarea class="form-control" name="keterangan" id="editKeterangan" rows="3"></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Unggah File (Opsional)</label>
                                <input type="file" class="form-control" name="file" id="editFile" accept=".pdf,.doc,.docx">
                                <div class="form-text">File yang didukung: PDF, DOC, DOCX. Kosongkan jika tidak ingin mengubah file.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-2"></i>Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Approve Dokumen -->
    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-sm">
                <form onsubmit="handleApproveSubmit(event)">
                    <input type="hidden" name="document_id" id="approveDocumentId">

                    <!-- Header -->
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title fw-bold" id="approveModalLabel">Persetujuan Dokumen</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="row g-4">

                            <!-- Approver Info -->
                            <div class="col-md-6">
                                <div class="card border-0 bg-white h-100">
                                    <div class="card-body">
                                        <h6 class="fw-semibold mb-3">Informasi Approver</h6>
                                        <div class="mb-3">
                                            <label for="approved_by" class="form-label">Nama Pihak yang Menyetujui</label>
                                            <input type="text" class="form-control" name="approved_by" id="approved_by" placeholder="Masukkan nama lengkap" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="approval_date" class="form-label">Tanggal Persetujuan</label>
                                            <input type="date" class="form-control" name="approval_date" id="approval_date" value="2025-07-04" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ISO Standards -->
                            <div class="col-md-6">
                                <div class="card border-0 bg-white h-100">
                                    <div class="card-body">
                                        <h6 class="fw-semibold mb-3">Standar ISO</h6>
                                        <div class="iso-standards-grid">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="iso_standards[]" value="ISO 9001" id="iso9001">
                                                <label class="form-check-label" for="iso9001">
                                                    ISO 9001 - Sistem Manajemen Mutu
                                                </label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="iso_standards[]" value="ISO 14001" id="iso14001">
                                                <label class="form-check-label" for="iso14001">
                                                    ISO 14001 - Manajemen Lingkungan
                                                </label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="iso_standards[]" value="ISO 45001" id="iso45001">
                                                <label class="form-check-label" for="iso45001">
                                                    ISO 45001 - Kesehatan & Keselamatan Kerja
                                                </label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="iso_standards[]" value="ISO 27001" id="iso27001">
                                                <label class="form-check-label" for="iso27001">
                                                    ISO 27001 - Keamanan Informasi
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Clauses -->
                            <div class="col-md-12">
                                <div class="card border-0 bg-white">
                                    <div class="card-body">
                                        <h6 class="fw-semibold mb-3">Klausul Terkait</h6>
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label">ISO 9001</label>
                                                <select class="form-select" name="clause_9001[]" multiple>
                                                    <option value="4.1">4.1 Konteks Organisasi</option>
                                                    <option value="5.1">5.1 Kepemimpinan</option>
                                                    <option value="6.1">6.1 Risiko & Peluang</option>
                                                    <option value="8.5">8.5 Produksi</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">ISO 14001</label>
                                                <select class="form-select" name="clause_14001[]" multiple>
                                                    <option value="6.1">6.1 Aspek Lingkungan</option>
                                                    <option value="7.2">7.2 Kompetensi</option>
                                                    <option value="8.1">8.1 Operasional</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">ISO 45001</label>
                                                <select class="form-select" name="clause_45001[]" multiple>
                                                    <option value="5.4">5.4 Konsultasi</option>
                                                    <option value="6.2">6.2 Tujuan</option>
                                                    <option value="8.1">8.1 Operasional</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">ISO 27001</label>
                                                <select class="form-select" name="clause_27001[]" multiple>
                                                    <option value="6.1.3">6.1.3 Risiko Keamanan</option>
                                                    <option value="7.2.2">7.2.2 Kesadaran</option>
                                                    <option value="A.9">A.9 Kontrol Akses</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Remarks -->
                            <div class="col-12">
                                <div class="card border-0 bg-white">
                                    <div class="card-body">
                                        <h6 class="fw-semibold mb-3">Catatan Tambahan</h6>
                                        <textarea class="form-control" name="remarks" id="remarks" rows="4" placeholder="Masukkan catatan tambahan jika ada..."></textarea>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="modal-footer border-top">
                        <button type="submit" class="btn btn-primary">Setujui Dokumen</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/daftar_pengajuan.js"></script>
</body>
</html>

<?= $this->endSection() ?>


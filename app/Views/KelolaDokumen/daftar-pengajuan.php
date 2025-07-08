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
                        <div class="row w-100">
                            <div class="col-6 pe-2">
                    <button type="button" class="btn w-100 text-white" style="background-color: #b41616;" data-bs-dismiss="modal">
                        Batal
                    </button>
                </div>

                            <div class="col-6 ps-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-check-lg me-2"></i>Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </div>


                </form>
            </div>
        </div>
    </div>

    <!-- Modal Approve Dokumen -->
    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-sm">
                <form onsubmit="handleApproveSubmit(event)" class="p-3">
                    <input type="hidden" name="document_id" id="approveDocumentId">

                    <!-- Header -->
                    <div class="modal-header border-bottom-0 pb-2">
                        <h5 class="modal-title fw-bold" id="approveModalLabel">Persetujuan Dokumen</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    <!-- Body -->
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="approved_by" class="form-label">Nama Pihak yang Menyetujui</label>
                            <input type="text" class="form-control" name="approved_by" id="approved_by" placeholder="Masukkan nama lengkap" required>
                        </div>
                        <div class="mb-3">
                            <label for="approval_date" class="form-label">Tanggal Persetujuan</label>
                            <input type="date" class="form-control" name="approval_date" id="approval_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="remarks" class="form-label">Catatan Tambahan</label>
                            <textarea class="form-control" name="remarks" id="remarks" rows="3" placeholder="Masukkan catatan tambahan jika ada..."></textarea>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="modal-footer border-top-0 pt-0">
                        <div class="row w-100">
                            <div class="col-6 pe-1">
                                <button type="button" class="btn w-100 text-white" style="background-color: #b41616;" data-bs-dismiss="modal">
                                    Not Approve
                                </button>
                            </div>
                            <div class="col-6 ps-1">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-check-lg me-2"></i>Approve
                                </button>
                            </div>
                        </div>
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


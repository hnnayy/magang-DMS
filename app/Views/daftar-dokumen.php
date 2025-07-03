<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Filter Section -->
    <div class="filter-section">
        <div class="row align-items-end">
            <div class="col-12 col-md-2">
                <label class="form-label fw-semibold">Filter Data</label>
            </div>
            <div class="col-6 col-md-2">
                <select class="form-select form-select-sm">
                    <option>Semua Standar</option>
                    <option>8.4</option>
                    <option>7.1</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select class="form-select form-select-sm">
                    <option>Semua Klausul</option>
                    <option>8.2.2</option>
                    <option>9.1</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select class="form-select form-select-sm">
                    <option>Semua pemilik doc</option>
                    <option>Layanan CENTER OF E-learning</option>
                    <option>Bagian layanan Center</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select class="form-select form-select-sm">
                    <option>Semua jenis doc</option>
                    <option>Prosedur</option>
                    <option>Instruksi Kerja</option>
                    <option>Dokumen Internal</option>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <button class="btn btn-primary btn-sm w-100">Filter</button>
            </div>
        </div>
    </div>

    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h4 class="mb-1">Daftar Dokumen</h4>
        </div>
        <div class="header-controls">
            <a href="#" class="excel-button">
                <i class="bi bi-file-earmark-excel"></i>
                Export to Excel
            </a>
            <div class="search-container">
                <input type="text" class="form-control form-control-sm" placeholder="Search" style="padding-right: 35px; width: 200px;">
                <i class="bi bi-search search-icon"></i>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Standar</th>
                        <th>Klausul</th>
                        <th>Jenis Dokumen</th>
                        <th>Nama Dokumen</th>
                        <th>Pemilik Dokumen</th>
                        <th>File Dokumen</th>
                        <th>Revisi Dokumen</th>
                        <th>Tanggal Efektif</th>
                        <th>Last Update</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>8.4</td>
                        <td>7.1/ 8.2.2, 9.1</td>
                        <td>Prosedur</td>
                        <td>
                            <div class="document-code">Dokumen ISO 2000-1-2016</div>
                            <div class="document-title">Prosedur Kebertungutan Layanan LMS</div>
                            <div class="document-code">Dokumen ISO 210813816</div>
                        </td>
                        <td><a href="#" class="owner-link">Layanan CENTER OF E-learning of Open Education Yan CeLOE</a></td>
                        <td>
                            <button class="action-btn download-btn" title="Download"><i class="bi bi-download"></i></button>
                        </td>
                        <td><span class="revision-badge">00</span></td>
                        <td>22-Jun-2022</td>
                        <td>
                            <div>15-Aug-2024</div>
                            <div class="text-muted" style="font-size: 0.75rem;">09:08:24</div>
                        </td>
                        <td>
                            <div class="d-flex">
                                <button class="action-btn edit-btn" title="Edit"><i class="bi bi-pencil"></i></button>
                                <button class="action-btn delete-btn" title="Delete"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    <!-- Tambah baris dokumen lain sesuai kebutuhan -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center pt-3 mt-3" style="border-top: 1px solid #dee2e6; background-color: #f8f9fa; padding: 15px; border-radius: 0 0 8px 8px;">
        <div><small class="text-muted">Showing 1 to 3 entries</small></div>
        <nav aria-label="Page navigation">
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item disabled"><span class="page-link">Previous</span></li>
                <li class="page-item active"><span class="page-link">1</span></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item"><a class="page-link" href="#">Next</a></li>
            </ul>
        </nav>
    </div>
</div>

<!-- Optional JS -->
<script>
    document.querySelector('.btn-primary').addEventListener('click', function() {
        alert('Filter functionality would be implemented here');
    });

    document.querySelectorAll('.download-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            alert('Download functionality would be implemented here');
        });
    });

    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            alert('Edit functionality would be implemented here');
        });
    });

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this document?')) {
                alert('Delete functionality would be implemented here');
            }
        });
    });

    document.querySelector('.search-container input').addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            alert('Search functionality would be implemented here');
        }
    });
</script>

<?= $this->endSection() ?>

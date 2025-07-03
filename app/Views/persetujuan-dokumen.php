<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="px-4 py-3 w-100">
    <h4>Persetujuan Dokumen</h4>
    <hr>

    <!-- Export Buttons and Search Section in one row -->
    <div class="row mb-3 align-items-center">
        <div class="col-md-6">
            <div class="export-buttons">
                <button class="btn btn-purple border me-2">Copy</button>
                <button class="btn btn-purple border me-2">CSV</button>
                <button class="btn btn-purple border me-2">Excel</button>
                <button class="btn btn-purple border me-2">PDF</button>
                <button class="btn btn-purple border">Print</button>
            </div>
        </div>
        <div class="col-md-6">
            <div class="input-group search-container">
                <input type="text" class="form-control search-input" id="searchInput" placeholder="Search">
                <button class="btn search-btn" type="button" id="searchBtn">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="table-responsive shadow-sm rounded bg-white p-3">
        <table class="table table-bordered table-hover align-middle" id="documentTable">
            <thead class="table-light">
                <tr>
                    <th class="text-center" style="width: 5%;">No</th>
                    <th style="width: 12%;">Fakultas/ Direktorat</th>
                    <th style="width: 15%;">Bagian/Unit/ Program Studi</th>
                    <th style="width: 20%;">Nama Dokumen</th>
                    <th class="text-center" style="width: 8%;">Revisi</th>
                    <th style="width: 12%;">Jenis Dokumen</th>
                    <th style="width: 25%;">Kode dan Nama Dokumen</th>
                    <th style="width: 12%;">File Dokumen</th>
                    <th style="width: 8%;">Keterangan</th>
                    <th class="text-center" style="width: 5%;">Aksi</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <?php 
                $sampleData = [
                    ['FSAL', 'Yan CeLOE', 'Prosedur perubahan data', '00', 'Intruksi kerja dan prosedur',  'IK', 'Perubahan Data', 'file.pdf', 'Keterangan 1'],
                    ['FSAL', 'Yan CeLOE', 'Prosedur perubahan data', '00', 'Intruksi kerja dan prosedur',  'IK', 'Revisi SOP', 'file.pdf', 'Keterangan 2'],
                    ['FSAL', 'Yan CeLOE', 'Prosedur perubahan data', '00', 'Intruksi kerja dan prosedur', 'IK', 'Validasi Data', 'file.pdf', 'Keterangan 3'],
                    ['FSAL', 'Yan CeLOE', 'Prosedur perubahan data', '00', 'Intruksi kerja dan prosedur',  'IK', 'Formulir Baru', 'file.pdf', 'Keterangan 4'],
                ];
                
                foreach ($sampleData as $i => $data):
                ?>
                <tr>
                    <td class="text-center"><?= $i + 1 ?></td>
                    <td><?= $data[0] ?></td>
                    <td><?= $data[1] ?></td>
                    <td><?= $data[2] ?></td>
                    <td class="text-center"><?= $data[3] ?></td>
                    <td><?= $data[4] ?></td>
                    <td><?= $data[5] . ' - ' . $data[6] ?></td>
                    <td><?= $data[7] ?></td>
                    <td><?= $data[8] ?></td>
                    <td>
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <!-- Tombol Delete -->
                            <a href="#" class="text-danger" title="Delete">
                                <i class="bi bi-trash"></i>
                            </a>

                            <!-- Tombol Edit (sesuai route) -->
                            <a href="<?= base_url('dokumen/edit') ?>" class="text-primary" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </a>

                            <!-- Tombol Approve -->
                            <a href="#" class="text-success" title="Approve">
                                <i class="bi bi-check-lg"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- No Results Message -->
        <div id="noResults" class="text-center py-4" style="display: none;">
            <i class="bi bi-search" style="font-size: 3rem; color: #6c757d;"></i>
            <h5 class="mt-3 text-muted">No results found</h5>
            <p class="text-muted">Try adjusting your search criteria</p>
        </div>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div id="entriesInfo">Showing <span id="entriesStart">1</span> to <span id="entriesEnd">4</span> of <span id="entriesTotal">4</span> entries</div>
        <nav>
            <ul class="pagination mb-0">
                <li class="page-item"><a class="page-link" href="#">Previous</a></li>
                <?php for ($p = 1; $p <= 1; $p++): ?>
                    <li class="page-item active"><a class="page-link" href="#"><?= $p ?></a></li>
                <?php endfor; ?>
                <li class="page-item"><a class="page-link" href="#">Next</a></li>
            </ul>
        </nav>
    </div>
</div>


<?= $this->endSection() ?>

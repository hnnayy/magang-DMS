<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="px-4 py-3 w-100">
    <h4>Create User</h4>
    <p>Lihat User</p>

    <!-- Search Section -->
    <div class="row mb-3">
        <div class="col-md-6">
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

    <!-- Export Buttons -->
    <div class="mb-2">
        <button class="btn btn-purple border">Copy</button>
        <button class="btn btn-purple border">CSV</button>
        <button class="btn btn-purple border">Excel</button>
        <button class="btn btn-purple border">PDF</button>
        <button class="btn btn-purple border">Print</button>
    </div>

    <!-- Table Section -->
    <div class="table-responsive shadow-sm rounded bg-white p-3">
        <table class="table table-bordered table-hover align-middle" id="userTable">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Employee ID</th>
                    <th>Directorate</th>
                    <th>Unit</th>
                    <th>Fullname</th>
                    <th>Jabatan</th>
                    <th>Institusi</th>
                    <th>Tipe</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <?php 
                $sampleData = [
                    ['20443847284', 'Teknik Elektro', 'S3 Teknik Elektro', 'Saputra Maymuna', 'UGIDS5', 'Telkom University', 'Eksternal'],
                    ['20443847285', 'Teknik Informatika', 'S1 Teknik Informatika', 'Ahmad Rizky', 'UGIDS3', 'Telkom University', 'Internal'],
                    ['20443847286', 'Manajemen', 'S2 Manajemen', 'Siti Nurhaliza', 'UGIDS4', 'Telkom University', 'Eksternal'],
                    ['20443847287', 'Teknik Industri', 'S1 Teknik Industri', 'Budi Santoso', 'UGIDS2', 'Telkom University', 'Internal'],
                    ['20443847288', 'Komunikasi', 'S1 Komunikasi', 'Dewi Sartika', 'UGIDS3', 'Telkom University', 'Eksternal'],
                    ['20443847289', 'Teknik Elektro', 'S2 Teknik Elektro', 'Rudi Hermawan', 'UGIDS4', 'Telkom University', 'Internal'],
                    ['20443847290', 'Desain Komunikasi Visual', 'S1 DKV', 'Maya Sari', 'UGIDS2', 'Telkom University', 'Eksternal'],
                    ['20443847291', 'Sistem Informasi', 'S1 Sistem Informasi', 'Andi Pratama', 'UGIDS3', 'Telkom University', 'Internal'],
                    ['20443847292', 'Teknik Komputer', 'S1 Teknik Komputer', 'Lina Marlina', 'UGIDS4', 'Telkom University', 'Eksternal'],
                    ['20443847293', 'Akuntansi', 'S1 Akuntansi', 'Fahmi Abdullah', 'UGIDS2', 'Telkom University', 'Internal']
                ];
                
                for ($i = 0; $i < count($sampleData); $i++): 
                    $data = $sampleData[$i];
                ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= $data[0] ?></td>
                    <td><?= $data[1] ?></td>
                    <td><?= $data[2] ?></td>
                    <td><?= $data[3] ?></td>
                    <td><?= $data[4] ?></td>
                    <td><?= $data[5] ?></td>
                    <td><?= $data[6] ?></td>
                    <td>
                        <a href="#" class="text-primary me-2" title="Delete"><i class="bi bi-trash"></i></a>
                        <a href="#" class="text-primary me-2" title="Edit"><i class="bi bi-pencil-square"></i></a>
                        <a href="#" class="text-primary me-2" title="Approve"><i class="bi bi-check-lg"></i></a>
                        <a href="#" class="text-primary" title="Not Approve"><i class="bi bi-x-lg"></i></a>
                    </td>
                </tr>
                <?php endfor; ?>
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
        <div id="entriesInfo">Showing <span id="entriesStart">1</span> to <span id="entriesEnd">10</span> of <span id="entriesTotal">10</span> entries</div>
        <nav>
            <ul class="pagination mb-0">
                <li class="page-item"><a class="page-link" href="#">Previous</a></li>
                <?php for ($p = 1; $p <= 5; $p++): ?>
                    <li class="page-item <?= $p == 1 ? 'active' : '' ?>">
                        <a class="page-link" href="#"><?= $p ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item"><a class="page-link" href="#">Next</a></li>
            </ul>
        </nav>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    const tableBody = document.getElementById('tableBody');
    const noResults = document.getElementById('noResults');
    const entriesStart = document.getElementById('entriesStart');
    const entriesEnd = document.getElementById('entriesEnd');
    const entriesTotal = document.getElementById('entriesTotal');

    let allRows = Array.from(tableBody.querySelectorAll('tr'));
    let filteredRows = [...allRows];

    function performSearch() {
        const searchTerm = searchInput.value.toLowerCase().trim();

        filteredRows = allRows.filter(row => {
            const cells = row.querySelectorAll('td');
            const employeeId = cells[1].textContent.toLowerCase();
            const directorate = cells[2].textContent.toLowerCase();
            const unit = cells[3].textContent.toLowerCase();
            const fullname = cells[4].textContent.toLowerCase();
            const jabatan = cells[5].textContent.toLowerCase();
            const institusi = cells[6].textContent.toLowerCase();

            // Search filter
            return searchTerm === '' || 
                employeeId.includes(searchTerm) ||
                directorate.includes(searchTerm) ||
                unit.includes(searchTerm) ||
                fullname.includes(searchTerm) ||
                jabatan.includes(searchTerm) ||
                institusi.includes(searchTerm);
        });

        displayResults();
    }

    function displayResults() {
        // Hide all rows first
        allRows.forEach(row => row.style.display = 'none');

        if (filteredRows.length === 0) {
            noResults.style.display = 'block';
            tableBody.parentElement.style.display = 'none';
        } else {
            noResults.style.display = 'none';
            tableBody.parentElement.style.display = 'table';
            
            // Show filtered rows and update row numbers
            filteredRows.forEach((row, index) => {
                row.style.display = '';
                row.cells[0].textContent = index + 1;
            });
        }

        // Update entries info
        const total = filteredRows.length;
        entriesStart.textContent = total > 0 ? '1' : '0';
        entriesEnd.textContent = total.toString();
        entriesTotal.textContent = total.toString();
    }

    function clearSearch() {
        searchInput.value = '';
        filteredRows = [...allRows];
        displayResults();
        searchInput.focus();
    }

    // Event listeners
    searchInput.addEventListener('input', performSearch);
    searchBtn.addEventListener('click', performSearch);

    // Enter key support
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            performSearch();
        }
    });

    // Initialize
    displayResults();
});
</script>



<?= $this->endSection() ?>
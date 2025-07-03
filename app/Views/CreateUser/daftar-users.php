<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="px-4 py-3 w-100">
    <h4>Lihat User</h4>
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
        <table class="table table-bordered table-hover align-middle" id="userTable">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Employee ID</th>
                    <th>Directorate</th>
                    <th>Unit</th>
                    <th>Fullname</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>

           <tbody id="tableBody">
    <?php $i = 1; foreach ($users as $user): ?>
    <tr>
        <td><?= $i++ ?></td>
        <td><?= $user['id'] ?></td> <!-- Employee ID -->
        <td><?= esc($user['parent_name']) ?></td> <!-- Directorate -->
        <td><?= esc($user['unit_name']) ?></td> <!-- Unit -->
        <td><?= esc($user['fullname']) ?></td> <!-- Full Name -->
        <td><?= esc($user['role_name'] ?? 'N/A') ?></td> <!-- Role -->
        <td>
            <a href="#" class="text-primary me-2" title="Delete"><i class="bi bi-trash"></i></a>
            <a href="#" class="text-primary me-2" title="Edit"><i class="bi bi-pencil-square"></i></a>
            <a href="#" class="text-primary me-2" title="Approve"><i class="bi bi-check-lg"></i></a>
            <a href="#" class="text-primary" title="Not Approve"><i class="bi bi-x-lg"></i></a>
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
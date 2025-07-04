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
                    <th>Direktorat</th>
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
        <td><?= esc($user['id']) ?></td> <!-- Employee ID -->
        <td><?= esc($user['parent_name']) ?></td> <!-- Directorate -->
        <td><?= esc($user['unit_name']) ?></td> <!-- Unit -->
        <td><?= esc($user['fullname']) ?></td> <!-- Full Name -->
        <td><?= esc($user['role_name'] ?? 'N/A') ?></td> <!-- Role -->
        <td>
            <a href="#" class="text-primary me-2" title="Delete"><i class="bi bi-trash"></i></a>
            <a href="#" class="text-primary edit-user" 
                data-bs-toggle="modal" 
                data-bs-target="#editUserModal"
                data-id="<?= $user['id'] ?>"
                data-employee="<?= esc($user['id']) ?>"
                data-directorate="<?= esc($user['parent_name']) ?>"
                data-unit="<?= esc($user['unit_name']) ?>"
                data-fullname="<?= esc($user['fullname']) ?>"
                data-role="<?= esc($user['role_name'] ?? '') ?>">
                <i class="bi bi-pencil-square"></i>
            </a>

        </td>
    </tr>
    <?php endforeach; ?>

    <!-- DataTables core -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

    <!-- Buttons Extension -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

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

            // Search filter
            return searchTerm === '' || 
                employeeId.includes(searchTerm) ||
                directorate.includes(searchTerm) ||
                unit.includes(searchTerm) ||
                fullname.includes(searchTerm) ||
                jabatan.includes(searchTerm);

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

<script>
$(document).ready(function () {
    const table = $('#userTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'copy',
                className: 'btn-copy-dt',
                title: 'Data User',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
            },
            {
                extend: 'csv',
                className: 'btn-csv-dt',
                title: 'Data_User',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
            },
            {
                extend: 'excel',
                className: 'btn-excel-dt',
                title: 'Data_User',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
            },
            {
                extend: 'pdfHtml5',
                className: 'btn-pdf-dt',
                title: 'Data User', // <- untuk nama file, bukan isi PDF
                exportOptions: { columns: [0, 1, 2, 3, 4, 5] },
                customize: function (doc) {
                const now = new Date();
                const waktuCetak = now.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true,
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                });

                // Cek apakah item pertama adalah string title dan hapus jika perlu
                if (typeof doc.content[0].text === 'string' && doc.content[0].text === 'Data User') {
                    doc.content.splice(0, 1);
                }

                // Tambahkan logo & judul custom
                doc.content.splice(0, 0,
                    {
                        text: 'Data User',
                        alignment: 'center',
                        bold: true,
                        fontSize: 16,
                        margin: [0, 0, 0, 10]
                    }
                );

                // Style header tabel
                doc.styles.tableHeader = {
                    fillColor: '#ececec',
                    color: '#000000',
                    alignment: 'center',
                    bold: true,
                    fontSize: 10
                };

                // Style baris
                doc.styles.tableBodyEven = { fillColor: '#ffffff' };
                doc.styles.tableBodyOdd = { fillColor: '#ffffff' };

                // Footer
                doc.footer = function (currentPage, pageCount) {
                    return {
                        columns: [
                            { text: `${waktuCetak}`, alignment: 'left', margin: [30, 0] },
                            { text: '© 2025 Telkom University – Document Management System', alignment: 'center' },
                            { text: currentPage.toString() + '/' + pageCount, alignment: 'right', margin: [0, 0, 30] }
                        ],
                        fontSize: 9
                    };
                };

                // Atur garis & padding tabel
                doc.content[doc.content.length - 1].layout = {
                    hLineWidth: function () { return 0.5; },
                    vLineWidth: function () { return 0.5; },
                    hLineColor: function () { return '#000'; },
                    vLineColor: function () { return '#000'; },
                    paddingLeft: function () { return 4; },
                    paddingRight: function () { return 4; }
                };

                // Tambahkan catatan akhir
                doc.content.push({
                    text: '* Daftar Pengguna Sistem',
                    alignment: 'left',
                    italics: true,
                    fontSize: 9,
                    margin: [0, 10, 0, 0]
                });
            }
        },

            {
                extend: 'print',
                className: 'btn-print-dt',
                title: '',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5] },
                customize: function (win) {
                    const now = new Date();
                    const waktuCetak = now.toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    });
                    const tanggalCetak = now.toLocaleDateString('en-GB');

                    $(win.document.body)
                        .css('font-size', '12px')
                        .prepend(`
                            <h3 style="margin-bottom: 0;">Data User</h3>
                            <hr>
                        `);

                    $(win.document.body).append(
                        '<p style="font-style: italic; margin-top: 20px;">* Daftar Pengguna Sistem</p>'
                    );

                    $(win.document.body).append(`
                        <div style="position: fixed; bottom: 20px; width: 100%; text-align: center; font-size: 10px;">
                            © 2025 Telkom University – Document Management System
                        </div>
                    `);

                    // Styling tabel agar rapi
                    const table = $(win.document.body).find('table');
                    table.css('border-collapse', 'collapse');
                    table.css('width', '100%');
                    table.find('th, td').css({
                        'border': '1px solid #000',
                        'padding': '6px',
                        'text-align': 'left',
                        'vertical-align': 'top'
                    });
                }
            }

        ],
        paging: false,
        info: false,
        ordering: false,
        searching: false
    });

    $('.dt-buttons').hide();

    $('.export-buttons .btn:contains("Copy")').on('click', function () {
        $('.btn-copy-dt').click();
    });
    $('.export-buttons .btn:contains("CSV")').on('click', function () {
        $('.btn-csv-dt').click();
    });
    $('.export-buttons .btn:contains("Excel")').on('click', function () {
        $('.btn-excel-dt').click();
    });
    $('.export-buttons .btn:contains("PDF")').on('click', function () {
        $('.btn-pdf-dt').click();
    });
    $('.export-buttons .btn:contains("Print")').on('click', function () {
        $('.btn-print-dt').click();
    });
});

</script>


<?= $this->endSection() ?>
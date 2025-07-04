<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="px-4 py-3 w-100">
    <h4>Lihat Unit</h4>
    <hr>

    <!-- Export + Search -->
    <div class="row mb-3 align-items-center">
        <div class="col-md-6">
            <div class="export-buttons">
                <button class="btn btn-purple me-2" onclick="window.location='<?= site_url('data-master/export/csv') ?>'">CSV</button>
                <button class="btn btn-purple me-2" onclick="window.location='<?= site_url('data-master/export/excel') ?>'">Excel</button>
                <button class="btn btn-purple me-2" onclick="window.location='<?= site_url('data-master/export/pdf') ?>'">PDF</button>
                <button class="btn btn-purple"        onclick="window.open('<?= site_url('data-master/export/print') ?>','_blank')">Print</button>
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

    <!-- Table -->
    <div class="table-responsive shadow-sm rounded bg-white p-3">
        <table class="table table-bordered table-hover align-middle" id="documentTable">
            <thead class="table-light">
                <tr>
                    <th class="text-center" style="width: 5%;">No</th>
                    <th style="width: 30%;">Fakultas/Direktorat</th>
                    <th style="width: 45%;">Bagian/Unit/Prodi</th>
                    <th class="text-center" style="width: 20%;">Aksi</th>
                </tr>
            </thead>
            <tbody id="tableBody">
            <?php if (! empty($units)) : ?>
                <?php foreach ($units as $index => $row) : ?>
                <tr>
                    <td class="text-center"><?= $index + 1 ?></td>
                    <td><?= esc($row['parent_name']) ?></td>
                    <td><?= esc($row['name']) ?></td>

                    <!-- ───────  KOLOM AKSI  ─────── -->
                    <td class="text-center">
                        <div class="d-flex align-items-center justify-content-center gap-2">

                            <!-- DELETE (pakai SweetAlert) -->
                            <form action="<?= site_url('data-master/unit/' . $row['id'] . '/delete') ?>"
                                method="post" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-link p-0"
                                        onclick="SwalConfirmDelete(this)">
                                    <i class="bi bi-trash text-danger"></i>
                                </button>
                            </form>

                            <!-- EDIT (ikon dan href lama—biarkan) -->
                            <a href="<?= site_url('data-master/unit/' . $row['id'] . '/edit') ?>"
                            class="text-primary" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </a>

                            <!-- APPROVE: form selalu ada
                            <form action="<?= site_url('data-master/unit/' . $row['id'] . '/approve') ?>"
                                method="post" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-link p-0"
                                        title="Aktifkan">
                                    <i class="bi bi-check-lg text-success"></i>
                                </button>
                            </form> -->

                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr><td colspan="4" class="text-center">Belum ada data.</td></tr>
            <?php endif; ?>
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
    <!-- <div class="d-flex justify-content-between align-items-center mt-3">
        <div id="entriesInfo">Showing <span id="entriesStart">1</span> to <span id="entriesEnd">7</span> of <span id="entriesTotal">7</span> entries</div>
        <nav>
            <ul class="pagination mb-0">
                <li class="page-item"><a class="page-link" href="#">Previous</a></li>
                <?php for ($p = 1; $p <= 3; $p++): ?>
                    <li class="page-item <?= $p == 1 ? 'active' : '' ?>">
                        <a class="page-link" href="#"><?= $p ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item"><a class="page-link" href="#">Next</a></li>
            </ul>
        </nav>
    </div> -->
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
            const fakultas = row.cells[1].textContent.toLowerCase();
            const bagian = row.cells[2].textContent.toLowerCase();
            return fakultas.includes(searchTerm) || bagian.includes(searchTerm);
        });

        displayResults();
    }

    function displayResults() {
        allRows.forEach(row => row.style.display = 'none');

        if (filteredRows.length === 0) {
            noResults.style.display = 'block';
            tableBody.parentElement.style.display = 'none';
        } else {
            noResults.style.display = 'none';
            tableBody.parentElement.style.display = 'table';

            filteredRows.forEach((row, index) => {
                row.style.display = '';
                row.cells[0].textContent = index + 1;
            });
        }

        const total = filteredRows.length;
        entriesStart.textContent = total > 0 ? '1' : '0';
        entriesEnd.textContent = total.toString();
        entriesTotal.textContent = total.toString();
    }

    searchInput.addEventListener('input', performSearch);
    searchBtn.addEventListener('click', performSearch);
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            performSearch();
        }
    });

    displayResults();
});
</script>

<script>
function SwalConfirmDelete(elem) {
    event.preventDefault();                     // tahan klik asli
    Swal.fire({
        title: 'Hapus unit ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            elem.closest('form').submit();      // kirim form
        }
    });
}
</script>
<!-- ============================================================== -->
<!-- 1)  JQUERY + DATATABLES + BUTTONS CDN                          -->
<!-- ============================================================== -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<link  rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<link  rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<!-- untuk tombol Excel/CSV (opsional) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

<!-- ============================================================== -->
<!-- 2)  INISIALISASI DATATABLES + BUTTONS                          -->
<!-- ============================================================== -->
<!-- <script>
$(function () {
  $('#documentTable').DataTable({
      rtip: 'Bfrtip',                         // tombol di atas tabel
      searching: true,
      buttons: [
        { extend: 'copy',   title: 'Daftar Unit' },
        { extend: 'csv',   title: 'Daftar Unit' },
        { extend: 'excel', title: 'Daftar Unit' },
        { extend: 'print', title: 'Daftar Unit' }
      ],
      pageLength: 10,
      order: [],                             // tanpa urutan default
      columnDefs: [{ orderable:false, targets: 3 }] // kolom “Aksi” tidak bisa sort
  });
});
</script> -->

<script>
$(function () {

  const dt = $('#documentTable').DataTable({
    
      dom: 'lrtip',
      pageLength: 10,
      order: [],
      columnDefs: [{ orderable:false, targets: 3 }],
      buttons: [
        {
            extend:    'copyHtml5',          
            text:      'Copy',
            titleAttr: 'Salin',
            className: 'btn btn-purple me-2'  
        }
]
      
  });

  // pindahkan button copy ke div buatan
  dt.buttons().container().appendTo('.export-buttons');

  // search custom
  $('#searchInput').on('keyup', function () {
      dt.search(this.value).draw();
  });
  $('#searchBtn').on('click', function () {
      dt.search($('#searchInput').val()).draw();
  });

});
</script>


<style>
/* Tombol Buttons di area export-buttons */
.export-buttons .dt-button {
    /* hapus border & background bawaan */
    border: none !important;
    background: transparent !important;
    box-shadow: none !important;
    padding: 0 !important;      /* biar ikut padd­ing Bootstrap */
}

.export-buttons .dt-button.btn-purple {
    background-color: #7c6df5 !important; /* sama seperti btn-purple */
    color: #fff !important;
    padding: .375rem .75rem !important;   /* padding Bootstrap */
    border-radius: .25rem !important;
}

.export-buttons .dt-button.btn-purple:hover {
    background-color: #6b5af0 !important; /* hover */
}
</style>




<?= $this->endSection() ?>

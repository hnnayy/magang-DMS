function toggleSubmenu(element) {
    const parent = element.parentElement;
    const isCurrentlyOpen = parent.classList.contains('open');
    
    
    document.querySelectorAll('.has-submenu.open').forEach(item => {
        item.classList.remove('open');
    });
    
    if (!isCurrentlyOpen) {
        parent.classList.add('open');
    }
}

function toggleProfileMenu() {
    const dropdown = document.querySelector('.profile-dropdown');
    dropdown.classList.toggle('active');
}

// Close profile dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.querySelector('.profile-dropdown');
    if (!dropdown.contains(event.target)) {
        dropdown.classList.remove('active');
    }
});

// Close submenu when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('.has-submenu')) {
        document.querySelectorAll('.has-submenu.open').forEach(item => {
            item.classList.remove('open');
        });
    }
});

// Mobile menu toggle (untuk responsive)
function toggleMobileMenu() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('mobile-active');
}

// Add mobile menu button functionality if needed
document.addEventListener('DOMContentLoaded', function() {
    // Initialize any additional functionality here
    console.log('Dashboard loaded successfully');
});

function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');

    sidebar.classList.toggle('active');

    if (sidebar.classList.contains('active')) {
        overlay.style.display = 'block';
    } else {
        overlay.style.display = 'none';
    }
    }

// daftar pengajuan
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
            const values = Array.from(cells).map(cell => cell.textContent.toLowerCase());
            return searchTerm === '' || values.some(text => text.includes(searchTerm));
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

// <!-- ✅ JavaScript Dropdown Dinamis -->

 const dokumenInternal = [
    { kode: 'UAT', nama: 'User Acceptances Test' },
    { kode: 'PM', nama: 'Pedoman Mutu/Pedoman Lain' },
    { kode: 'SM', nama: 'Sasaran Mutu' },
    { kode: 'UM', nama: 'User Manual/Manual' },
    { kode: 'TW', nama: 'Laporan Triwulan' },
    { kode: 'MD', nama: 'Modul/Materi' },
    { kode: 'LP', nama: 'Laporan Lain' },
    { kode: 'AG', nama: 'Agreement/Perjanjian' },
    { kode: 'RA', nama: 'Rencana Kerja Anggaran' },
    { kode: 'DP', nama: 'Dokumen Proses Layanan IT' },
    { kode: 'PR', nama: 'Prosedur' },
    { kode: 'IK', nama: 'Instruksi Kerja' },
    { kode: 'FM', nama: 'Formulir tanpa Prosedur' },
    { kode: 'FMP', nama: 'Formulir Milik Prosedur' },
    { kode: 'FMI', nama: 'Formulir Milik Instruksi Kerja' }
  ];

  const dokumenEksternal = [
    { kode: 'DI', nama: 'Dokumen Internal' },
    { kode: 'DE', nama: 'Dokumen Eksternal' },
    { kode: 'KM', nama: 'Kebijakan Mutu' }
  ];

  function updateKodeOptions() {
    const jenis = document.getElementById("jenis-dokumen").value;
    const kodeSelect = document.getElementById("kode-dokumen");
    kodeSelect.innerHTML = '<option value="">-- Pilih Dokumen --</option>';

    const data = jenis === 'internal' ? dokumenInternal : dokumenEksternal;

    data.forEach(item => {
      const option = document.createElement("option");
      option.value = item.kode;
      option.text = `${item.kode} - ${item.nama}`;
      kodeSelect.appendChild(option);
    });
  }
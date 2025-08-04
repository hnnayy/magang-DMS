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

// Daftar prodi berdasarkan fakultas
    const prodiOptions = {
        FTE: [
            'Electrical Energy Engineering',
            'Teknik Biomedis',
            'Teknik Telekomunikasi',
            'Teknik Elektro',
            'Smart Science and Technology (Teknik Fisika)',
            'Teknik Komputer',
            'Teknik Pangan'
        ],
        FRI: [
            'Teknik Industri',
            'Sistem Informasi',
            'Digital Supply Chain',
            'Manajemen Rekayasa Industri'
        ],
        FIF: [
            'Informatika',
            'Rekayasa Perangkat Lunak',
            'Cybersecurity',
            'Teknologi Informasi',
            'Sains Data'
        ],
        FEB: [
            'Akuntansi',
            'Manajemen',
            'Leisure Management',
            'Administrasi Bisnis',
            'Digital Business'
        ],
        FKS: [
            'Ilmu Komunikasi',
            'Digital Public Relation',
            'Digital Content Broadcasting',
            'Psikologi (Digital Psychology)'
        ],
        FIK: [
            'Visual Arts',
            'Desain Komunikasi Visual',
            'Desain Produk & Inovasi',
            'Desain Interior',
            'Kriya (Fashion & Textile Design)',
            'Film dan Animasi'
        ],
        FIT: [
            'Ilmu Komunikasi',
            'Digital Public Relation',
            'Digital Content Broadcasting',
            'Psikologi (Digital Psychology)'
        ]
    };

    // Fungsi untuk memperbarui dropdown prodi saat fakultas berubah
    function updateProdi() {
        const fakultas = document.getElementById('fakultas').value;
        const prodiSelect = document.getElementById('prodi');

        // Reset dropdown prodi
        prodiSelect.innerHTML = '<option value="" disabled selected hidden>Please Select Program Study...</option>';

        // Tampilkan opsi prodi sesuai fakultas
        if (fakultas && prodiOptions[fakultas]) {
            prodiOptions[fakultas].forEach(function (prodi) {
                const option = document.createElement('option');
                option.value = prodi;
                option.textContent = prodi;
                prodiSelect.appendChild(option);
            });
        }
    }


//daftar pengajuan
document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('editDocumentId').value = btn.dataset.id;
                document.getElementById('editFakultas').value = btn.dataset.fakultas;
                document.getElementById('editBagian').value = btn.dataset.bagian;
                document.getElementById('editNama').value = btn.dataset.nama;
                document.getElementById('editNomor').value = btn.dataset.nomor;
                document.getElementById('editRevisi').value = btn.dataset.revisi;
                document.getElementById('editJenis').value = btn.dataset.jenis;
                document.getElementById('editKode').value = btn.dataset.kode;
                document.getElementById('editKeterangan').value = btn.dataset.keterangan;
            });
        });

        // Isi ID untuk approve
        document.querySelectorAll('.approve-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('approveDocumentId').value = btn.dataset.id;
            });
        });

        // Fungsi contoh untuk form submission
        function handleEditSubmit(e) {
            e.preventDefault();
            alert("Form edit disubmit!");
            // Implementasikan logic AJAX atau form POST di sini
        }

        function handleApproveSubmit(e) {
            e.preventDefault();
            alert("Dokumen disetujui!");
            // Implementasikan logic AJAX atau form POST di sini
        }

        function deleteDocument(id) {
            if (confirm("Yakin ingin menghapus dokumen ini?")) {
                alert("Dokumen dengan ID " + id + " dihapus.");
                // Implementasi penghapusan bisa dilakukan di sini
            }
        }



        function resetFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('filterFakultas').value = '';
            document.getElementById('filterJenis').value = '';
        }

//pusher

document.addEventListener('DOMContentLoaded', function () {
    const userId = document.querySelector('meta[name="user-id"]').getAttribute('content');
    const notifList = document.getElementById('notif-list');
    const notifCount = document.getElementById('notif-count');

    // Fungsi ambil notifikasi dari backend
    function loadNotifications() {
        fetch('/notifications/get')
            .then(response => response.json())
            .then(data => {
                notifList.innerHTML = '<li class="dropdown-header">Notifikasi</li>';
                let unread = 0;

                data.forEach(notif => {
                    const li = document.createElement('li');
                    li.innerHTML = `
                        <a href="${notif.link ?? '#'}" class="dropdown-item notif-item ${notif.is_read == 0 ? 'fw-bold' : ''}" data-id="${notif.id}">
                            ${notif.message}
                            <br><small class="text-muted">${notif.created_at}</small>
                        </a>`;
                    notifList.appendChild(li);

                    if (notif.is_read == 0) unread++;
                });

                notifCount.textContent = unread;
                notifCount.style.display = unread > 0 ? 'inline-block' : 'none';
            });
    }

    // Tandai sebagai dibaca saat klik
    notifList.addEventListener('click', function (e) {
        if (e.target.classList.contains('notif-item')) {
            const notifId = e.target.dataset.id;
            fetch(`/notifications/read/${notifId}`, { method: 'POST' })
                .then(() => loadNotifications());
        }
    });

    // Jalankan saat page load
    loadNotifications();

    // Setup Pusher untuk real-time
    const pusher = new Pusher(PUSHER_KEY, {
        cluster: PUSHER_CLUSTER
    });

    const channel = pusher.subscribe(`user-${userId}`);
    channel.bind('new-notification', function (data) {
        loadNotifications(); // refresh list
    });
});


//notif
// Tambahkan di file dashboard.js atau buat file notification.js terpisah

$(document).ready(function() {
    // Handle click pada notifikasi
    $(document).on('click', '.notification-link', function(e) {
        e.preventDefault();
        
        const notificationId = $(this).data('notification-id');
        const href = $(this).attr('href');
        
        // Mark as read via AJAX
        markNotificationAsRead(notificationId, function(success) {
            if (success) {
                // Remove notification from dropdown
                $(`.notification-item[data-notification-id="${notificationId}"]`).fadeOut();
                
                // Update notification count
                updateNotificationCount();
                
                // Redirect to the link
                window.location.href = href;
            } else {
                // Still redirect even if marking as read failed
                window.location.href = href;
            }
        });
    });
    
    // Auto refresh notifications every 30 seconds
    setInterval(function() {
        refreshNotifications();
    }, 30000);
});

/**
 * Mark notification as read
 */
function markNotificationAsRead(notificationId, callback) {
    $.ajax({
        url: BASE_URL + 'notification/markAsRead',
        type: 'POST',
        data: {
            notification_id: notificationId,
            [csrfToken]: csrfHash
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                console.log('Notification marked as read');
                callback(true);
            } else {
                console.error('Failed to mark notification as read:', response.message);
                callback(false);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX error marking notification as read:', error);
            callback(false);
        }
    });
}

/**
 * Update notification count badge
 */
function updateNotificationCount() {
    const remainingNotifications = $('.notification-item').not(':hidden').length - 1; // -1 for divider
    const badge = $('.notif-badge');
    
    if (remainingNotifications > 0) {
        badge.text(remainingNotifications).show();
    } else {
        badge.hide();
        // Update dropdown content to show no notifications
        $('#notif-list').html(`
            <li class="dropdown-header">Notifikasi</li>
            <li class="notification-item">
                <div class="dropdown-item text-muted text-center py-3">
                    <i class="bi bi-bell-slash mb-2" style="font-size: 2rem;"></i>
                    <div>Tidak ada notifikasi baru</div>
                </div>
            </li>
        `);
    }
}

/**
 * Refresh notifications without page reload
 */
function refreshNotifications() {
    $.ajax({
        url: BASE_URL + 'notification/fetch',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                updateNotificationDropdown(response.notifications, response.count);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error refreshing notifications:', error);
        }
    });
}

/**
 * Update notification dropdown content
 */
function updateNotificationDropdown(notifications, count) {
    let html = '<li class="dropdown-header">Notifikasi</li>';
    
    if (notifications && notifications.length > 0) {
        notifications.forEach(function(notif) {
            const creatorName = notif.creator_fullname || notif.creator_name || 'Pengguna Tidak Dikenal';
            const createdDate = new Date(notif.createddate).toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            
            html += `
                <li class="notification-item" data-notification-id="${notif.id}">
                    <a class="dropdown-item d-flex align-items-center notification-link" 
                       href="${BASE_URL}document-submission-list?reference_id=${notif.reference_id || ''}"
                       data-notification-id="${notif.id}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 50 50" class="me-2">
                            <path fill="#007bff" d="M 30.398438 2 L 7 2 L 7 48 L 43 48 L 43 14.601563 Z M 15 28 L 31 28 L 31 30 L 15 30 Z M 35 36 L 15 36 L 15 34 L 35 34 Z M 35 24 L 15 24 L 15 22 L 35 22 Z M 30 15 L 30 4.398438 L 40.601563 15 Z"></path>
                        </svg>
                        <div class="notification-content">
                            <div class="fw-bold">
                                ${creatorName}
                                ${notif.createdby ? `<small class="text-muted">(ID: ${notif.createdby})</small>` : ''}
                            </div>
                            <div class="notification-message">${notif.message}</div>
                            <small class="text-muted">${createdDate}</small>
                        </div>
                        <span class="ms-auto">
                            <i class="bi bi-circle-fill text-primary" style="font-size: 8px;" title="Belum dibaca"></i>
                        </span>
                    </a>
                </li>
            `;
        });
        
        html += `
            <li><hr class="dropdown-divider"></li>
            <li class="text-center">
                <a class="dropdown-item text-primary" href="${BASE_URL}notifications">
                    <small>Lihat semua notifikasi</small>
                </a>
            </li>
        `;
        
        // Update badge
        $('.notif-badge').text(count).show();
    } else {
        html += `
            <li class="notification-item">
                <div class="dropdown-item text-muted text-center py-3">
                    <i class="bi bi-bell-slash mb-2" style="font-size: 2rem;"></i>
                    <div>Tidak ada notifikasi baru</div>
                </div>
            </li>
        `;
        
        // Hide badge
        $('.notif-badge').hide();
    }
    
    $('#notif-list').html(html);
}

// Get CSRF token and hash from meta tags
const csrfToken = $('meta[name="csrf-token"]').attr('content');
let csrfHash = $('meta[name="csrf-hash"]').attr('content');

// Update CSRF hash after each AJAX request
$(document).ajaxComplete(function(event, xhr, settings) {
    const newHash = xhr.getResponseHeader('X-CSRF-TOKEN');
    if (newHash) {
        csrfHash = newHash;
        $('meta[name="csrf-hash"]').attr('content', newHash);
    }
});
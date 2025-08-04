// ===== SIDEBAR FUNCTIONALITY =====
function toggleSubmenu(element) {
    // Prevent event from bubbling up to document
    event.stopPropagation();
    
    const parent = element.parentElement;
    const isCurrentlyOpen = parent.classList.contains('open');
    
    // Close all other submenus
    document.querySelectorAll('.has-submenu.open').forEach(item => {
        if (item !== parent) {
            item.classList.remove('open');
        }
    });
    
    // Toggle current submenu
    if (!isCurrentlyOpen) {
        parent.classList.add('open');
    } else {
        parent.classList.remove('open');
    }
}

function toggleProfileMenu() {
    const dropdown = document.querySelector('.profile-dropdown');
    dropdown.classList.toggle('active');
}

// ===== RESPONSIVE SIDEBAR FUNCTIONS =====
function isSplitMode() {
    return window.innerWidth <= 992;
}

function toggleSidebar() {
    if (isSplitMode()) {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        
        if (sidebar.classList.contains('active')) {
            sidebar.classList.remove('active');
            overlay.style.display = 'none';
            document.body.style.overflow = 'auto';
        } else {
            sidebar.classList.add('active');
            overlay.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
    }
}

function closeSidebar() {
    if (isSplitMode()) {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        sidebar.classList.remove('active');
        overlay.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// ===== EVENT LISTENERS SETUP =====
document.addEventListener('DOMContentLoaded', function() {
    // Initialize sidebar toggle button
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }

    // Initialize sidebar overlay click to close
    const sidebarOverlay = document.querySelector('.sidebar-overlay');
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', closeSidebar);
    }

    console.log('Dashboard loaded successfully');
});

// ===== CLICK OUTSIDE HANDLERS =====
document.addEventListener('click', function(event) {
    // Handle profile dropdown close
    const profileDropdown = document.querySelector('.profile-dropdown');
    if (profileDropdown && !profileDropdown.contains(event.target)) {
        profileDropdown.classList.remove('active');
    }
    
    // Handle submenu close - ONLY if not clicking inside sidebar
    const sidebar = document.getElementById('sidebar');
    if (sidebar && !sidebar.contains(event.target)) {
        // Only close submenus if clicking outside sidebar entirely
        document.querySelectorAll('.has-submenu.open').forEach(item => {
            item.classList.remove('open');
        });
    }
    
    // Handle responsive sidebar close
    if (isSplitMode()) {
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        
        // Close sidebar if clicking outside AND not on toggle button
        if (sidebar && sidebar.classList.contains('active') && 
            !sidebar.contains(event.target) && 
            (!sidebarToggle || !sidebarToggle.contains(event.target))) {
            closeSidebar();
        }
    }
});

// ===== PREVENT SUBMENU LINKS FROM CLOSING PARENT =====
document.addEventListener('click', function(event) {
    // If clicking on a submenu link, don't close the submenu
    if (event.target.closest('.submenu a')) {
        event.stopPropagation();
    }
});

// ===== MOBILE MENU TOGGLE (Legacy support) =====
function toggleMobileMenu() {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.classList.toggle('mobile-active');
    }
}

// ===== SEARCH FUNCTIONALITY =====
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    const tableBody = document.getElementById('tableBody');
    const noResults = document.getElementById('noResults');
    const entriesStart = document.getElementById('entriesStart');
    const entriesEnd = document.getElementById('entriesEnd');
    const entriesTotal = document.getElementById('entriesTotal');
    
    if (searchInput && tableBody) {
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
                if (noResults) noResults.style.display = 'block';
                if (tableBody.parentElement) tableBody.parentElement.style.display = 'none';
            } else {
                if (noResults) noResults.style.display = 'none';
                if (tableBody.parentElement) tableBody.parentElement.style.display = 'table';
                filteredRows.forEach((row, index) => {
                    row.style.display = '';
                    if (row.cells[0]) row.cells[0].textContent = index + 1;
                });
            }

            const total = filteredRows.length;
            if (entriesStart) entriesStart.textContent = total > 0 ? '1' : '0';
            if (entriesEnd) entriesEnd.textContent = total.toString();
            if (entriesTotal) entriesTotal.textContent = total.toString();
        }

        searchInput.addEventListener('input', performSearch);
        if (searchBtn) searchBtn.addEventListener('click', performSearch);
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });

        displayResults();
    }
});

// ===== DOCUMENT MANAGEMENT =====
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
    const jenis = document.getElementById("jenis-dokumen");
    const kodeSelect = document.getElementById("kode-dokumen");
    
    if (jenis && kodeSelect) {
        kodeSelect.innerHTML = '<option value="">-- Pilih Dokumen --</option>';
        const data = jenis.value === 'internal' ? dokumenInternal : dokumenEksternal;
        data.forEach(item => {
            const option = document.createElement("option");
            option.value = item.kode;
            option.text = `${item.kode} - ${item.nama}`;
            kodeSelect.appendChild(option);
        });
    }
}

// ===== PROGRAM STUDY MANAGEMENT =====
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

function updateProdi() {
    const fakultas = document.getElementById('fakultas');
    const prodiSelect = document.getElementById('prodi');
    
    if (fakultas && prodiSelect) {
        prodiSelect.innerHTML = '<option value="" disabled selected hidden>Please Select Program Study...</option>';
        
        if (fakultas.value && prodiOptions[fakultas.value]) {
            prodiOptions[fakultas.value].forEach(function (prodi) {
                const option = document.createElement('option');
                option.value = prodi;
                option.textContent = prodi;
                prodiSelect.appendChild(option);
            });
        }
    }
}

// ===== DOCUMENT FORM HANDLERS =====
document.addEventListener('DOMContentLoaded', function() {
    // Edit button handlers
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const editElements = {
                'editDocumentId': btn.dataset.id,
                'editFakultas': btn.dataset.fakultas,
                'editBagian': btn.dataset.bagian,
                'editNama': btn.dataset.nama,
                'editNomor': btn.dataset.nomor,
                'editRevisi': btn.dataset.revisi,
                'editJenis': btn.dataset.jenis,
                'editKode': btn.dataset.kode,
                'editKeterangan': btn.dataset.keterangan
            };

            Object.keys(editElements).forEach(key => {
                const element = document.getElementById(key);
                if (element) element.value = editElements[key];
            });
        });
    });

    // Approve button handlers
    document.querySelectorAll('.approve-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const approveElement = document.getElementById('approveDocumentId');
            if (approveElement) approveElement.value = btn.dataset.id;
        });
    });
});

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
    const elements = ['searchInput', 'filterFakultas', 'filterJenis'];
    elements.forEach(id => {
        const element = document.getElementById(id);
        if (element) element.value = '';
    });
}

// ===== NOTIFICATION SYSTEM =====
document.addEventListener('DOMContentLoaded', function () {
    const userIdMeta = document.querySelector('meta[name="user-id"]');
    const notifList = document.getElementById('notif-list');
    const notifCount = document.getElementById('notif-count');
    
    if (userIdMeta && notifList && notifCount) {
        const userId = userIdMeta.getAttribute('content');
        
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
                })
                .catch(error => console.error('Error loading notifications:', error));
        }

        // Mark notification as read when clicked
        notifList.addEventListener('click', function (e) {
            if (e.target.classList.contains('notif-item')) {
                const notifId = e.target.dataset.id;
                fetch(`/notifications/read/${notifId}`, { method: 'POST' })
                    .then(() => loadNotifications())
                    .catch(error => console.error('Error marking notification as read:', error));
            }
        });

        loadNotifications();

        // Setup Pusher for real-time notifications (if available)
        if (typeof Pusher !== 'undefined' && typeof PUSHER_KEY !== 'undefined') {
            const pusher = new Pusher(PUSHER_KEY, {
                cluster: PUSHER_CLUSTER
            });
            const channel = pusher.subscribe(`user-${userId}`);
            channel.bind('new-notification', function (data) {
                loadNotifications();
            });
        }
    }
});

// ===== JQUERY-BASED NOTIFICATION HANDLERS =====
$(document).ready(function() {
    // Handle notification clicks
    $(document).on('click', '.notification-link', function(e) {
        e.preventDefault();
        
        const notificationId = $(this).data('notification-id');
        const href = $(this).attr('href');
        
        markNotificationAsRead(notificationId, function(success) {
            window.location.href = href;
        });
    });
    
    // Auto refresh notifications every 30 seconds
    setInterval(function() {
        refreshNotifications();
    }, 30000);
});

function markNotificationAsRead(notificationId, callback) {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    const csrfHash = $('meta[name="csrf-hash"]').attr('content');
    
    if (typeof $ !== 'undefined' && csrfToken && csrfHash) {
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
    } else {
        callback(false);
    }
}

function updateNotificationCount() {
    if (typeof $ !== 'undefined') {
        const remainingNotifications = $('.notification-item').not(':hidden').length - 1;
        const badge = $('.notif-badge');
        
        if (remainingNotifications > 0) {
            badge.text(remainingNotifications).show();
        } else {
            badge.hide();
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
}

function refreshNotifications() {
    if (typeof $ !== 'undefined' && typeof BASE_URL !== 'undefined') {
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
}

function updateNotificationDropdown(notifications, count) {
    if (typeof $ === 'undefined' || typeof BASE_URL === 'undefined') return;
    
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
        
        $('.notif-badge').hide();
    }
    
    $('#notif-list').html(html);
}

// ===== CSRF TOKEN MANAGEMENT =====
$(document).ready(function() {
    // Update CSRF hash after each AJAX request
    $(document).ajaxComplete(function(event, xhr, settings) {
        const newHash = xhr.getResponseHeader('X-CSRF-TOKEN');
        if (newHash) {
            $('meta[name="csrf-hash"]').attr('content', newHash);
        }
    });
});
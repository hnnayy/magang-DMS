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

        sidebar.classList.toggle('hidden');

        if (sidebar.classList.contains('hidden')) {
            overlay.style.display = 'none';
        } else {
            overlay.style.display = 'block';
        }
    }

    function toggleSubmenu(el) {
        const parent = el.closest('.has-submenu');
        parent.classList.toggle('open');
    }

    function toggleProfileMenu() {
        const dropdown = document.querySelector('.profile-dropdown');
        dropdown.classList.toggle('active');
    }
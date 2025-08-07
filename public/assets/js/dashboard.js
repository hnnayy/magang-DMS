function toggleSubmenu(element) {
    event.stopPropagation();
    const parent = element.parentElement;
    const isCurrentlyOpen = parent.classList.contains('open');
    
    document.querySelectorAll('.has-submenu.open').forEach(item => {
        if (item !== parent) {
            item.classList.remove('open');
        }
    });
    
    parent.classList.toggle('open');
}

function toggleProfileMenu() {
    const dropdown = document.querySelector('.profile-dropdown');
    dropdown.classList.toggle('active');
}

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

document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }

    const sidebarOverlay = document.querySelector('.sidebar-overlay');
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', closeSidebar);
    }

    console.log('Dashboard loaded successfully');
});

document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('sidebar');
    const notificationDropdown = document.getElementById('notificationDropdown');
    
    if (sidebar && !sidebar.contains(event.target) && !notificationDropdown.contains(event.target)) {
        document.querySelectorAll('.has-submenu.open').forEach(item => {
            item.classList.remove('open');
        });
    }
    
    if (isSplitMode()) {
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        if (sidebar && sidebar.classList.contains('active') && 
            !sidebar.contains(event.target) && 
            (!sidebarToggle || !sidebarToggle.contains(event.target)) && 
            !notificationDropdown.contains(event.target)) {
            closeSidebar();
        }
    }
    
    const profileDropdown = document.querySelector('.profile-dropdown');
    if (profileDropdown && profileDropdown.classList.contains('active') && 
        !profileDropdown.contains(event.target) && 
        !notificationDropdown.contains(event.target)) {
        profileDropdown.classList.remove('active');
    }
});

document.addEventListener('click', function(event) {
    if (event.target.closest('.submenu a')) {
        event.stopPropagation();
    }
});

function toggleMobileMenu() {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.classList.toggle('mobile-active');
    }
}
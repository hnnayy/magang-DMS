<div class="sidebar" id="sidebar">
    <div class="menu-container">
        <ul class="menu">
            <?php
            $menuList = getSidebarMenuByRole(); // otomatis ambil dari session
            ?>
            <?php foreach ($menuList as $menuName => $menuData): ?>
                <li class="has-submenu">
                    <div class="submenu-toggle" onclick="toggleSubmenu(this)">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <i class="<?= esc($menuData['icon']) ?>"></i>
                            <span><?= esc($menuName) ?></span>
                        </div>
                        <i class="fi fi-rr-angle-small-down arrow"></i>
                    </div>
                    <ul class="submenu">
                        <?php foreach ($menuData['submenus'] as $submenu): ?>
                            <li>
                                <a href="<?= base_url('/' . slugify($submenu['name'])) ?>" onclick="handleSubmenuClick(event)">
                                    <?= esc($submenu['name']) ?>
                                </a>
                            </li>
                        <?php endforeach ?>
                    </ul>
                </li>
            <?php endforeach ?>
        </ul>
    </div>
</div>

<script>
// ===== HELPER FUNCTIONS =====
function isSplitMode() {
    return window.innerWidth <= 992;
}

function toggleSidebar() {
    if (isSplitMode()) {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        
        if (sidebar.classList.contains('active')) {
            sidebar.classList.remove('active');
            if (overlay) overlay.style.display = 'none';
            document.body.style.overflow = 'auto';
        } else {
            sidebar.classList.add('active');
            if (overlay) overlay.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
    }
}

function closeSidebar() {
    if (isSplitMode()) {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        
        if (sidebar) sidebar.classList.remove('active');
        if (overlay) overlay.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// ===== SUBMENU HANDLING =====
function handleSubmenuClick(event) {
    // Prevent the click from bubbling up and closing the submenu
    event.stopPropagation();
    
    // In mobile mode, close sidebar when navigating
    if (isSplitMode()) {
        setTimeout(() => {
            closeSidebar();
        }, 100); // Small delay to ensure navigation starts
    }
}

// ===== EVENT LISTENERS =====
document.addEventListener('DOMContentLoaded', function() {
    // Initialize sidebar overlay click handler
    const sidebarOverlay = document.querySelector('.sidebar-overlay');
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            closeSidebar();
        });
    }
    
    // Initialize sidebar toggle button
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleSidebar();
        });
    }
});

// ===== CLICK OUTSIDE HANDLER =====
document.addEventListener('click', function(e) {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    
    // Handle responsive sidebar close
    if (isSplitMode() && sidebar && sidebar.classList.contains('active')) {
        // Close sidebar if clicking outside AND not on toggle button
        if (!sidebar.contains(e.target) && 
            (!sidebarToggle || !sidebarToggle.contains(e.target))) {
            closeSidebar();
        }
    }
    
    // Handle submenu close - but only if clicking outside sidebar entirely
    if (sidebar && !sidebar.contains(e.target)) {
        document.querySelectorAll('.has-submenu.open').forEach(item => {
            item.classList.remove('open');
        });
    }
});

// ===== WINDOW RESIZE HANDLER =====
window.addEventListener('resize', function() {
    if (!isSplitMode()) {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        
        // Reset sidebar state when switching to desktop mode
        if (sidebar) sidebar.classList.remove('active');
        if (overlay) overlay.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
});
</script>
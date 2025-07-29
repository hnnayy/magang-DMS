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
                                <a href="<?= base_url('/' . slugify($submenu['name'])) ?>">
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

    <div id="overlay" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; z-index:998;"></div>

<script>
function isSplitMode() {
    return window.innerWidth <= 992;
}

function toggleSidebar() {
    if (isSplitMode()) {
        const sidebar = document.getElementById('sidebar');
        
        if (sidebar.classList.contains('active')) {
            sidebar.classList.remove('active');
            document.body.style.overflow = 'auto'; // Enable scroll
        } else {
            sidebar.classList.add('active');
            document.body.style.overflow = 'hidden'; // Disable scroll
        }
    }
}

function closeSidebar() {
    if (isSplitMode()) {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
}

// Event listener untuk klik di luar sidebar
document.addEventListener('click', function(e) {
    if (isSplitMode()) {
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        
        // Jika klik di luar sidebar dan bukan tombol toggle
        if (sidebar && sidebar.classList.contains('active') && 
            !sidebar.contains(e.target) && 
            !sidebarToggle.contains(e.target)) {
            closeSidebar();
        }
    }
});
</script>

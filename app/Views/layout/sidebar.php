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

function openSidebar() {
    if (isSplitMode()) {
        document.getElementById('sidebar').classList.add('active');
        document.getElementById('overlay').style.display = 'block';
    }
}

function closeSidebar() {
    if (isSplitMode()) {
        document.getElementById('sidebar').classList.remove('active');
        document.getElementById('overlay').style.display = 'none';
    }
}

document.getElementById('overlay').addEventListener('click', closeSidebar);

window.addEventListener('resize', function () {
    if (!isSplitMode()) {
        document.getElementById('sidebar').classList.remove('active');
        document.getElementById('overlay').style.display = 'none';
    }
});

</script>

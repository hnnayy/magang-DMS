<div class="sidebar">
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

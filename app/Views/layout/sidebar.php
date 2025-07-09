<div class="sidebar">
    <div class="menu-container">
        <ul class="menu">
            <!-- Dashboard -->
            <li>
                <a href="<?= base_url('/') ?>" class="<?= (uri_string() == '' || uri_string() == 'dashboard') ? 'active' : '' ?>">
                    <i class="fi fi-rr-dashboard"></i>Dashboard
                </a>
            </li>

            <!-- Create User -->
            <li class="has-submenu <?= (strpos(uri_string(), 'create-user') !== false) ? 'open' : '' ?>">
                <div class="submenu-toggle" onclick="toggleSubmenu(this)">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <i class="fi fi-rr-user-add"></i><span>Create User</span>
                    </div>
                    <i class="fi fi-rr-angle-small-down arrow"></i>
                </div>
                <ul class="submenu">
                    <li><a href="<?= base_url('create-user/create') ?>" class="<?= (uri_string() == 'create-user/create') ? 'active' : '' ?>"><i class="fi fi-rr-user-add"></i>Tambah User</a></li>
                    <li><a href="<?= base_url('create-user/list') ?>" class="<?= (uri_string() == 'create-user/list') ? 'active' : '' ?>"><i class="fi fi-rr-users-alt"></i>Lihat User</a></li>
                </ul>
            </li>

            <!-- Master Data -->
            <li class="has-submenu <?= (strpos(uri_string(), 'data-master') !== false) ? 'open' : '' ?>">
                <div class="submenu-toggle" onclick="toggleSubmenu(this)">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <i class="fi fi-rr-database"></i><span>Master Data</span>
                    </div>
                    <i class="fi fi-rr-angle-small-down arrow"></i>
                </div>
                <ul class="submenu">
                    <li><a href="<?= base_url('data-master/create') ?>" class="<?= (uri_string() == 'data-master/create') ? 'active' : '' ?>"><i class="fi fi-rr-add-document"></i>Tambah Unit</a></li>
                    <li><a href="<?= base_url('data-master/list') ?>" class="<?= (uri_string() == 'data-master/list') ? 'active' : '' ?>"><i class="fi fi-rr-clipboard-list"></i>Lihat Unit</a></li>
                </ul>
            </li>

            <!-- Document Management -->
            <li class="has-submenu <?= (strpos(uri_string(), 'dokumen') !== false) ? 'open' : '' ?>">
                <div class="submenu-toggle" onclick="toggleSubmenu(this)">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <i class="fi fi-rr-document"></i><span>Document Management</span>
                    </div>
                    <i class="fi fi-rr-angle-small-down arrow"></i>
                </div>
                <ul class="submenu">
                    <li><a href="<?= base_url('dokumen/add') ?>" class="<?= (uri_string() == 'dokumen/add') ? 'active' : '' ?>"><i class="fi fi-rr-add-document"></i>Tambah Dokumen</a></li>
                    <li><a href="<?= base_url('dokumen/pengajuan') ?>" class="<?= (uri_string() == 'dokumen/pengajuan') ? 'active' : '' ?>"><i class="fi fi-rr-clipboard-list"></i>Daftar Pengajuan</a></li>
                    <li><a href="<?= base_url('dokumen/daftar') ?>" class="<?= (uri_string() == 'dokumen/daftar') ? 'active' : '' ?>"><i class="fi fi-rr-list"></i>Daftar Dokumen</a></li>
                    <li><a href="<?= base_url('dokumen/persetujuan') ?>" class="<?= (uri_string() == 'dokumen/persetujuan') ? 'active' : '' ?>"><i class="fi fi-rr-check-circle"></i>Persetujuan Dokumen</a></li>
                    <li><a href="<?= base_url('dokumen/config-jenis-dokumen') ?>" class="<?= (uri_string() == 'dokumen/config-jenis-dokumen') ? 'active' : '' ?>"><i class="fi fi-rr-clipboard-list"></i>Jenis & Kode Dokumen</a></li>
                </ul>
            </li>

            <!-- Menu -->
            <li class="has-submenu <?= (strpos(uri_string(), 'Menu') !== false) ? 'open' : '' ?>">
                <div class="submenu-toggle" onclick="toggleSubmenu(this)">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <i class="fi fi-rr-apps"></i><span>Menu</span>
                    </div>
                    <i class="fi fi-rr-angle-small-down arrow"></i>
                </div>
                <ul class="submenu">
                    <li><a href="<?= base_url('menu/create') ?>" class="<?= (uri_string() == 'menu/create') ? 'active' : '' ?>"><i class="fi fi-rr-add"></i>Tambah Menu</a></li>
                    <li><a href="<?= base_url('Menu/lihat-menu') ?>" class="<?= (uri_string() == 'Menu/lihat-menu') ? 'active' : '' ?>"><i class="fi fi-rr-list-check"></i>Lihat Menu</a></li>
                </ul>
            </li>

            <!-- Sub Menu -->
            <li class="has-submenu <?= (strpos(uri_string(), 'SubMenu') !== false) ? 'open' : '' ?>">
                <div class="submenu-toggle" onclick="toggleSubmenu(this)">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <i class="fi fi-rr-menu-burger"></i><span>Sub Menu</span>
                    </div>
                    <i class="fi fi-rr-angle-small-down arrow"></i>
                </div>
                <ul class="submenu">
                    <li><a href="<?= base_url('submenu/create') ?>" class="<?= (uri_string() == 'submenu/create') ? 'active' : '' ?>"><i class="fi fi-rr-add"></i>Tambah Submenu</a></li>
                    <li><a href="<?= base_url('submenu/lihat-submenu') ?>" class="<?= (uri_string() == 'submenu/lihat-submenu') ? 'active' : '' ?>"><i class="fi fi-rr-list-check"></i>Lihat Submenu</a></li>
                </ul>
            </li>

            <!-- Role -->
            <li class="has-submenu <?= (strpos(uri_string(), 'Role') !== false) ? 'open' : '' ?>">
                <div class="submenu-toggle" onclick="toggleSubmenu(this)">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <i class="fi fi-rr-shield-check"></i><span>Role</span>
                    </div>
                    <i class="fi fi-rr-angle-small-down arrow"></i>
                </div>
                <ul class="submenu">
                    <li><a href="<?= base_url('role/create') ?>" class="<?= (uri_string() == 'Role/create') ? 'active' : '' ?>"><i class="fi fi-rr-add"></i>Tambah Role</a></li>
                    <li><a href="<?= base_url('Role/list') ?>" class="<?= (uri_string() == 'Role/list') ? 'active' : '' ?>"><i class="fi fi-rr-list-check"></i>Lihat Role</a></li>
                </ul>
            </li>

            <!-- Privilege -->
            <li class="has-submenu <?= (strpos(uri_string(), 'Privilege') !== false) ? 'open' : '' ?>">
                <div class="submenu-toggle" onclick="toggleSubmenu(this)">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <i class="fi fi-rr-lock"></i><span>Privilege</span>
                    </div>
                    <i class="fi fi-rr-angle-small-down arrow"></i>
                </div>
                <ul class="submenu">
                    <li><a href="<?= base_url('privilege/create') ?>" class="<?= (uri_string() == 'Privilege/create') ? 'active' : '' ?>"><i class="fi fi-rr-add"></i>Tambah Privilege</a></li>
                    <li><a href="<?= base_url('Privilege/list') ?>" class="<?= (uri_string() == 'Privilege/list') ? 'active' : '' ?>"><i class="fi fi-rr-list-check"></i>Lihat Privilege</a></li>
                </ul>
            </li>
        </ul>
    </div>
</div>

<div class="sidebar">
    <ul class="menu">
        <li><a href="<?= base_url('/') ?>" class="<?= (uri_string() == 'dashboard') ? 'active' : '' ?>"><i class="fi fi-rr-dashboard"></i>Dashboard</a></li>

        <li class="has-submenu">
            <div class="submenu-toggle" onclick="toggleSubmenu(this)">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <i class="fi fi-rr-user-add"></i><span>Create User</span>
                </div>
                <i class="fi fi-rr-angle-small-down arrow"></i>
            </div>
            <ul class="submenu">
                <li><a href="<?= base_url('create-user/create') ?>"><i class="fi fi-rr-user-pen"></i>Tambah User</a></li>
                <li><a href="<?= base_url('create-user/list') ?>"><i class="fi fi-rr-users-alt"></i>Lihat User</a></li>
            </ul>
        </li>

        <li class="has-submenu <?= (strpos(uri_string(), 'dokumen') !== false) ? 'open' : '' ?>">
            <div class="submenu-toggle" onclick="toggleSubmenu(this)">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <i class="fi fi-rr-document"></i><span>Kelola Dokumen</span>
                </div>
                <i class="fi fi-rr-angle-small-down arrow"></i>
            </div>
            <ul class="submenu">
                <li><a href="<?= base_url('dokumen/add') ?>"><i class="fi fi-rr-add-document"></i>Tambah Dokumen</a></li>
                <li><a href="<?= base_url('dokumen/pengajuan') ?>"><i class="fi fi-rr-clipboard-list"></i>Daftar Pengajuan</a></li>
            </ul>
        </li>

        <li><a href="<?= base_url('dokumen/daftar') ?>" class="<?= (uri_string() == 'dokumen/daftar') ? 'active' : '' ?>"><i class="fi fi-rr-list"></i>Daftar Dokumen</a></li>

        <li class="has-submenu <?= (strpos(uri_string(), 'data-master') !== false) ? 'open' : '' ?>">
            <div class="submenu-toggle" onclick="toggleSubmenu(this)">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <i class="fi fi-rr-database"></i><span>Master Data</span>
                </div>
                <i class="fi fi-rr-angle-small-down arrow"></i>
            </div>
            <ul class="submenu">
                <li><a href="<?= base_url('data-master/create') ?>"><i class="fi fi-rr-add-document"></i>Tambah Unit</a></li>
                <li><a href="<?= base_url('data-master/list') ?>"><i class="fi fi-rr-clipboard-list"></i>Lihat Unit</a></li>
            </ul>
        </li>

        <li><a href="<?= base_url('dokumen/persetujuan') ?>" class="<?= (uri_string() == 'dokumen/persetujuan') ? 'active' : '' ?>"><i class="fi fi-rr-check-circle"></i>Persetujuan Dokumen</a></li>
    </ul>

    <div class="logout">
        <a href="<?= base_url('logout') ?>"><i class="fi fi-rr-sign-out-alt"></i> Log Out</a>
    </div>
</div>

<!-- Overlay harus di luar .sidebar -->
<div class="sidebar-overlay" onclick="toggleSidebar()" style="display: none;"></div>

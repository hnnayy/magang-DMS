<div class="sidebar">
        <ul class="menu">
            <li><a href="<?= base_url('dashboard') ?>" class="<?= (uri_string() == 'dashboard') ? 'active' : '' ?>"><i class="fi fi-rr-dashboard"></i>Dashboard</a></li>

            <li class="has-submenu">
                <div class="submenu-toggle" onclick="toggleSubmenu(this)">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <i class="fi fi-rr-user-add"></i><span>Create User</span>
                    </div>
                    <i class="fi fi-rr-angle-small-down arrow"></i>
                </div>
                <ul class="submenu">
                    <li><a href="<?= base_url('user/create') ?>"><i class="fi fi-rr-user-pen"></i>Tambah User</a></li>
                    <li><a href="<?= base_url('user/list') ?>"><i class="fi fi-rr-users-alt"></i>Lihat User</a></li>
                </ul>
            </li>

            <li class="has-submenu <?= (strpos(uri_string(), 'document') !== false) ? 'open' : '' ?>">
                <div class="submenu-toggle" onclick="toggleSubmenu(this)">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <i class="fi fi-rr-document"></i><span>Kelola Dokumen</span>
                    </div>
                    <i class="fi fi-rr-angle-small-down arrow"></i>
                </div>
                <ul class="submenu">
                    <li><a href="<?= base_url('document/create') ?>"><i class="fi fi-rr-add-document"></i>Tambah Dokumen</a></li>
                    <li><a href="<?= base_url('document/submissions') ?>"><i class="fi fi-rr-clipboard-list"></i>Daftar Pengajuan</a></li>
                </ul>
            </li>

            <li><a href="<?= base_url('document/list') ?>" class="<?= (uri_string() == 'document/list') ? 'active' : '' ?>"><i class="fi fi-rr-list"></i>Daftar Dokumen</a></li>
            <li><a href="<?= base_url('master-data') ?>" class="<?= (uri_string() == 'master-data') ? 'active' : '' ?>"><i class="fi fi-rr-database"></i>Data Master</a></li>
            <li><a href="<?= base_url('document/approval') ?>" class="<?= (uri_string() == 'document/approval') ? 'active' : '' ?>"><i class="fi fi-rr-check-circle"></i>Persetujuan Dokumen</a></li>
        </ul>

        <div class="logout">
            <a href="<?= base_url('logout') ?>"><i class="fi fi-rr-sign-out-alt"></i> Log Out</a>
        </div>
    </div>
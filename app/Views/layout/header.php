<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Dashboard DMS - Telkom University' ?></title>

    <!-- Flaticon Icons -->
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-regular-rounded/css/uicons-regular-rounded.css">
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-solid-rounded/css/uicons-solid-rounded.css">
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-solid-straight/css/uicons-solid-straight.css">

    <!-- Bootstrap & Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/global.css') ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Font Awesome & jQuery -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Tambahkan CDN Select2 di head layout -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

</head>
<body>

    <div class="topbar">
        <div class="sidebar-toggle" onclick="toggleSidebar()">
            <i class="fi fi-rr-menu-burger"></i>
        </div>

        <div class="logo">
            <a href="<?= base_url('/') ?>">
                <img src="<?= base_url('assets/images/logo/telkom-logo.png') ?>" alt="Telkom-logo" />
            </a>
        </div>

        <div class="user-info">
            <i class="fi fi-rr-envelope"></i>
            <i class="fi fi-rr-bell"></i>
            <i class="fi fi-rr-search"></i>

            <div class="profile-dropdown" onclick="toggleProfileMenu()">
                <img src="<?= base_url('assets/images/profil/profil.jpg') ?>" alt="Profil" class="profile-img" />
                <span class="username"><?= session()->get('username') ?? 'Admin' ?></span>
                <i class="fi fi-rr-caret-down dropdown-arrow"></i>
                <div class="profile-menu">
                    <a href="<?= base_url('profile') ?>"><i class="fi fi-rr-user"></i> Profil Saya</a>
                    <a href="<?= base_url('settings') ?>"><i class="fi fi-rr-settings"></i> Pengaturan</a>
                    <a href="<?= base_url('logout') ?>"><i class="fi fi-rr-sign-out-alt"></i> Log Out</a>
                </div>
            </div>
        </div>
    </div>

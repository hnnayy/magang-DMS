<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Dashboard DMS - Telkom University' ?></title>

    <!-- Link CDN Flaticon UIcons -->
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-regular-rounded/css/uicons-regular-rounded.css">
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-solid-rounded/css/uicons-solid-rounded.css">
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-solid-straight/css/uicons-solid-straight.css">
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
</head>
<body>

    <div class="topbar">
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
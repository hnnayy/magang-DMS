<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard DMS - Telkom University</title>

    <!-- Link CDN Flaticon UIcons -->
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-regular-rounded/css/uicons-regular-rounded.css">
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-solid-rounded/css/uicons-solid-rounded.css">
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-solid-straight/css/uicons-solid-straight.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #ffffff;
            padding: 15px 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            height: 70px;
        }

        .logo {
            display: flex;
            align-items: center;
            height: 100%;
        }

        .logo img {
            height: 40px;
            width: auto;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
            height: 100%;
        }

        .user-info i {
            font-size: 20px;
            cursor: pointer;
            color: #666;
            transition: color 0.3s ease;
        }

        .user-info i:hover {
            color: #b41616;
        }

        .profile-dropdown {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 25px;
            transition: background-color 0.3s ease;
            position: relative;
            height: fit-content;
        }

        .profile-dropdown:hover {
            background-color: #f8f9fa;
        }

        .profile-img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #b41616;
        }

        .username {
            font-weight: 600;
            font-size: 14px;
            color: #333;
        }

        .dropdown-arrow {
            font-size: 12px;
            color: #666;
            transition: transform 0.3s ease;
        }

        .profile-dropdown.active .dropdown-arrow {
            transform: rotate(180deg);
        }

        .profile-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            min-width: 180px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1001;
        }

        .profile-dropdown.active .profile-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .profile-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            text-decoration: none;
            color: #333;
            transition: background-color 0.3s ease;
            border-bottom: 1px solid #f0f0f0;
        }

        .profile-menu a:hover {
            background-color: #f8f9fa;
            color: #b41616;
        }

        .profile-menu a:last-child {
            border-bottom: none;
        }

        .sidebar {
            width: 260px;
            background-color: #ffffff;
            position: fixed;
            left: 0;
            top: 70px;
            height: calc(100vh - 70px);
            box-shadow: 2px 0 4px rgba(0,0,0,0.1);
            overflow-y: auto;
            padding-top: 20px;
        }

        .sidebar .menu {
            list-style: none;
            padding: 0 15px;
        }

        .sidebar .menu li {
            margin-bottom: 5px;
            list-style: none; /* Pastikan tidak ada bullet */
        }

        .sidebar .menu li > a,
        .submenu-toggle {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            text-decoration: none;
            color: #555;
            border-radius: 8px;
            transition: all 0.3s ease;
            cursor: pointer;
            font-weight: 500;
        }

        .sidebar .menu li > a:hover,
        .submenu-toggle:hover {
            background-color: #f8f9fa;
            color: #b41616;
        }

        .sidebar .menu li > a.active {
            background-color: #b41616;
            color: white;
        }

        .sidebar .menu li i {
            font-size: 18px;
            width: 20px;
            text-align: center;
        }

        .has-submenu {
            position: relative;
        }

        .submenu-toggle {
            justify-content: space-between;
            width: 100%;
            border: none;
            background: none;
        }

        .submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            background-color: #f8f9fa;
            border-radius: 8px;
            margin-top: 5px;
            list-style: none; /* Hilangkan bullet pada submenu */
        }

        .has-submenu.open .submenu {
            max-height: 200px;
        }

        .submenu li {
            margin-left: 25px;
            list-style: none; /* Pastikan submenu item tidak ada bullet */
        }

        .submenu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 20px 10px 40px;
            font-size: 14px;
            color: #666;
            border-radius: 0;
            text-decoration: none;
        }

        .submenu a:hover {
            background-color: #e9ecef;
            color: #b41616;
        }

        .submenu a i {
            font-size: 16px;
            width: 16px;
            text-align: center;
        }

        .arrow {
            font-size: 12px;
            transition: transform 0.3s ease;
        }

        .has-submenu.open .arrow {
            transform: rotate(180deg);
        }

        .logout {
            position: absolute;
            bottom: 20px;
            left: 15px;
            right: 15px;
        }

        .logout a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            text-decoration: none;
            color: #555;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .logout a:hover {
            background-color: #f8f9fa;
            color: #b41616;
        }

        .main-content {
            margin-left: 260px;
            margin-top: 70px;
            padding: 30px;
            min-height: calc(100vh - 70px);
        }

        .welcome-box {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            text-align: center;
            border: 1px solid #e9ecef;
        }

        .welcome-box h1 {
            font-size: 32px;
            margin-bottom: 15px;
            color: #b41616;
            font-weight: 600;
        }

        .welcome-box p {
            font-size: 16px;
            margin-bottom: 30px;
            color: #666;
            line-height: 1.6;
        }

        .welcome-box img {
            max-width: 80%;
            height: auto;
            border-radius: 12px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .topbar {
                padding: 10px 15px;
            }
            
            .welcome-box {
                padding: 25px;
            }
            
            .welcome-box h1 {
                font-size: 24px;
            }
        }

        /* Scrollbar Styling */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #b41616;
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #8b1111;
        }
    </style>
</head>
<body>

    <div class="topbar">
        <div class="logo">
            <a href="/">
                <img src='assets/images/logo/telkom-logo.png' alt="Telkom-logo" />
            </a>
        </div>
        <div class="user-info">
            <i class="fi fi-rr-envelope"></i>
            <i class="fi fi-rr-bell"></i>
            <i class="fi fi-rr-search"></i>

            <div class="profile-dropdown" onclick="toggleProfileMenu()">
                <img src='assets/images/profil/profil.jpg' alt="Profil" class="profile-img" />
                <span class="username">Admin</span>
                <i class="fi fi-rr-caret-down dropdown-arrow"></i>
                <div class="profile-menu">
                    <a href="#"><i class="fi fi-rr-user"></i> Profil Saya</a>
                    <a href="#"><i class="fi fi-rr-settings"></i> Pengaturan</a>
                    <a href="#"><i class="fi fi-rr-sign-out-alt"></i> Log Out</a>
                </div>
            </div>
        </div>
    </div>

    <div class="sidebar">
        <ul class="menu">
            <li><a href="#"><i class="fi fi-rr-dashboard"></i>Dashboard</a></li>

            <li class="has-submenu">
                <div class="submenu-toggle" onclick="toggleSubmenu(this)">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <i class="fi fi-rr-user-add"></i><span>Create User</span>
                    </div>
                    <i class="fi fi-rr-angle-small-down arrow"></i>
                </div>
                <ul class="submenu">
                    <li><a href="#"><i class="fi fi-rr-user-pen"></i>Tambah User</a></li>
                    <li><a href="#"><i class="fi fi-rr-users-alt"></i>Lihat User</a></li>
                </ul>
            </li>

            <li class="has-submenu open">
                <div class="submenu-toggle" onclick="toggleSubmenu(this)">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <i class="fi fi-rr-document"></i><span>Kelola Dokumen</span>
                    </div>
                    <i class="fi fi-rr-angle-small-down arrow"></i>
                </div>
                <ul class="submenu">
                    <li><a href="#"><i class="fi fi-rr-add-document"></i>Tambah Dokumen</a></li>
                    <li><a href="#"><i class="fi fi-rr-clipboard-list"></i>Daftar Pengajuan</a></li>
                </ul>
            </li>

            <li><a href="#"><i class="fi fi-rr-list"></i>Daftar Dokumen</a></li>
            <li><a href="#"><i class="fi fi-rr-database"></i>Data Master</a></li>
            <li><a href="#"><i class="fi fi-rr-check-circle"></i>Persetujuan Dokumen</a></li>
        </ul>

        <div class="logout">
            <a href="#"><i class="fi fi-rr-sign-out-alt"></i> Log Out</a>
        </div>
    </div>

    <?= $this->renderSection('content')?>

    <script>
        function toggleSubmenu(element) {
            const parent = element.parentElement;
            const isCurrentlyOpen = parent.classList.contains('open');
            
            // Tutup semua dropdown yang terbuka
            document.querySelectorAll('.has-submenu.open').forEach(item => {
                item.classList.remove('open');
            });
            
            // Jika dropdown yang diklik tidak sedang terbuka, buka dropdown tersebut
            if (!isCurrentlyOpen) {
                parent.classList.add('open');
            }
        }

        function toggleProfileMenu() {
            const dropdown = document.querySelector('.profile-dropdown');
            dropdown.classList.toggle('active');
        }

        // Close profile dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.querySelector('.profile-dropdown');
            if (!dropdown.contains(event.target)) {
                dropdown.classList.remove('active');
            }
        });

        // Close submenu when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.has-submenu')) {
                document.querySelectorAll('.has-submenu.open').forEach(item => {
                    item.classList.remove('open');
                });
            }
        });
    </script>
</body>
</html>
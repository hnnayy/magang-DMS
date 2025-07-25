<?php

if (!function_exists('hasPrivilege')) {
    /**
     * Cek apakah user memiliki privilege tertentu terhadap suatu submenu.
     *
     * @param string $slugSubmenu Slug dari submenu (misalnya: 'daftar-pengajuan')
     * @param string $action Nama aksi (can_create, can_update, can_delete, can_approve)
     * @return bool
     */
    function hasPrivilege(string $slugSubmenu, string $action): bool
    {
        $privileges = session()->get('privileges');

        return isset($privileges[$slugSubmenu][$action]) && $privileges[$slugSubmenu][$action];//user id-check db usernya
    }
}



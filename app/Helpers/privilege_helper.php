<?php
// Buat di helper misalnya: app/Helpers/privilege_helper.php
if (!function_exists('hasPrivilege')) {
    function hasPrivilege($submenu_id, $action)
    {
        $role_id = session()->get('role_id');
        if (!$role_id) return false;

        $db = \Config\Database::connect();
        $query = $db->table('privilege')
            ->where('role_id', $role_id)
            ->where('submenu_id', $submenu_id)
            ->get()
            ->getRow();

        if (!$query) return false;

        return (bool) $query->$action; // $action = create / update / delete / approve
    }
}

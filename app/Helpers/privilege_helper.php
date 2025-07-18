<?php

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

        $result = (bool) $query->$action;

        // Logging debug privilege access
        log_message('debug', 'Check privilege: ' . json_encode([
            'role_id'    => $role_id,
            'submenu_id' => $submenu_id,
            'action'     => $action,
            'result'     => $result,
        ]));

        return $result;
    }
}

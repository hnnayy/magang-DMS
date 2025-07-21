<?php

use App\Models\PrivilegeModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

if (!function_exists('getSidebarMenuByRole')) {
    function getSidebarMenuByRole($roleId = null)
    {
        try {
            if (!$roleId) {
                $roleId = session()->get('role_id');
            }

            if (!$roleId) {
                return [];
            }

            $privilegeModel = new PrivilegeModel();

            $privileges = $privilegeModel
                ->select('submenu.id AS submenu_id, submenu.name AS submenu_name, submenu.parent, menu.name AS menu_name, menu.icon AS menu_icon')
                ->join('submenu', 'submenu.id = privilege.submenu_id')
                ->join('menu', 'menu.id = submenu.parent')
                ->where('privilege.role_id', $roleId)
                // ->where('privilege.can_create', 1) // hanya submenu yg boleh create
                ->where('submenu.status', 1)
                ->where('menu.status', 1)
                ->orderBy('menu.id ASC, submenu.id ASC')
                ->findAll();

            $menuTree = [];

            foreach ($privileges as $row) {
                $menuName = $row['menu_name'];
                if (!isset($menuTree[$menuName])) {
                    $menuTree[$menuName] = [
                        'icon'     => $row['menu_icon'],
                        'submenus' => [],
                    ];
                }

                $menuTree[$menuName]['submenus'][] = [
                    'id'   => $row['submenu_id'],
                    'name' => $row['submenu_name'],
                ];
            }

            return $menuTree;
        } catch (DatabaseException $e) {
            log_message('error', 'Sidebar DB Error: ' . $e->getMessage());
            return [];
        }
    }
}

<?php

namespace App\Models;
use CodeIgniter\Model;

class PrivilegeModel extends Model
{
    protected $table = 'privilege';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['role_id', 'submenu_id', 'create', 'update', 'delete', 'approve'];
    
    // Method untuk join dengan role dan submenu
    public function getWithRelations()
    {
        return $this->select('privilege.*, role.name as role_name, submenu.name as submenu_name, menu.name as menu_name')
                    ->join('role', 'role.id = privilege.role_id', 'left')
                    ->join('submenu', 'submenu.id = privilege.submenu_id', 'left')
                    ->join('menu', 'menu.id = submenu.parent', 'left')
                    ->findAll();
    }
}

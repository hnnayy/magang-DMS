<?php

namespace App\Models;
use CodeIgniter\Model;

class MenuModel extends Model
{
    protected $table = 'menu';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['name', 'icon', 'status'];
    
    // Method untuk join dengan submenu
    public function getWithSubmenus()
    {
        return $this->select('menu.*, GROUP_CONCAT(submenu.name) as submenus')
                    ->join('submenu', 'submenu.parent = menu.id', 'left')
                    ->groupBy('menu.id')
                    ->findAll();
    }
}
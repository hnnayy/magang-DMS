<?php

namespace App\Models;
use CodeIgniter\Model;

class SubmenuModel extends Model
{
    protected $table = 'submenu';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['parent', 'name', 'status'];
    protected $useTimestamps = false;
    
    // Method untuk join dengan menu
    public function getWithMenu()
    {
        return $this->select('submenu.*, menu.name as menu_name')
                    ->join('menu', 'menu.id = submenu.parent', 'left')
                    ->findAll();
    }
}

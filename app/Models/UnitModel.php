<?php

// app/Models/UnitModel.php
namespace App\Models;
use CodeIgniter\Model;

class UnitModel extends Model
{
    protected $table = 'unit';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['parent_id', 'name', 'status'];

    // Method untuk join dengan unit_parent
    public function getWithParent()
    {
        return $this->select('unit.*, unit_parent.name as parent_name')
                    ->join('unit_parent', 'unit_parent.id = unit.parent_id', 'left')
                    ->findAll();
    }

    // Konversi status dari integer ke string jika diperlukan
    public function getStatusText($status)
    {
        return $status == 1 ? 'Active' : 'Inactive';
    }
}
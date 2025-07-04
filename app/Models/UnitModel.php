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

    //validasi otomatis
    protected $validationRules = [
        'parent_id' => 'required|is_natural_no_zero',
        'name'      => 'required|alpha_space|max_length[40]',
        'status'    => 'permit_empty|in_list[1,2]',   // 1=Active, 2=Inactive
    ];

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
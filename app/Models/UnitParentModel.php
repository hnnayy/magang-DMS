<?php
namespace App\Models;
use CodeIgniter\Model;

class UnitParentModel extends Model
{
    protected $table = 'unit_parent';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['type', 'name', 'status'];
    
    // Method untuk join dengan unit
    public function getWithUnits()
    {
        return $this->select('unit_parent.*, GROUP_CONCAT(unit.name) as units')
                    ->join('unit', 'unit.parent_id = unit_parent.id', 'left')
                    ->groupBy('unit_parent.id')
                    ->findAll();
    }
}
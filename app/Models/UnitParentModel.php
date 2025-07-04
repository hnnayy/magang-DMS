<?php
namespace App\Models;
use CodeIgniter\Model;

class UnitParentModel extends Model
{
    protected $table = 'unit_parent';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['type', 'name', 'status'];

    /** ---------- Validasi otomatis ---------- */
    protected $validationRules = [
        'type' => 'required|in_list[1,2]',        // 1 = Directorate, 2 = Faculty
        'name' => 'required|alpha_space|max_length[40]',
    ];
    
    // Method untuk join dengan unit, fitur lama     
    public function getWithUnits()
    {
        return $this->select('unit_parent.*, GROUP_CONCAT(unit.name) as units')
                    ->join('unit', 'unit.parent_id = unit_parent.id', 'left')
                    ->groupBy('unit_parent.id')
                    ->findAll();
    }
}
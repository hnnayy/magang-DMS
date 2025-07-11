<?php

namespace App\Models;
use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'user';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes   = false;    
    protected $useTimestamps    = false;   
  
    protected $allowedFields = ['unit_id', 'username', 'fullname', 'status', 'createddate', 'createdby'];
    
    // Auto set created date
    protected $beforeInsert = ['setCreatedDate'];
    
    protected function setCreatedDate(array $data)
    {
        $data['data']['createddate'] = date('Y-m-d H:i:s');
        return $data;
    }
    
    // Method untuk join dengan unit dan creator
    public function getWithRelations()
    {
        return $this->select('user.*, unit.name as unit_name, creator.fullname as created_by_name')
                    ->join('unit', 'unit.id = user.unit_id', 'left')
                    ->join('user creator', 'creator.id = user.createdby', 'left')
                    ->findAll();
    }

    public function getUnitsByDirectorate($directorateId)
    {
        return $this->where('parent_name', $directorateId)->findAll(); // Sesuaikan logika
    }

    public function softDeleteById($id): bool
    {
        return $this->update($id, ['status' => 0]);
    }

     public function findAllActive()
    {
        return $this->where('status', 1)->findAll();
    }
}
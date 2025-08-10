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
        // Set status ke 0 untuk soft delete
        $result = $this->update($id, ['status' => 0]);
        
        if ($result) {
            log_message('info', "User with ID {$id} has been soft deleted");
            return true;
        }
        
        log_message('error', "Failed to soft delete user with ID {$id}");
        return false;
    }

     public function findAllActive()
    {
        return $this->where('status', 1)->findAll();
    }
}
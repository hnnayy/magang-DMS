<?php

namespace App\Models;
use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'user';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['unit_id', 'username', 'fullname', 'status', 'createddate', 'createdby'];
    
    // Auto set created date
    protected $useTimestamps = false;
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
}
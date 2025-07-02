<?php

namespace App\Models;
use CodeIgniter\Model;

class UserRoleModel extends Model
{
    protected $table = 'user_role';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['user_id', 'role_id', 'status', 'createddate', 'createdby'];
    
    protected $useTimestamps = false;
    protected $beforeInsert = ['setCreatedDate'];
    
    protected function setCreatedDate(array $data)
    {
        $data['data']['createddate'] = date('Y-m-d H:i:s');
        return $data;
    }
    
    // Method untuk join dengan user, role, dan creator
    public function getWithRelations()
    {
        return $this->select('user_role.*, user.fullname as user_name, role.name as role_name, creator.fullname as created_by_name')
                    ->join('user', 'user.id = user_role.user_id', 'left')
                    ->join('role', 'role.id = user_role.role_id', 'left')
                    ->join('user creator', 'creator.id = user_role.createdby', 'left')
                    ->findAll();
    }
}
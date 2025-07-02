<?php

namespace App\Models;
use CodeIgniter\Model;

class DocumentModel extends Model
{
    protected $table = 'document';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['type', 'number', 'date_published', 'revision', 'title', 'description', 'filepath', 'unit_id', 'status', 'createddate', 'createdby'];
    
    protected $useTimestamps = false;
    protected $beforeInsert = ['setCreatedDate'];
    
    protected function setCreatedDate(array $data)
    {
        $data['data']['createddate'] = date('Y-m-d H:i:s');
        return $data;
    }
    
    // Method untuk join dengan document_type, unit, dan creator
    public function getWithRelations()
    {
        return $this->select('document.*, document_type.name as type_name, unit.name as unit_name, user.fullname as created_by_name')
                    ->join('document_type', 'document_type.id = document.type', 'left')
                    ->join('unit', 'unit.id = document.unit_id', 'left')
                    ->join('user', 'user.id = document.createdby', 'left')
                    ->findAll();
    }
}

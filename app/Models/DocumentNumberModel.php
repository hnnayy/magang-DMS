<?php

namespace App\Models;

use CodeIgniter\Model;

class DocumentNumberModel extends Model
{
    protected $table = 'document_number';
    protected $primaryKey = 'id';
    protected $returnType = 'array'; 
    protected $useTimestamps = false;

    protected $allowedFields = [
        'unit_id',
        'type',
        'number',
        'createddate',
        'createdby',
    ];

    protected $beforeInsert = ['setCreatedDate'];

    protected function setCreatedDate(array $data)
    {
        if (!isset($data['data']['createddate'])) {
            $data['data']['createddate'] = date('Y-m-d H:i:s');
        }
        return $data;
    }
}

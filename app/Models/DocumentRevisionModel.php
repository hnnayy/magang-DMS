<?php

namespace App\Models;

use CodeIgniter\Model;

class DocumentRevisionModel extends Model
{
    protected $table = 'document_revision';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'document_id',
        'revision',
        'filename',
        'filepath',
        'filesize',
        'remark',
        'createddate',
        'createdby',
    ];

    // Auto-set createddate saat insert
    protected $beforeInsert = ['setCreatedDate'];

    protected function setCreatedDate(array $data)
    {
        if (!isset($data['data']['createddate'])) {
            $data['data']['createddate'] = date('Y-m-d H:i:s');
        }
        return $data;
    }

    public function getLatestRevisionNumber($documentId)
    {
        return $this->where('document_id', $documentId)
                    ->orderBy('revision', 'DESC')
                    ->limit(1)
                    ->get()
                    ->getRowArray();
    }
}

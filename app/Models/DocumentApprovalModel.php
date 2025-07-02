<?php

namespace App\Models;
use CodeIgniter\Model;

class DocumentApprovalModel extends Model
{
    protected $table = 'document_approval';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['document_id', 'remark', 'status', 'approvedate', 'approveby'];
    
    // Method untuk join dengan document dan approver
    public function getWithRelations()
    {
        return $this->select('document_approval.*, document.title as document_title, user.fullname as approved_by_name')
                    ->join('document', 'document.id = document_approval.document_id', 'left')
                    ->join('user', 'user.id = document_approval.approveby', 'left')
                    ->findAll();
    }
}
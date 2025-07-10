<?php
namespace App\Models;

use CodeIgniter\Model;

class DocumentModel extends Model
{
    protected $table = 'document';
    protected $primaryKey = 'id';
protected $allowedFields = [
    'type',
    'kode_dokumen_id',
    'unit_id',
    'title',
    'number',
    'revision',
    'description',
    'filepath',
    'filename',       
    'status',
    'createddate',
    'createdby',
    'updated_at',
    'date_published', 
];




    public function getWithJenisDokumen()
{
    return $this->select('document.*, document_type.name AS jenis_dokumen')
                ->join('document_type', 'document_type.id = document.type', 'left')
                ->findAll();
}

}

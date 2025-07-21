<?php
namespace App\Models;

use CodeIgniter\Model;

class DocumentModel extends Model
{
    protected $table      = 'document';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'type',
        'kode_dokumen_id',
        'unit_id',   
        'title',
        'number',
        'revision',
        'description',
        'status',
        'createddate',
        'createdby',
        'updated_at',
        'date_published',
        'updated_at',
    ];

    public function getWithRelations()
    {
        return $this->select('
                    document.*,
                    document_type.name AS jenis_dokumen,
                    unit.name AS unit_name,
                    unit_parent.name AS parent_unit_name
                ')
                ->join('document_type', 'document_type.id = document.type', 'left')
                ->join('unit', 'unit.id = document.unit_id', 'left')
                ->join('unit_parent', 'unit_parent.id = document.unit_parent_id', 'left')
                ->findAll();
    }

    /**
     * Ambil satu dokumen lengkap berdasarkan ID
     */
    public function getByIdWithRelations($id)
    {
        return $this->select('
                    document.*,
                    document_type.name AS jenis_dokumen,
                    unit.name AS unit_name,
                    unit_parent.name AS parent_unit_name
                ')
                ->join('document_type', 'document_type.id = document.type', 'left')
                ->join('unit', 'unit.id = document.unit_id', 'left')
                ->join('unit_parent', 'unit_parent.id = document.unit_parent_id', 'left')
                ->where('document.id', $id)
                ->first();
    }
}

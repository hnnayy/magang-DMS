<?php

namespace App\Models;

use CodeIgniter\Model;

class DocumentCodeModel extends Model
{
    protected $table = 'kode_dokumen';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    
    protected $allowedFields = [
        'document_type_id', 
        'kode',
        'nama',
        'status',
        'created_at',
        'updated_at',
    ];
    
    protected $useTimestamps = true;

    
}

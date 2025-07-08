<?php

// app/Models/DocumentModel.php
namespace App\Models;

use CodeIgniter\Model;

class DocumentModel extends Model
{
    protected $table = 'document'; // atau 'document' kalau migrasinya pakai itu
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'fakultas',
        'bagian',
        'nama',
        'jenis',
        'kode_nama',
        'nomor',
        'keterangan',
        'file',
        'created_at',
        'updated_at'
    ];
}

<?php

// app/Models/DocumentModel.php
namespace App\Models;

use CodeIgniter\Model;

class DocumentModel extends Model
{
    protected $table = 'document'; // atau 'document' kalau migrasinya pakai itu
    protected $primaryKey = 'id';
    protected $allowedFields = [
    'type',
    'number',
    'title',
    'description',
    'filepath',
    'unit_id',
    'createddate',
    'createdby'
];

}

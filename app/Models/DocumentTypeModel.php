<?php

namespace App\Models;
use CodeIgniter\Model;

class DocumentTypeModel extends Model
{
    protected $table = 'document_type';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['name', 'description', 'status'];
}
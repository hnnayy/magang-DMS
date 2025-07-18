<?php

namespace App\Models;

use CodeIgniter\Model;

class UnitParentModel extends Model
{
 
    protected $table = 'unit_parent';
    protected $primaryKey = 'id';
    protected $allowedFields = ['type', 'name', 'status'];
    protected $returnType = 'array';
}

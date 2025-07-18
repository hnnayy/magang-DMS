<?php

namespace App\Models;

use CodeIgniter\Model;

class UnitModel extends Model
{
    protected $table = 'unit';
    protected $primaryKey = 'id';
    protected $allowedFields = ['parent_id', 'name', 'status'];
    protected $returnType = 'array';
}

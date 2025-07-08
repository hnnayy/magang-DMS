<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table      = 'role';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'access_level', 'description', 'status'];
    protected $returnType = 'array';
}

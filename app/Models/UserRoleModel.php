<?php
namespace App\Models;

use CodeIgniter\Model;

class UserRoleModel extends Model
{
    protected $table = 'user_role';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'role_id', 'status', 'createdby', 'createddate'];
    protected $returnType = 'array';
}

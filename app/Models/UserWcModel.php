<?php

namespace App\Models;

use CodeIgniter\Model;

class UserWcModel extends Model
{
    protected $table = 'userWC'; // Sesuaikan nama tabel
    protected $primaryKey = 'id';
    protected $allowedFields = ['username', 'fullname']; // Sesuaikan field-nya
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class StandardModel extends Model
{
    protected $table = 'standards';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id','nama_standar'];
    protected $useTimestamps = true;

    
}

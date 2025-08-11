<?php

namespace App\Models;

use CodeIgniter\Model;

class StandardModel extends Model
{
    protected $table = 'standards';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nama_standar','description', 'status'];
    protected $useSoftDeletes = false; // Disabling soft deletes since deleted_at is removed
    protected $useTimestamps = false;
    protected $createdField  = null;
    protected $updatedField  = null;
}
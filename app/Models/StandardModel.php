<?php
namespace App\Models;

use CodeIgniter\Model;

class StandardModel extends Model
{
    protected $table = 'standards';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['nama_standar', 'description', 'status'];
    protected $useSoftDeletes = false;
    protected $useTimestamps = false;
    protected $createdField = null;
    protected $updatedField = null;

    // Get all active standards
    public function getActiveStandards()
    {
        return $this->where('status', 1)->orderBy('nama_standar', 'ASC')->findAll();
    }

    // Check if standard exists by name (only active standards)
    public function existsByName($name, $excludeId = null)
    {
        $builder = $this->where('nama_standar', $name)->where('status', 1);
        
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        
        return $builder->first() !== null;
    }

    // Get standard by ID (only active)
    public function getActiveById($id)
    {
        return $this->where('id', $id)->where('status', 1)->first();
    }
}
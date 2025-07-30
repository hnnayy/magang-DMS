<?php

namespace App\Models;

use CodeIgniter\Model;

class ClauseModel extends Model
{
    protected $table = 'clauses';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['id', 'standar_id', 'nomor_klausul', 'nama_klausul']; // Corrected to 'standar_id'
    protected $useTimestamps = true;

    public function getWithStandard()
    {
        return $this->select('clauses.*, standards.nama_standar')
                    ->join('standards', 'standards.id = clauses.standar_id', 'left') // Changed to 'standar_id'
                    ->findAll();
    }
}
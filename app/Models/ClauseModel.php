<?php

namespace App\Models;

use CodeIgniter\Model;

class ClauseModel extends Model
{
    protected $table = 'clauses';
    protected $primaryKey = 'id';
    protected $allowedFields = ['standar_id', 'nama_klausul', 'description', 'status'];
    protected $useTimestamps = false;

    // Override delete untuk soft delete dengan status
    public function delete($id = null, bool $purge = false)
    {
        if ($id) {
            $data = ['status' => 0]; // Tandai sebagai dihapus
            return $this->update($id, $data);
        }
        return parent::delete($id, $purge);
    }

    // Ambil hanya data yang aktif (status = 1)
    public function getWithStandard()
    {
        return $this->select('clauses.*, standards.nama_standar')
                    ->join('standards', 'standards.id = clauses.standar_id', 'left')
                    ->where('clauses.status', 1) // Ambil hanya data aktif
                    ->findAll();
    }
}
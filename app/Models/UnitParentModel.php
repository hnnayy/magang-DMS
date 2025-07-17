<?php

namespace App\Models;

use CodeIgniter\Model;

class UnitParentModel extends Model
{
    protected $table = 'unit_parent';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['type', 'name', 'status', 'created_at', 'updated_at'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validasi
    protected $validationRules = [
        'type'   => 'required|in_list[1,2]',
        'name'   => 'required|alpha_space|max_length[40]',
        'status' => 'required|in_list[1,2]',
    ];

    // Pesan validasi
    protected $validationMessages = [
        'type' => [
            'required' => 'Type harus dipilih.',
            'in_list'  => 'Type harus 1 (Directorate) atau 2 (Faculty).',
        ],
        'name' => [
            'required'  => 'Nama fakultas harus diisi.',
            'max_length' => 'Nama fakultas tidak boleh lebih dari 40 karakter.',
            'alpha_space' => 'Nama fakultas hanya boleh berisi huruf dan spasi.',
        ],
        'status' => [
            'required' => 'Status harus dipilih.',
            'in_list'  => 'Status harus 1 (Active) atau 2 (Inactive).',
        ]
    ];

    // Fungsi untuk soft delete (update status menjadi 0)
    public function softDelete($id)
    {
        return $this->update($id, [
            'status' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    // Fungsi untuk mengambil data yang tidak dihapus (status != 0)
    public function getActiveData()
    {
        return $this->where('status !=', 0)->findAll();
    }

    // Fungsi untuk mengambil data berdasarkan status
    public function getDataByStatus($status)
    {
        return $this->where('status', $status)->findAll();
    }
}
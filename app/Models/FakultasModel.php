<?php

namespace App\Models;

use CodeIgniter\Model;

class FakultasModel extends Model
{
    // Nama tabel yang digunakan
    protected $table      = 'fakultas';
    
    // Primary key
    protected $primaryKey = 'id';
    
    // Kolom yang bisa di-insert atau update
    protected $allowedFields = ['nama', 'level', 'status'];

    // Menggunakan timestamps untuk created_at dan updated_at
    protected $useTimestamps = true;

    // Kolom untuk timestamps
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Aturan validasi untuk setiap field
    protected $validationRules = [
        'nama'   => 'required|max_length[255]',  // Nama fakultas harus diisi dan tidak lebih dari 255 karakter
        'level'  => 'required|in_list[1,2]',     // Level harus 1 atau 2
        'status' => 'required|in_list[active,inactive]',  // Status harus active atau inactive
    ];

    // Pesan validasi kustom (opsional)
    protected $validationMessages = [
        'nama' => [
            'required' => 'Nama fakultas harus diisi.',
            'max_length' => 'Nama fakultas tidak boleh lebih dari 255 karakter.'
        ],
        'level' => [
            'required' => 'Level harus dipilih.',
            'in_list' => 'Level harus 1 (Directorate) atau 2 (Faculty).'
        ],
        'status' => [
            'required' => 'Status harus dipilih.',
            'in_list' => 'Status harus aktif atau tidak aktif.'
        ],
    ];
}

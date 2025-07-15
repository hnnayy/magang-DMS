<?php

namespace App\Models;
use CodeIgniter\Model;

class UnitParentModel extends Model
{
    // Nama tabel yang digunakan
    protected $table = 'unit_parent';
    
    // Primary key
    protected $primaryKey = 'id';
    
    // Tipe data yang dikembalikan (bisa array atau objek)
    protected $returnType = 'array';
    
    // Kolom yang bisa di-insert atau update
    protected $allowedFields = ['type', 'name', 'status'];
    
    // Menggunakan timestamps untuk created_at dan updated_at
    protected $useTimestamps = true;
    
    // Kolom untuk timestamps
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    
    /** ---------- Validasi otomatis ---------- */
    protected $validationRules = [
        'type'   => 'required|in_list[1,2]',        // 1 = Directorate, 2 = Faculty
        'name'   => 'required|alpha_space|max_length[40]',  // Nama hanya boleh huruf dan spasi
        'status' => 'required|in_list[1,2]',        // 1 = Active, 2 = Inactive
    ];

    // Pesan validasi kustom (opsional)
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

    /** ---------- Method untuk join dengan unit (fitur lama) ---------- */
    public function getWithUnits()
    {
        return $this->select('unit_parent.*, GROUP_CONCAT(unit.name) as units')
                    ->join('unit', 'unit.parent_id = unit_parent.id', 'left')
                    ->groupBy('unit_parent.id')
                    ->findAll();
    }
}

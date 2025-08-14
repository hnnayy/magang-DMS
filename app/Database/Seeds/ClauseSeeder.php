<?php
namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ClauseSeeder extends Seeder
{
    public function run()
    {
        // Kosongkan tabel clauses
        $this->db->table('clauses')->truncate();

        $data = [
            [
                'standar_id'   => 1, // Pastikan standar_id ada di tabel standards
                'nama_klausul' => '7.1',
                'description'  => 'Resources',
                'status'       => 1
            ],
            [
                'standar_id'   => 1,
                'nama_klausul' => '8.2.2',
                'description'  => 'Determining the requirements',
                'status'       => 1
            ],
            [
                'standar_id'   => 2, // Ubah dari 10 ke 2 (atau nilai lain yang valid)
                'nama_klausul' => '7.1 tambah baruxx',
                'description'  => 'ini baruxx',
                'status'       => 1
            ]
        ];

        $this->db->table('clauses')->insertBatch($data);
    }
}
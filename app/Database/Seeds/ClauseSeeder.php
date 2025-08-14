<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ClausesSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'id' => 1,
                'standar_id' => 1,
                'nama_klausul' => '7.1',
                'description' => 'Resources',
                'status' => 1
            ],
            [
                'id' => 2,
                'standar_id' => 1,
                'nama_klausul' => '8.2.2',
                'description' => 'Determining the requirements',
                'status' => 1
            ],
            [
                'id' => 4,
                'standar_id' => 10,
                'nama_klausul' => '7.1 tambah baruxx',
                'description' => 'ini baruxx',
                'status' => 1
            ]
        ];

        $this->db->table('clauses')->insertBatch($data);
    }
}

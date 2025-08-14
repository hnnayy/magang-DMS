<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class StandardsSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'id' => 1,
                'nama_standar' => 'ISO 9001',
                'description' => null,
                'status' => 1
            ],
            [
                'id' => 3,
                'nama_standar' => 'tambah baru',
                'description' => null,
                'status' => 1
            ],
            [
                'id' => 4,
                'nama_standar' => 'sapi',
                'description' => null,
                'status' => 0
            ],
            [
                'id' => 5,
                'nama_standar' => 'standar baru 2',
                'description' => 'deskripsi uv',
                'status' => 0
            ]
        ];

        $this->db->table('standards')->insertBatch($data);
    }
}

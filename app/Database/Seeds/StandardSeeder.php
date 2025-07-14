<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class StandardSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['nama_standar' => 'ISO 9001'],
            ['nama_standar' => 'ISO 27001'],
        ];

        $this->db->table('standards')->insertBatch($data);
    }
}

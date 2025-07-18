<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'id'          => 99,
                'unit_id'     => 1,
                'username'    => 'budi',
                'fullname'    => 'Budi Santoso',
                'status'      => 1,
                'createddate' => '2025-07-15 14:31:15',
                'createdby'   => 1,
            ],
        ];

        // Simple batch insert
        $this->db->table('user')->insertBatch($data);
    }
}

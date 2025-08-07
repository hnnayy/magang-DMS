<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'id'           => 1,
                'user_id'      => 1,
                'role_id'      => 1,
                'status'       => 1,
                'createddate'  => '2025-07-17 17:02:54',
                'createdby'    => 1,
            ],
            [
                'id'           => 2,
                'user_id'      => 2,
                'role_id'      => 2,
                'status'       => 1,
                'createddate'  => '2025-07-17 17:02:54',
                'createdby'    => 1,
            ],
        ];

        // Masukkan ke tabel
        $this->db->table('user_role')->insertBatch($data);
    }
}

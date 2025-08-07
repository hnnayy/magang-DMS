<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'id'            => 1,
                'name'          => 'admin_super',
                'access_level'  => 1,
                'description'   => null,
                'status'        => 1
            ],
            [
                'id'            => 2,
                'name'          => 'staff',
                'access_level'  => 2,
                'description'   => null,
                'status'        => 1
            ],
        ];

        $this->db->table('role')->insertBatch($data);
    }
}

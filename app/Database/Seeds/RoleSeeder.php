<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'id'            => 99,
                'name'          => 'admin_super',
                'access_level'  => 0,
                'description'   => null,
                'status'        => 1
            ],
        ];

        $this->db->table('role')->insertBatch($data);
    }
}

<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserWCSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'id'          => 1,
                'unit_id'     => 1,
                'username'    => 'budi',
                'fullname'    => 'Budi Santoso',
                'status'      => 1,
                'createddate' => '2025-07-15 14:31:15',
                'createdby'   => 1,
            ],
            [
                'id'          => 2,
                'unit_id'     => 1,
                'username'    => 'staff',
                'fullname'    => 'staff Santoso',
                'status'      => 1,
                'createddate' => '2025-07-15 14:31:15',
                'createdby'   => 1,
            ],
        ];        

        $this->db->table('userWC')->insertBatch($data);
    }
}

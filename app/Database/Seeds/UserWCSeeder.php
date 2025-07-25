<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserWCSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'id'         => 6,
                'unit_id'    => 2,
                'username'   => 'haninayy',
                'fullname'   => 'Hanin Ayy',
                'status'     => 1,
                'createddate'=> '2025-07-14 08:29:07',
                'createdby'  => 5
            ],
            [
                'id'         => 7,
                'unit_id'    => 2,
                'username'   => 'karina',
                'fullname'   => 'Karina Pretty',
                'status'     => 1,
                'createddate'=> '2025-07-15 02:25:35',
                'createdby'  => 4
            ],
            [
                'id'         => 9,
                'unit_id'    => 2,
                'username'   => 'budi',
                'fullname'   => 'Budi Santoso',
                'status'     => 1,
                'createddate'=> '2025-07-15 14:31:15',
                'createdby'  => 3
            ],
            [
                'id'         => 10,
                'unit_id'    => 100,
                'username'   => 'staff',
                'fullname'   => 'ini staff',
                'status'     => 1,
                'createddate'=> '2025-07-18 11:14:19',
                'createdby'  => 99
            ],
            [
                'id'         => 12,
                'unit_id'    => 2,
                'username'   => 'user',
                'fullname'   => 'user baru',
                'status'     => 1,
                'createddate'=> '2025-07-21 10:15:39',
                'createdby'  => 99
            ],
        ];

        $this->db->table('uservc')->insertBatch($data);
    }
}

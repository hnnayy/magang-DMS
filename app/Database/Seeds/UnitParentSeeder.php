<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UnitParentSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'id'     => 1,
                'type'   => 2, // 2 = Faculty
                'name'   => 'Fakultas Teknik Elektro',
                'status' => 1  // 1 = Active
            ],
        ];

        $this->db->table('unit_parent')->insertBatch($data);
    }
}

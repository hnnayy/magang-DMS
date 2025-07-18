<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'id'        => 1,
                'parent_id' => 2,
                'name'      => 'Teknik Komputer',
                'status'    => 1
            ],
        ];

        $this->db->table('unit')->insertBatch($data);
    }
}

<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PrivilegeSeeder extends Seeder
{
    public function run()
    {
        // Ambil semua ID submenu dari tabel submenu
        $submenuIds = range(1, 23); // sesuaikan dengan jumlah submenu yang kamu punya

        $data = [];

        // Role 1: semua akses = 1
        foreach ($submenuIds as $id) {
            $data[] = [
                'role_id'     => 1,
                'submenu_id'  => $id,
                'can_create'  => 1,
                'can_update'  => 1,
                'can_delete'  => 1,
                'can_approve' => 1,
            ];
        }

        // Role 2: semua akses = 0
        foreach ($submenuIds as $id) {
            $data[] = [
                'role_id'     => 2,
                'submenu_id'  => $id,
                'can_create'  => 0,
                'can_update'  => 0,
                'can_delete'  => 0,
                'can_approve' => 0,
            ];
        }

        // Insert batch
        $this->db->table('privilege')->insertBatch($data);
    }
}

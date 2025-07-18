<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PrivilegeSeeder extends Seeder
{
    public function run()
    {
        $data = [
            // role_id = 99
            ['id' => 4,  'role_id' => 99, 'submenu_id' => 1,   'can_create' => 1, 'can_update' => 1, 'can_delete' => 1, 'can_approve' => 1],
            ['id' => 5,  'role_id' => 99, 'submenu_id' => 101, 'can_create' => 1, 'can_update' => 1, 'can_delete' => 1, 'can_approve' => 1],
            ['id' => 6,  'role_id' => 99, 'submenu_id' => 201, 'can_create' => 1, 'can_update' => 1, 'can_delete' => 1, 'can_approve' => 1],
            ['id' => 7,  'role_id' => 99, 'submenu_id' => 202, 'can_create' => 1, 'can_update' => 1, 'can_delete' => 1, 'can_approve' => 1],
            ['id' => 8,  'role_id' => 99, 'submenu_id' => 301, 'can_create' => 1, 'can_update' => 1, 'can_delete' => 1, 'can_approve' => 1],
            ['id' => 9,  'role_id' => 99, 'submenu_id' => 302, 'can_create' => 1, 'can_update' => 1, 'can_delete' => 1, 'can_approve' => 1],
            ['id' => 10, 'role_id' => 99, 'submenu_id' => 401, 'can_create' => 1, 'can_update' => 1, 'can_delete' => 1, 'can_approve' => 1],
            ['id' => 11, 'role_id' => 99, 'submenu_id' => 402, 'can_create' => 1, 'can_update' => 1, 'can_delete' => 1, 'can_approve' => 1],
            ['id' => 12, 'role_id' => 99, 'submenu_id' => 403, 'can_create' => 1, 'can_update' => 1, 'can_delete' => 1, 'can_approve' => 1],
            ['id' => 13, 'role_id' => 99, 'submenu_id' => 404, 'can_create' => 1, 'can_update' => 1, 'can_delete' => 1, 'can_approve' => 1],
            ['id' => 14, 'role_id' => 99, 'submenu_id' => 501, 'can_create' => 1, 'can_update' => 1, 'can_delete' => 1, 'can_approve' => 1],
            ['id' => 15, 'role_id' => 99, 'submenu_id' => 601, 'can_create' => 1, 'can_update' => 1, 'can_delete' => 1, 'can_approve' => 1],
            ['id' => 16, 'role_id' => 99, 'submenu_id' => 602, 'can_create' => 1, 'can_update' => 1, 'can_delete' => 1, 'can_approve' => 1],
            ['id' => 17, 'role_id' => 99, 'submenu_id' => 701, 'can_create' => 1, 'can_update' => 1, 'can_delete' => 1, 'can_approve' => 1],
            ['id' => 18, 'role_id' => 99, 'submenu_id' => 702, 'can_create' => 1, 'can_update' => 1, 'can_delete' => 1, 'can_approve' => 1],
            ['id' => 19, 'role_id' => 99, 'submenu_id' => 801, 'can_create' => 1, 'can_update' => 1, 'can_delete' => 1, 'can_approve' => 1],
            ['id' => 20, 'role_id' => 99, 'submenu_id' => 802, 'can_create' => 1, 'can_update' => 1, 'can_delete' => 1, 'can_approve' => 1],
            ['id' => 21, 'role_id' => 99, 'submenu_id' => 901, 'can_create' => 1, 'can_update' => 1, 'can_delete' => 1, 'can_approve' => 1],
            ['id' => 22, 'role_id' => 99, 'submenu_id' => 902, 'can_create' => 1, 'can_update' => 1, 'can_delete' => 1, 'can_approve' => 1]
        ];

        $this->db->table('privilege')->insertBatch($data);
    }
}

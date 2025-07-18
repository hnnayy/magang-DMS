<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'id'     => 1,
                'name'   => 'Dashboard',
                'icon'   => 'fi fi-rr-dashboard',
                'status' => 1,
            ],
            [
                'id'     => 2,
                'name'   => 'Create User',
                'icon'   => 'fi fi-rr-user-add',
                'status' => 1,
            ],
            [
                'id'     => 3,
                'name'   => 'Master Data',
                'icon'   => 'fi fi-rr-database',
                'status' => 1,
            ],
            [
                'id'     => 4,
                'name'   => 'Document Management',
                'icon'   => 'fi fi-rr-document',
                'status' => 1,
            ],
            [
                'id'     => 5,
                'name'   => 'Daftar Dokumen',
                'icon'   => 'fi fi-rr-list',
                'status' => 1,
            ],
            [
                'id'     => 6,
                'name'   => 'Menu',
                'icon'   => 'fi fi-rr-apps',
                'status' => 1,
            ],
            [
                'id'     => 7,
                'name'   => 'Sub Menu',
                'icon'   => 'fi fi-rr-menu-burger',
                'status' => 1,
            ],
            [
                'id'     => 8,
                'name'   => 'Role',
                'icon'   => 'fi fi-rr-shield-check',
                'status' => 1,
            ],
            [
                'id'     => 9,
                'name'   => 'Privilege',
                'icon'   => 'fi fi-rr-lock',
                'status' => 1,
            ],
            [
                'id'     => 10,
                'name'   => 'Kelola Dokumen',
                'icon'   => 'fi fi-rr-document',
                'status' => 1,
            ],
            [
                'id'     => 11,
                'name'   => 'Kelola Dokumen',
                'icon'   => 'fi fi-rr-document',
                'status' => 1,
            ],
        ];

        // Insert batch
        $this->db->table('menu')->insertBatch($data);
    }
}

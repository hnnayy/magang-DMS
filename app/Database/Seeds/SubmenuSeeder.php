<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SubmenuSeeder extends Seeder
{
    public function run()
    {
        $data = [
            // Menu ID 2: Create User
            ['id' => 201, 'parent' => 2, 'name' => 'Tambah User', 'status' => 1],
            ['id' => 202, 'parent' => 2, 'name' => 'Lihat User', 'status' => 1],

            // Menu ID 3: Master Data
            ['id' => 301, 'parent' => 3, 'name' => 'Tambah Unit', 'status' => 1],
            ['id' => 302, 'parent' => 3, 'name' => 'Lihat Unit', 'status' => 1],

            // Menu ID 4: Document Management
            ['id' => 401, 'parent' => 4, 'name' => 'Tambah Dokumen', 'status' => 1],
            ['id' => 402, 'parent' => 4, 'name' => 'Daftar Pengajuan', 'status' => 1],
            ['id' => 403, 'parent' => 4, 'name' => 'Persetujuan Dokumen', 'status' => 1],
            ['id' => 404, 'parent' => 4, 'name' => 'Jenis & Kode Dokumen', 'status' => 1],

            // Menu ID 5: Daftar Dokumen
            ['id' => 501, 'parent' => 5, 'name' => 'Daftar Dokumen', 'status' => 1],

            // Menu ID 6: Menu
            ['id' => 601, 'parent' => 6, 'name' => 'Tambah Menu', 'status' => 1],
            ['id' => 602, 'parent' => 6, 'name' => 'Lihat Menu', 'status' => 1],

            // Menu ID 7: Sub Menu
            ['id' => 701, 'parent' => 7, 'name' => 'Tambah Submenu', 'status' => 1],
            ['id' => 702, 'parent' => 7, 'name' => 'Lihat Submenu', 'status' => 1],

            // Menu ID 8: Role
            ['id' => 801, 'parent' => 8, 'name' => 'Tambah Role', 'status' => 1],
            ['id' => 802, 'parent' => 8, 'name' => 'Lihat Role', 'status' => 1],

            // Menu ID 9: Privilege
            ['id' => 901, 'parent' => 9, 'name' => 'Tambah Privilege', 'status' => 1],
            ['id' => 902, 'parent' => 9, 'name' => 'Lihat Privilege', 'status' => 1],
        ];

        $this->db->table('submenu')->insertBatch($data);
    }
}

<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SubmenuSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['id' => 2, 'parent' => 1, 'name' => 'Create User', 'status' => 1],
            ['id' => 3, 'parent' => 1, 'name' => 'User List', 'status' => 1],
            ['id' => 301, 'parent' => 3, 'name' => 'Create Unit', 'status' => 1],
            ['id' => 302, 'parent' => 3, 'name' => 'Unit List', 'status' => 1],
            ['id' => 401, 'parent' => 4, 'name' => 'Create Document', 'status' => 1],
            ['id' => 402, 'parent' => 4, 'name' => 'Document Submission List', 'status' => 1],
            ['id' => 403, 'parent' => 4, 'name' => 'Document Approval', 'status' => 1],
            ['id' => 404, 'parent' => 4, 'name' => 'Document Type & Code', 'status' => 1],
            ['id' => 501, 'parent' => 5, 'name' => 'Document List', 'status' => 1],
            ['id' => 601, 'parent' => 6, 'name' => 'Create Menu', 'status' => 1],
            ['id' => 602, 'parent' => 6, 'name' => 'Menu List', 'status' => 1],
            ['id' => 701, 'parent' => 7, 'name' => 'Create Submenu', 'status' => 1],
            ['id' => 702, 'parent' => 7, 'name' => 'Submenu List', 'status' => 1],
            ['id' => 801, 'parent' => 8, 'name' => 'Create Role', 'status' => 1],
            ['id' => 802, 'parent' => 8, 'name' => 'Role List', 'status' => 1],
            ['id' => 901, 'parent' => 9, 'name' => 'Create Privilege', 'status' => 1],
            ['id' => 902, 'parent' => 9, 'name' => 'Privilege List', 'status' => 1],
            ['id' => 903, 'parent' => 3, 'name' => 'Create Faculty', 'status' => 1],
            ['id' => 904, 'parent' => 3, 'name' => 'Faculty List', 'status' => 1],
            ['id' => 905, 'parent' => 3, 'name' => 'Document Type & Code', 'status' => 1],
            ['id' => 906, 'parent' => 13, 'name' => 'Monev Dashboard', 'status' => 1],
            ['id' => 907, 'parent' => 13, 'name' => 'm m', 'status' => 1],
            ['id' => 908, 'parent' => 3, 'name' => 'List Kebunku', 'status' => 1],
            ['id' => 909, 'parent' => 15, 'name' => 'Create Document', 'status' => 1],
            ['id' => 910, 'parent' => 3, 'name' => 'ssssc ssssss', 'status' => 0],
        ];

        $this->db->table('submenu')->insertBatch($data);
    }
}

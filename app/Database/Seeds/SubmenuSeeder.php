<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SubmenuSeeder extends Seeder
{
    public function run()
    {
        $data = [
            // parent = 2 (User Management)
            ['id' => 1,  'parent' => 2,  'name' => 'Create User',              'status' => 1],
            ['id' => 2,  'parent' => 2,  'name' => 'User List',                'status' => 1],

            // parent = 3 (Master Data)
            ['id' => 3,  'parent' => 3,  'name' => 'Create Unit',              'status' => 1],
            ['id' => 4,  'parent' => 3,  'name' => 'Unit List',                'status' => 1],
            ['id' => 5,  'parent' => 3,  'name' => 'Create Faculty',           'status' => 1],
            ['id' => 6,  'parent' => 3,  'name' => 'Faculty List',             'status' => 1],
            ['id' => 7,  'parent' => 3,  'name' => 'Document Type',             'status' => 1],
            ['id' => 8,  'parent' => 3,  'name' => 'Document Code',             'status' => 1],

            // parent = 4 (Document Management)
            ['id' => 10, 'parent' => 4,  'name' => 'Create Document',          'status' => 1],
            ['id' => 11, 'parent' => 4,  'name' => 'Document Submission List', 'status' => 1],
            ['id' => 12, 'parent' => 4,  'name' => 'Document Approval',        'status' => 1],

            // parent = 5 (Document List)
            ['id' => 14, 'parent' => 5,  'name' => 'Document List',            'status' => 1],

            // parent = 6 (Menu)
            ['id' => 15, 'parent' => 6,  'name' => 'Create Menu',              'status' => 1],
            ['id' => 16, 'parent' => 6,  'name' => 'Menu List',                'status' => 1],

            // parent = 7 (Sub Menu)
            ['id' => 17, 'parent' => 7,  'name' => 'Create Submenu',           'status' => 1],
            ['id' => 18, 'parent' => 7,  'name' => 'Submenu List',             'status' => 1],

            // parent = 8 (Role)
            ['id' => 19, 'parent' => 8,  'name' => 'Create Role',              'status' => 1],
            ['id' => 20, 'parent' => 8,  'name' => 'Role List',                'status' => 1],

            // parent = 9 (Privilege)
            ['id' => 21, 'parent' => 9,  'name' => 'Create Privilege',         'status' => 1],
            ['id' => 22, 'parent' => 9,  'name' => 'Privilege List',           'status' => 1],

            // parent = 13 (Monev Dashboard)
            ['id' => 23, 'parent' => 13, 'name' => 'Monev Dashboard',          'status' => 1],

        ];

        $this->db->table('submenu')->insertBatch($data);
    }
}

<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call(StandardSeeder::class);
        $this->call(ClauseSeeder::class);
        $this->call(MenuSeeder::class);
        $this->call(SubmenuSeeder::class);
        $this->call(PrivilegeSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(UserRoleSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(UnitParentSeeder::class);
        $this->call(UnitSeeder::class);

    }
}

<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call(StandardSeeder::class);
        $this->call(ClauseSeeder::class);
    }
}

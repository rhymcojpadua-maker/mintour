<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleUsersSeeder::class,       // admin + demo users
            VictoriaDemoSeeder::class,    // the entire realistic Victoria dataset
            VictoriaPortalSeeder::class,  // official tourism portal listings
        ]);
    }
}

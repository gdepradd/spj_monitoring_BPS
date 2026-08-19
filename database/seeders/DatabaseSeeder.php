<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            StatusPengajuanSeeder::class,
            StatusVerifikasiSeeder::class,
            StatusPencairanSeeder::class,
            UserSeeder::class,
        ]);
    }
}

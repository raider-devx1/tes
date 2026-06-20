<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Panggil UserSeeder agar dieksekusi saat migrate:fresh --seed
        $this->call([
            UserSeeder::class,
           
        ]);
    }
}
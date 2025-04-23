<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // On appelle les seeders Ville et Quartier
        $this->call([
            VilleSeeder::class,
            QuartierSeeder::class,
        ]);
    }
}

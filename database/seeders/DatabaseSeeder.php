<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
// Imports...
use Database\Seeders\TypeLogementSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            VilleSeeder::class,
            QuartierSeeder::class,
            TypeLogementSeeder::class, // <-- ICI
            UserSeeder::class,
            LogementSeeder::class,
            // ...
        ]);
    }
}

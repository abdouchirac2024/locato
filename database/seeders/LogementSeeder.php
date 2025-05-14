<?php

namespace Database\Seeders;

use App\Models\Logement;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LogementSeeder extends Seeder
{
    public function run(): void
    {
        // Crée 25 logements de test
        $this->command->info("Seeding Logements...");
        Logement::factory()->count(25)->create();
         $this->command->info("Logements seeded.");
    }
}

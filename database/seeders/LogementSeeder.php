<?php

namespace Database\Seeders;

use App\Models\Logement;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LogementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crée 20 logements de test en utilisant la factory
        // Assurez-vous que les seeders pour TypeLogement, Quartier, Bailleur
        // sont exécutés avant celui-ci dans DatabaseSeeder.php
        Logement::factory()->count(20)->create();

         // Vous pouvez aussi créer des logements spécifiques ici si besoin
         // Logement::create([...]);
    }
}

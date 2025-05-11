<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ville;

class VilleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Création manuelle des villes
        $villes = [
            ['nomVille' => 'Douala'],
            ['nomVille' => 'Yaoundé'],
            ['nomVille' => 'Bafoussam'],
            ['nomVille' => 'Garoua'],
            ['nomVille' => 'Bertoua'],
        ];

        foreach ($villes as $ville) {
            Ville::create($ville);
        }
    }
}

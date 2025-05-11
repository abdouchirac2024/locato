<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Quartier;
use App\Models\Ville;

class QuartierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupération des villes créées
        $douala = Ville::where('nomVille', 'Douala')->first();
        $yaounde = Ville::where('nomVille', 'Yaoundé')->first();

        // Création des quartiers associés aux villes
        $quartiers = [
            ['nomQuartier' => 'Bonamoussadi', 'villeId' => $douala->id],
            ['nomQuartier' => 'Akwa', 'villeId' => $douala->id],
            ['nomQuartier' => 'Ndogbong', 'villeId' => $douala->id],
            ['nomQuartier' => 'Mvog-Ada', 'villeId' => $yaounde->id],
            ['nomQuartier' => 'Essos', 'villeId' => $yaounde->id],
        ];

        foreach ($quartiers as $quartier) {
            Quartier::create($quartier);
        }
    }
}

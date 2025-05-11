<?php

namespace Database\Factories;

use App\Models\Bailleur;
use App\Models\Logement;
use App\Models\Quartier;
use App\Models\TypeLogement;
use Illuminate\Database\Eloquent\Factories\Factory;

class LogementFactory extends Factory
{
    protected $model = Logement::class;

    public function definition(): array
    {
        // Création ou récupération des entités associées
        $typeLogement = TypeLogement::inRandomOrder()->first() ?? TypeLogement::factory()->create();
        $quartier = Quartier::inRandomOrder()->first() ?? Quartier::factory()->create();
        $bailleur = Bailleur::inRandomOrder()->first() ?? Bailleur::factory()->create();

        // Génération aléatoire pour le statut de disponibilité
        $dispo_fr = $this->faker->randomElement(['LIBRE', 'OCCUPE']);

        return [
            'libelle' => $this->faker->words(3, true), // Ex: "Joli studio meublé"
            'nbrMois' => $this->faker->numberBetween(1, 6),
            'prix' => $this->faker->randomFloat(2, 50000, 500000), // Prix avec deux décimales
            'nbrpieces' => $this->faker->numberBetween(1, 8),
            'descrip_fr' => $this->faker->realText(200), // Génère une description réaliste
            'descrip_en' => $this->faker->realText(200), // Description en anglais, ajoutée pour le multilingue
            'contPrep' => $this->faker->boolean(60), // 60% de chance d'être true
            'forage' => $this->faker->boolean(20), // 20% de chance d'être true
            'parking' => $this->faker->boolean(75), // 75% de chance d'être true
            'gardien' => $this->faker->boolean(40), // 40% de chance d'être true
            'dispo_fr' => $dispo_fr, // 'LIBRE' ou 'OCCUPE'
            'dispo_en' => $dispo_fr == 'LIBRE' ? 'FREE' : 'RENTED', // Traduction basée sur la dispo_fr
            'typLogId' => $typeLogement->id,
            'quartierId' => $quartier->id,
            'bailId' => $bailleur->id, // ID du bailleur
        ];
    }
}

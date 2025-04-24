<?php

namespace Database\Factories;

use App\Models\Bailleur;
use App\Models\Logement;
use App\Models\Quartier;
use App\Models\TypeLogement; // Assurez-vous que ce modèle et sa factory existent
use Illuminate\Database\Eloquent\Factories\Factory;

class LogementFactory extends Factory
{
    protected $model = Logement::class;

    public function definition(): array
    {
        // Assurez-vous que des enregistrements existent ou créez-les via factory
        $typeLogement = TypeLogement::inRandomOrder()->first() ?? TypeLogement::factory()->create();
        $quartier = Quartier::inRandomOrder()->first() ?? Quartier::factory()->create();
        // Important: Assurez-vous que BailleurFactory crée un User associé
        $bailleur = Bailleur::inRandomOrder()->first() ?? Bailleur::factory()->create();

        $dispo_fr = $this->faker->randomElement(['LIBRE', 'OCCUPE']);

        return [
            'libelle' => $this->faker->words(3, true), // Ex: "Joli studio meublé"
            'nbrMois' => $this->faker->numberBetween(1, 6),
            'prix' => $this->faker->randomFloat(0, 50000, 500000), // Prix sans décimales
            'nbrpieces' => $this->faker->numberBetween(1, 8),
            'descrip_fr' => $this->faker->realText(200), // Génère une description en français réaliste
            // 'descrip_en' sera généré automatiquement par le modèle
            'contPrep' => $this->faker->boolean(60), // 60% de chance d'être true
            'forage' => $this->faker->boolean(20),   // 20% de chance d'être true
            'parking' => $this->faker->boolean(75),  // 75% de chance d'être true
            'gardien' => $this->faker->boolean(40),  // 40% de chance d'être true
            'dispo_fr' => $dispo_fr, // 'LIBRE' ou 'OCCUPE'
            // 'dispo_en' sera généré automatiquement par le modèle
            'typLogId' => $typeLogement->id,
            'quartierId' => $quartier->id,
            'bailId' => $bailleur->id, // ID du bailleur créé ou récupéré
        ];
    }
}

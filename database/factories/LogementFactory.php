<?php

namespace Database\Factories;

use App\Models\Bailleur;
use App\Models\Logement;
use App\Models\Quartier;
use App\Models\TypeLogement;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str; // Pour Str::random

class LogementFactory extends Factory
{
    protected $model = Logement::class;

    public function definition(): array
    {
        $typeLogement = TypeLogement::inRandomOrder()->first() ?? TypeLogement::factory()->create();
        $quartier = Quartier::inRandomOrder()->first() ?? Quartier::factory()->create();
        $bailleur = Bailleur::inRandomOrder()->first() ?? Bailleur::factory()->create();
        $dispo_fr = $this->faker->randomElement(['LIBRE', 'OCCUPE']);

        return [
            'reference' => 'LOG-' . strtoupper(Str::random(8)),
            'libelle' => $this->faker->sentence(4), // Génère un libellé FR
            // 'libelle_en' => null, // Laissé null pour traduction auto (si colonne existe)
            'latitude' => $this->faker->latitude(4.0, 4.1), // Exemple pour Douala/Yaoundé
            'longitude' => $this->faker->longitude(9.6, 11.6), // Exemple pour Douala/Yaoundé
            'nbrpieces' => $this->faker->numberBetween(1, 10),
            'superficie' => $this->faker->numberBetween(20, 300),
            'nbr_salles_bain' => $this->faker->numberBetween(1, 4),
            'nbr_chambres' => $this->faker->numberBetween(1, 6),
            'climatisation' => $this->faker->boolean(30),
            'meuble' => $this->faker->boolean(50),
            'adresse' => $this->faker->streetAddress,
            'prix' => $this->faker->randomFloat(0, 50000, 1000000), // Prix sans décimales
            'nbrMois' => $this->faker->numberBetween(1, 6),
            'descrip_fr' => $this->faker->realText(300), // Description FR
            // 'descrip_en' sera généré par le modèle
            'contPrep' => $this->faker->boolean(60),
            'forage' => $this->faker->boolean(20),
            'parking' => $this->faker->boolean(75),
            'gardien' => $this->faker->boolean(40),
            'dispo_fr' => $dispo_fr,
            // 'dispo_en' sera généré par le modèle
            'typLogId' => $typeLogement->id,
            'quartierId' => $quartier->id,
            'bailId' => $bailleur->id,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\TypeLogement; // Importe le modèle TypeLogement
use Illuminate\Database\Eloquent\Factories\Factory;

class TypeLogementFactory extends Factory
{
    /**
     * Le nom du modèle correspondant à la factory.
     *
     * @var string
     */
    protected $model = TypeLogement::class; // Lie cette factory au modèle TypeLogement

    /**
     * Définit l'état par défaut du modèle.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'libelle_logement' => $this->faker->word(), // Génère un libellé aléatoire en français
            'libelle_logement_en' => $this->faker->word(), // Génère un libellé aléatoire en anglais
            'standing' => $this->faker->randomElement(['Luxe', 'Haut Standing', 'Moyen Standing', 'Economique', 'Social']), // Génère un type de standing aléatoire
            'standing_en' => $this->faker->randomElement(['Luxury', 'High Standing', 'Medium Standing', 'Economical', 'Social']), // Génère une traduction aléatoire en anglais pour le standing
        ];
    }
}

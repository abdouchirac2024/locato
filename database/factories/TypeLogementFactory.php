<?php
namespace Database\Factories;

use App\Models\TypeLogement;
use Illuminate\Database\Eloquent\Factories\Factory;

class TypeLogementFactory extends Factory
{
    protected $model = TypeLogement::class;

    public function definition(): array
    {
        return [
            'libelle_logement' => $this->faker->unique()->word() . $this->faker->randomElement([' Moderne', ' Classique', ' Familial', ' de Luxe', ' Économique']),
            'standing' => $this->faker->optional(0.7)->randomElement(['Haut Standing', 'Moyen Standing', 'Standard', 'Basique']), // 70% de chance d'avoir une valeur, sinon null
            // Les champs _en seront générés par le modèle
        ];
    }
}

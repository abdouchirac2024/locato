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
        // La seule colonne à remplir est 'Standing' selon votre migration
        return [
            'Standing' => $this->faker->randomElement(['Luxe', 'Haut Standing', 'Moyen Standing', 'Economique', 'Social']), // Génère un type de standing aléatoire parmi une liste prédéfinie
            // Si vous aviez d'autres colonnes dans TypeLogement, définissez-les ici.
        ];
    }
}

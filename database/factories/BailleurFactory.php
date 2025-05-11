<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BailleurFactory extends Factory
{
    /**
     * Le nom du modèle correspondant à la factory.
     *
     * @var string
     */
    protected $model = \App\Models\Bailleur::class; // Spécifie le modèle

    /**
     * Définit l'état par défaut du modèle.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory()->bailleur(), // Génère un utilisateur associé
            'verif' => $this->faker->boolean(), // Génère un booléen pour la vérification
            'numFiscal' => $this->faker->numerify('##########'), // Génère un numéro fiscal
            'description_fr' => $this->faker->paragraph(), // Description en français
            'description_en' => $this->faker->paragraph(), // Description en anglais
            'nbrLog' => $this->faker->numberBetween(1, 10), // Nombre de logements
            // Gestion des statuts
            'statut_fr' => $this->faker->randomElement(['en_attente', 'verifie']), // Statut en français
            'statut_en' => $this->faker->randomElement(['pending', 'verified']), // Statut en anglais
        ];
    }
}

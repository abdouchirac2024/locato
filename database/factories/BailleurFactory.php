<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BailleurFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory()->bailleur(),
            'verif' => $this->faker->boolean(),
            'numFiscal' => $this->faker->numerify('##########'),
            'description_fr' => $this->faker->paragraph(),
            'nbrLog' => $this->faker->numberBetween(1, 10),
        ];
    }
}

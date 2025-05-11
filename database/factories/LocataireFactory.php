<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LocataireFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory()->locataire(),
            'preference' => $this->faker->word(),
            'nrbvist' => $this->faker->numberBetween(0, 20),
        ];
    }
}

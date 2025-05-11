<?php

namespace Database\Factories;

use App\Models\Quartier;
use App\Models\Ville;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuartierFactory extends Factory
{
    protected $model = Quartier::class;

    public function definition(): array
    {
        return [
            'nomQuartier' => $this->faker->streetName,
            'villeId' => Ville::inRandomOrder()->first()->id ?? Ville::factory(),
        ];
    }
}

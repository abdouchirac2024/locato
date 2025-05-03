<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'prenom' => $this->faker->firstName(),
            'matricule' => strtoupper(Str::random(7)),
            'telephone' => $this->faker->unique()->phoneNumber(),
            'photoProfile' => null,
            'cni' => null,
            'role' => $this->faker->randomElement(['Locataire', 'Bailleur']),
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
            'quartier_id' => \App\Models\Quartier::factory(),
        ];
    }

    public function admin(): static
    {
        return $this->state([
            'role' => 'ADMIN',
            'email' => 'helpdigi35@gmail.com',
        ]);
    }

    public function bailleur(): static
    {
        return $this->state([
            'role' => 'Bailleur',
        ]);
    }

    public function locataire(): static
    {
        return $this->state([
            'role' => 'Locataire',
        ]);
    }

    public function unverified(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
                'verification_code' => rand(1000, 9999),
            ];
        });
    }
}

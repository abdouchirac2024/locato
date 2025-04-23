<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Créer un admin
        User::factory()->admin()->create();

        // Créer 5 bailleurs
        User::factory()->count(5)->bailleur()->create();

        // Créer 10 locataires
        User::factory()->count(10)->locataire()->create();
    }
}

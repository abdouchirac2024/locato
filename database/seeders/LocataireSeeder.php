<?php

namespace Database\Seeders;

use App\Models\Locataire;
use Illuminate\Database\Seeder;

class LocataireSeeder extends Seeder
{
    public function run(): void
    {
        Locataire::factory()->count(10)->create();
    }
}

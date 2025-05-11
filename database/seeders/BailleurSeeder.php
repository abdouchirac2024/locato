<?php

namespace Database\Seeders;

use App\Models\Bailleur;
use Illuminate\Database\Seeder;

class BailleurSeeder extends Seeder
{
    public function run(): void
    {
        Bailleur::factory()->count(5)->create();
    }
}

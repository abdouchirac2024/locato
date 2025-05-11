<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            VilleSeeder::class,
            QuartierSeeder::class,
            // Assurez-vous que TypeLogementSeeder est appelé s'il existe
            // TypeLogementSeeder::class,
            UserSeeder::class, // Crée Users (y compris Bailleurs et Locataires avec leurs profils)
            // Les seeders BailleurSeeder et LocataireSeeder ne sont plus forcément nécessaires
            // si UserSeeder gère la création des profils associés via les factories User/Bailleur/Locataire.
            // Vérifiez vos factories User/Bailleur/Locataire.

            LogementSeeder::class, // **Ajoutez cette ligne**

            // ... autres seeders (AvisSeeder, VisiteSeeder, etc. si vous en avez) ...
        ]);
    }
}

<?php
namespace Database\Seeders;

use App\Models\TypeLogement;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TypeLogementSeeder extends Seeder
{
    public function run(): void
    {
        if (TypeLogement::count() == 0) {
             $types = [
                 ['libelle_logement' => 'Studio', 'standing' => 'Economique'],
                 ['libelle_logement' => 'Appartement T2', 'standing' => 'Moyen Standing'],
                 ['libelle_logement' => 'Appartement T3', 'standing' => 'Haut Standing'],
                 ['libelle_logement' => 'Villa Simple', 'standing' => 'Moyen Standing'],
                 ['libelle_logement' => 'Duplex', 'standing' => 'Haut Standing'],
                 ['libelle_logement' => 'Chambre Moderne', 'standing' => 'Economique'],
                 ['libelle_logement' => 'Bureau', 'standing' => null],
             ];
             $this->command->info("Seeding default TypeLogements...");
             foreach ($types as $typeData) {
                 TypeLogement::create($typeData); // Le modèle gère la traduction
             }
        } else {
             $this->command->info('Table type_logements is not empty, skipping default types seeding.');
        }
    }
}

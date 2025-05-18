<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Quartier;
use App\Models\Ville;

class QuartierSeeder extends Seeder
{
    public function run(): void
    {
        // Tableau des villes et leurs quartiers respectifs
        $data = [
            'Douala' => [
                'Akwa', 'Bonanjo', 'Bonapriso', 'Bonamoussadi', 'Makepe', 'Logbessou', 'Logpom',
                'Kotto', 'Ndokoti', 'New-Bell', 'Bali', 'Deïdo', 'Ndogbong', 'Bépanda',
                'Cité des Palmiers', 'Village', 'PK8', 'PK14', 'PK17', 'Yassa', 'Ndogsimbi',
                'Nyalla', 'Logbaba', 'Mboppi', 'Cité SIC', 'Bilongue', 'Bonabéri', 'Kombé',
                'Mbanya', 'Makèpè Missokè',
            ],

            'Yaoundé' => [
                'Mvog-Ada', 'Essos', 'Biyem-Assi', 'Melen', 'Ngoa-Ekelle', 'Emana', 'Etoudi',
                'Nlongkak', 'Etoa-Meki', 'Ekounou', 'Nkolbisson', 'Obili', 'Omnisports',
                'Mimboman', 'Mokolo', 'Tsinga', 'Elig-Essono', 'Efoulan',
            ],

            'Bafoussam' => [
                'Banego', 'Tamdja', 'Kopou', 'Djeleng', 'Tchouo', 'Kamkop', 'Ngouache',
                'Famla', 'Lafé-Baleng', 'Mairie', 'Marché A', 'Marché B',
            ],

            'Garoua' => [
                'Plateau', 'Poli', 'Demsa', 'Laïndé', 'Roumde Adjia', 'Bokle', 'Pitoa', 'Tcholliré',
            ],

            'Bertoua' => [
                'Mokolo II', 'Ngaikada', 'Kano', 'Nkolbikon', 'Enia', 'Madina', 'Ndiabomo', 'Ndeme',
            ],
        ];

        // Insertion dans la base de données
        foreach ($data as $nomVille => $quartiers) {
            $ville = Ville::where('nomVille', $nomVille)->first();

            if ($ville) {
                foreach ($quartiers as $quartier) {
                    Quartier::create([
                        'nomQuartier' => $quartier,
                        'villeId' => $ville->id,
                    ]);
                }
            } else {
                $this->command->warn("Ville '{$nomVille}' non trouvée. Vérifiez que VilleSeeder a été exécuté.");
            }
        }
    }
}

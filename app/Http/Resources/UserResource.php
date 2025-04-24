<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if (is_null($this->resource)) {
            return [];
        }

        $data = [
            'id' => $this->id,
            'nom' => $this->name,
            'prenom' => $this->prenom,
            'email' => $this->email,
            'telephone' => $this->telephone,
            'matricule' => $this->matricule,
            'role' => $this->role,
            'photo_profil_url' => $this->photoProfile ? asset('storage/' . $this->photoProfile) : null,
            'quartier' => new QuartierResource($this->whenLoaded('quartier')), // Utilise QuartierResource
            'email_verifie' => !is_null($this->email_verified_at),
            'date_inscription' => $this->created_at?->toIso8601String(),
            // 'updated_at' => $this->updated_at?->toIso8601String(), // Moins utile pour l'API peut-être
        ];

        // --- CORRECTION ICI ---
        // Inclure les détails spécifiques SEULEMENT SI la relation correspondante est chargée
        // Et que l'objet de la relation n'est pas null (sécurité supplémentaire)

        // Détails Locataire
        $this->whenLoaded('locataire', function () use (&$data) {
            if ($this->locataire) { // Vérifie que l'objet relationnel existe
                 $data['details_locataire'] = [
                    'preference' => $this->locataire->preference,
                    'nombre_visites_payees' => $this->locataire->nrbvist,
                ];
            }
        });

        // Détails Bailleur
        $this->whenLoaded('bailleur', function () use (&$data) {
             if ($this->bailleur) { // Vérifie que l'objet relationnel existe
                $data['details_bailleur'] = [
                    'id_bailleur' => $this->bailleur->id, // Ajout de l'ID spécifique bailleur
                    'est_verifie' => (bool) $this->bailleur->verif,
                    'numero_fiscal' => $this->bailleur->numFiscal,
                    'description' => $this->bailleur->description, // Utilise l'accesseur traduit
                    'nombre_logements' => $this->bailleur->nbrLog,
                ];
             }
        });
        // --- FIN CORRECTION ---

        return $data;
    }
}

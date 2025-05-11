<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AvisResource extends JsonResource
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

        // La locale est supposée être définie dans le contrôleur
        // L'accesseur $this->coment est donc traduit

        return [
            'id' => $this->id,
            'commentaire' => $this->coment, // Utilise l'accesseur traduit
            'note' => $this->note, // Note sur 5 par exemple ?
            'est_visible' => (bool) $this->visible, // Si l'admin l'a approuvé
            'date_publication' => $this->dateEnv?->toIso8601String(), // Date de soumission de l'avis

            // Inclure les informations sur le locataire qui a laissé l'avis
            // si la relation 'locataire.user' est chargée
            'auteur' => $this->whenLoaded('locataire', function () {
                // Vérifie si l'utilisateur associé au locataire est aussi chargé
                if ($this->locataire && $this->locataire->relationLoaded('user')) {
                    return [
                        'id_utilisateur' => $this->locataire->user->id,
                        'nom_complet' => $this->locataire->user->name . ' ' . $this->locataire->user->prenom,
                        // URL de la photo de profil si elle existe
                        'photo_profil_url' => $this->locataire->user->photoProfile ? asset('storage/' . $this->locataire->user->photoProfile) : null,
                    ];
                }
                return null; // Retourne null si locataire ou user n'est pas chargé
            }),

             // Optionnel : ID du logement concerné (peut être utile)
            // 'logement_id' => $this->logId,
        ];
    }
}

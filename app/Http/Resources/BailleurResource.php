<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BailleurResource extends JsonResource
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
        return [
            'id_bailleur' => $this->id, // ID spécifique du profil bailleur
            'est_verifie' => (bool) $this->verif,
            'numero_fiscal' => $this->numFiscal,
            // Utilise l'accesseur du modèle Bailleur pour la traduction
            'description' => $this->description,
            'nombre_logements_enregistres' => $this->nbrLog,
            // Inclut les détails de l'utilisateur associé si la relation 'user' est chargée
            'utilisateur' => new UserResource($this->whenLoaded('user')),
        ];
    }
}

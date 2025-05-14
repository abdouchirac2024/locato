<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuartierResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Vérifie si la ressource sous-jacente n'est pas nulle
        // (Bonne pratique ajoutée pour éviter les erreurs avec whenLoaded sur null)
        if (is_null($this->resource)) {
            return [];
        }

        return [
            'id' => $this->id,
            'nomQuartier' => $this->nomQuartier, // Nom du quartier
            'del_yn' => $this->del_yn,
            'created_by' => $this->whenLoaded('creator', function() {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name
                ];
            }),
            'ville' => $this->whenLoaded('ville', function() {
                return [
                    'id' => $this->ville->id,
                    'nom' => $this->ville->nomVille
                ];
            }),
            'villeId' => $this->villeId, // Inclut l'ID de la ville (clé étrangère)
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}

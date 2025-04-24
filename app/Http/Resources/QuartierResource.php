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
            // Inclut le nom de la ville seulement si la relation 'ville' a été chargée
            'nomVille' => $this->whenLoaded('ville', fn() => $this->ville->nomVille),
            'villeId' => $this->villeId, // Inclut l'ID de la ville (clé étrangère)
        ];
    }
}

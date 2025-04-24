<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TypeLogementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Vérifie si la ressource sous-jacente n'est pas nulle
        if (is_null($this->resource)) {
            return [];
        }

        return [
            'id' => $this->id,
            'libelle' => $this->Standing, // Utilise le nom de colonne 'Standing' du modèle
            // Ajoutez d'autres champs du modèle TypeLogement si nécessaire
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VilleResource extends JsonResource
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
            'id' => $this->id,
            'nom' => $this->nomVille, // Utilise le nom de colonne 'nomVille'
        ];
    }
}

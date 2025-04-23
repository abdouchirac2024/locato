<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuartierResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nomQuartier' => $this->nomQuartier,
            'nomVille' => $this->whenLoaded('ville', fn() => $this->ville->nomVille),
            'villeId' => $this->villeId, // Optionnel : pratique pour les formulaires
        ];
    }
}

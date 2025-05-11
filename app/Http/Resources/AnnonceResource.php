<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnonceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return  [
        'titre' => $this->titre,
        'contenu' => $this->titre,
        'date_publication' =>$this->created_at?->toIso8601String(),
        'logId' =>new LogementResource($this->whenLoaded('logement')),
        'bailId' =>new BailleurResource($this->whenLoaded('bailleur')),
    ];

    }
}

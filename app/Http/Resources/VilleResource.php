<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VilleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'nomVille' => $this->nomVille,
        ];
    }
}

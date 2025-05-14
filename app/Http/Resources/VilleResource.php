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
<<<<<<< HEAD
         if (is_null($this->resource)) {
=======
        if (is_null($this->resource)) {
>>>>>>> 172c567c215f6076d8e6aab0684aa69c300f9117
            return [];
        }
        return [
            'id' => $this->id,
<<<<<<< HEAD
            'nom' => $this->nomVille, // Utilise le nom de colonne 'nomVille'
=======
            'nom' => $this->nomVille,
            'del_yn' => $this->del_yn,
            'created_by' => $this->whenLoaded('creator', function() {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
>>>>>>> 172c567c215f6076d8e6aab0684aa69c300f9117
        ];
    }
}

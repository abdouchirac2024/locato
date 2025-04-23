<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'prenom' => $this->prenom,
            'email' => $this->email,
            'telephone' => $this->telephone,
            'matricule' => $this->matricule,
            'role' => $this->role,
            'photoProfile' => $this->photoProfile ? asset('storage/' . $this->photoProfile) : null,
            'quartier' => $this->quartier,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        // Ajouter les données spécifiques au rôle
        if ($this->isLocataire() && $this->locataire) {
            $data['locataire'] = [
                'preference' => $this->locataire->preference,
                'nrbvist' => $this->locataire->nrbvist,
            ];
        }

        if ($this->isBailleur() && $this->bailleur) {
            $data['bailleur'] = [
                'verif' => $this->bailleur->verif,
                'numFiscal' => $this->bailleur->numFiscal,
                'description' => $this->bailleur->description,
                'nbrLog' => $this->bailleur->nbrLog,
            ];
        }

        return $data;
    }
}

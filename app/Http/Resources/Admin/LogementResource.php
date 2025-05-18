<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\QuartierResource;
use App\Http\Resources\BailleurResource;
use App\Http\Resources\ImageLogementResource;
use App\Http\Resources\AvisResource;

class LogementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Gérer le cas où la ressource elle-même est nulle
        if (is_null($this->resource)) {
            return [];
        }

        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'libelle' => $this->libelle,
            'coordonnees' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ],
            'adresse' => $this->adresse,
            'caracteristiques' => [
                'nombre_pieces' => $this->nbrpieces,
                'superficie_m2' => $this->superficie,
                'nombre_salles_bain' => $this->nbr_salles_bain,
                'nombre_chambres' => $this->nbr_chambres,
                'est_meuble' => (bool) $this->meuble,
            ],
            'prix_mensuel' => $this->prix,
            'mois_avance_requis' => $this->nbrMois,
            'description' => $this->description,
            'equipements' => [
                'compteur_prepaye' => (bool) $this->contPrep,
                'forage' => (bool) $this->forage,
                'parking' => (bool) $this->parking,
                'gardien' => (bool) $this->gardien,
                'climatisation' => (bool) $this->climatisation,
            ],
            'disponibilite' => $this->dispo,
            'statut_validation' => $this->validation_status,
            'statut_validation_lisible' => match ($this->validation_status) {
                 \App\Models\Logement::STATUS_EN_ATTENTE => 'En attente',
                 \App\Models\Logement::STATUS_APPROUVE => 'Approuvé',
                 \App\Models\Logement::STATUS_REJETE => 'Rejeté',
                 default => 'Inconnu',
             },
            'notes_admin' => $this->admin_notes,
            'type_logement' => new TypeLogementResource($this->whenLoaded('typeLogement')),
            'quartier_details' => new QuartierResource($this->whenLoaded('quartier')),
            'bailleur_info' => new BailleurResource($this->whenLoaded('bailleur')),
            'images' => ImageLogementResource::collection($this->whenLoaded('imagesLogement')),
            'avis_recents' => AvisResource::collection($this->whenLoaded('avis')),
            'date_creation' => $this->created_at?->toIso8601String(),
            'derniere_modification' => $this->updated_at?->toIso8601String(),
        ];
    }
} 
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LogementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // La locale est supposée être définie par App::setLocale() dans le contrôleur.
        // Les accesseurs $this->description et $this->dispo sont donc déjà traduits.

        return [
            'id' => $this->id,
            'libelle' => $this->libelle,
            'nombre_mois_avance' => $this->nbrMois,
            'prix' => $this->prix,
            'nombre_pieces' => $this->nbrpieces,
            'description' => $this->description, // Utilise l'accesseur traduit
            'details_equipements' => [ // Regrouper les booléens
                'compteur_prepaye' => $this->contPrep,
                'forage' => $this->forage,
                'parking' => $this->parking,
                'gardien' => $this->gardien,
            ],
            'disponibilite' => $this->dispo, // Utilise l'accesseur traduit

            // Charger les données des relations si elles sont présentes (via with/load)
            'type_logement' => new TypeLogementResource($this->whenLoaded('typeLogement')), // Utiliser une ressource dédiée
            'quartier_details' => new QuartierResource($this->whenLoaded('quartier')), // Utiliser une ressource dédiée
            'bailleur_info' => new BailleurResource($this->whenLoaded('bailleur')), // Utiliser une ressource dédiée

             // Collections de ressources pour les relations HasMany
            'images' => ImageLogementResource::collection($this->whenLoaded('imagesLogement')),
            'avis_recents' => AvisResource::collection($this->whenLoaded('avis')),

            // Dates de création/modification
            'date_creation' => $this->created_at?->toIso8601String(), // Format standard ISO 8601
            'derniere_modification' => $this->updated_at?->toIso8601String(),

            // Optionnel: Liens HATEOAS (si vous construisez une API hypermédia)
            // '_links' => [
            //    'self' => route('logements.show', $this->id),
            //    'bailleur' => route('bailleurs.show', $this->bailId), // Assurez-vous que ces routes existent
            // ]
        ];
    }
}

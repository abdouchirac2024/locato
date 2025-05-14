<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LogementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if (is_null($this->resource)) { return []; }

        // L'appel à App::setLocale() dans le contrôleur garantit que
        // les accesseurs $this->description, $this->dispo, $this->libelle
        // retournent la bonne langue.

        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'libelle' => $this->libelle, // Accesseur magique (traduit si _en existe)
            'coordonnees' => [ // Regrouper lat/lon
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ],
            'adresse' => $this->adresse,
            'caracteristiques' => [
                'nombre_pieces' => $this->nbrpieces,
                'superficie_m2' => $this->superficie,
                'nombre_salles_bain' => $this->nbr_salles_bain,
                'nombre_chambres' => $this->nbr_chambres,
                'est_meuble' => (bool) $this->meuble, // Cast en booléen
            ],
            'prix_mensuel' => $this->prix,
            'mois_avance_requis' => $this->nbrMois,
            'description' => $this->description, // Accesseur magique traduit
            'equipements' => [
                'compteur_prepaye' => (bool) $this->contPrep,
                'forage' => (bool) $this->forage,
                'parking' => (bool) $this->parking,
                'gardien' => (bool) $this->gardien,
                'climatisation' => (bool) $this->climatisation,
            ],
            'disponibilite' => $this->dispo, // Accesseur magique traduit

            // Relations chargées conditionnellement
            'type_logement' => new TypeLogementResource($this->whenLoaded('typeLogement')),
            'quartier_details' => new QuartierResource($this->whenLoaded('quartier')),
            'bailleur_info' => new BailleurResource($this->whenLoaded('bailleur')),
            'images' => ImageLogementResource::collection($this->whenLoaded('imagesLogement')), // Utilisation du nom de relation correct
            'avis_recents' => AvisResource::collection($this->whenLoaded('avis')),

            // Timestamps
            'date_creation' => $this->created_at?->toIso8601String(),
            'derniere_modification' => $this->updated_at?->toIso8601String(),
        ];
    }
}

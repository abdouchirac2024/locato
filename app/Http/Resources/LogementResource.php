<?php
<<<<<<< HEAD

=======
>>>>>>> 172c567c215f6076d8e6aab0684aa69c300f9117
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LogementResource extends JsonResource
{
<<<<<<< HEAD
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
=======
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
>>>>>>> 172c567c215f6076d8e6aab0684aa69c300f9117
        ];
    }
}

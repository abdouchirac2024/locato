<?php

namespace App\Http\Resources; // Le namespace de cette classe reste le même

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// --- MODIFICATION : Importer la ressource spécifique depuis le dossier Admin ---
use App\Http\Resources\Admin\TypeLogementResource;
// --- FIN MODIFICATION ---

// Importer les autres ressources nécessaires (assurez-vous qu'elles existent et sont correctes)
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

        // La locale (fr/en) est définie dans le contrôleur avant d'appeler cette ressource.
        // Les accesseurs du modèle Logement ($this->description, $this->dispo, $this->libelle)
        // et les accesseurs du modèle TypeLogement via la ressource importée
        // retourneront donc automatiquement la bonne langue.

        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'libelle' => $this->libelle, // Utilise l'accesseur getLibelleAttribute() du modèle Logement
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
            'description' => $this->description, // Utilise l'accesseur getDescriptionAttribute()
            'equipements' => [
                'compteur_prepaye' => (bool) $this->contPrep,
                'forage' => (bool) $this->forage,
                'parking' => (bool) $this->parking,
                'gardien' => (bool) $this->gardien,
                'climatisation' => (bool) $this->climatisation,
            ],
            'disponibilite' => $this->dispo, // Utilise l'accesseur getDispoAttribute()
            'statut_validation' => $this->validation_status,
            'statut_validation_lisible' => match ($this->validation_status) {
                 \App\Models\Logement::STATUS_EN_ATTENTE => 'En attente',
                 \App\Models\Logement::STATUS_APPROUVE => 'Approuvé',
                 \App\Models\Logement::STATUS_REJETE => 'Rejeté',
                 default => 'Inconnu',
             },
            'notes_admin' => $this->admin_notes, // Inclure les notes

            // --- MODIFICATION : Utilise la classe importée ---
            // PHP sait maintenant que TypeLogementResource fait référence à App\Http\Resources\Admin\TypeLogementResource
            'type_logement' => new TypeLogementResource($this->whenLoaded('typeLogement')),
            // --- FIN MODIFICATION ---

            'quartier_details' => new QuartierResource($this->whenLoaded('quartier')),
            'bailleur_info' => new BailleurResource($this->whenLoaded('bailleur')),
            'images' => ImageLogementResource::collection($this->whenLoaded('imagesLogement')),
            'avis_recents' => AvisResource::collection($this->whenLoaded('avis')),

            // Timestamps
            'date_creation' => $this->created_at?->toIso8601String(),
            'derniere_modification' => $this->updated_at?->toIso8601String(),
        ];
    }
}

<?php
namespace App\Http\Resources; // Placé à la racine des Resources par défaut

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\LogementResource; // Pour afficher les détails du logement
use App\Http\Resources\UserResource; // Pour afficher les détails de l'utilisateur (locataire)
use Carbon\Carbon; // Ajout de l'import pour la classe Carbon

class VisiteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        if (is_null($this->resource)) {
            return [];
        }

        // La locale est définie dans le contrôleur, les accesseurs du modèle Visite fonctionneront
        return [
            'id' => $this->id,
            'date_visite_demandee' => $this->dateVisite?->format('Y-m-d'),
            'heure_visite_demandee' => $this->heureVisite ? Carbon::parse($this->heureVisite)->format('H:i') : null,
            'statut' => $this->statut, // Utilise l'accesseur getStatutAttribute()
            'commentaire_locataire' => $this->commentaire_locataire,
            'confirmation_bailleur' => (bool) $this->confirmation_bailleur,
            'commentaire_bailleur' => $this->commentaire_bailleur,
            'motif_rejet_bailleur' => $this->motifRejet, // Utilise l'accesseur getMotifRejetAttribute()
            'date_proposee_bailleur' => $this->date_proposee_bailleur?->format('Y-m-d'),
            'heure_proposee_bailleur' => $this->heure_proposee_bailleur ? Carbon::parse($this->heure_proposee_bailleur)->format('H:i') : null,

            // Relations (si chargées)
            'logement' => new LogementResource($this->whenLoaded('logement')),
            // Charger l'utilisateur du locataire, pas juste le profil locataire
            'locataire_info' => new UserResource($this->whenLoaded('locataire', fn() => $this->locataire?->user)),

            'date_demande' => $this->created_at?->toIso8601String(),
            'derniere_maj_demande' => $this->updated_at?->toIso8601String(),
        ];
    }
}

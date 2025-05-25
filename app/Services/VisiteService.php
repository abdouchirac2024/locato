<?php
namespace App\Services;

use App\Models\Visite;
use App\Models\Logement;
use App\Models\User;
use App\Models\Locataire;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon; // Pour la gestion des dates/heures
use App\Events\VisitCompleted;

class VisiteService
{
    /**
     * Un locataire demande une visite pour un logement.
     */
    public function requestVisite(array $data): Visite
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user || !$user->isLocataire() || !$user->locataire) {
            throw new AuthorizationException("Seuls les locataires connectés peuvent demander une visite.");
        }

        $logement = Logement::approuve()->findOrFail($data['logId']); // Ne peut demander que pour logements approuvés

        // Vérifier si une visite active/programmée existe déjà pour ce logement par ce locataire
        $existingVisite = Visite::where('logId', $logement->id)
                                ->where('locaId', $user->locataire->id)
                                ->whereIn('statut_fr', [Visite::STATUT_DEMANDEE, Visite::STATUT_PROGRAMMEE, Visite::STATUT_CONTRE_PROPOSITION])
                                ->first();
        if ($existingVisite) {
            throw new \LogicException("Vous avez déjà une demande de visite en cours ou programmée pour ce logement.");
        }

        Log::info("Service: Locataire requesting visite", ['locataire_id' => $user->locataire->id, 'logement_id' => $logement->id, 'data' => $data]);

        // Valider que la date/heure est dans le futur (peut être fait dans le FormRequest aussi)
        $requestedDateTime = Carbon::parse($data['dateVisite'] . ' ' . $data['heureVisite']);
        if ($requestedDateTime->isPast()) {
             throw new \InvalidArgumentException("La date et l'heure de la visite doivent être dans le futur.");
        }

        $visiteData = [
            'logId' => $logement->id,
            'locaId' => $user->locataire->id,
            'dateVisite' => $data['dateVisite'],
            'heureVisite' => Carbon::createFromFormat('H:i', $data['heureVisite'])->format('H:i:s'), // Assurer format H:i:s
            'statut_fr' => Visite::STATUT_DEMANDEE, // Statut initial
            'commentaire_locataire' => $data['commentaire_locataire'] ?? null,
            'confirmation_bailleur' => false, // Non confirmé par défaut
        ];

        $visite = Visite::create($visiteData); // Le modèle Visite gère la traduction du statut
        Log::info("Service: Visite requested successfully", ['visite_id' => $visite->id]);

        // TODO: Notifier le bailleur du logement de la nouvelle demande de visite
        // $logement->bailleur->user->notify(new NewVisiteRequestNotification($visite));

        return $visite->load(['logement', 'locataire.user']);
    }

    /**
     * Un bailleur met à jour le statut d'une demande de visite.
     */
    public function updateVisiteStatus(Visite $visite, string $newStatusFr, ?string $bailleurComment = null, ?string $proposedDate = null, ?string $proposedTime = null): Visite
    {
        /** @var User|null $user */
        $user = Auth::user();
        // Vérifier que l'utilisateur est le bailleur du logement associé à la visite
        if (!$user || !$user->isBailleur() || !$user->bailleur || $visite->logement->bailId !== $user->bailleur->id) {
            throw new AuthorizationException("Vous n'êtes pas autorisé à modifier cette demande de visite.");
        }

        // Vérifier si le statut est valide
        $validStatus = [
            Visite::STATUT_PROGRAMMEE, Visite::STATUT_ANNULEE_BAILLEUR,
            Visite::STATUT_REPORTEE_BAILLEUR, Visite::STATUT_EFFECTUEE, Visite::STATUT_CONTRE_PROPOSITION
        ];
        if (!in_array($newStatusFr, $validStatus)) {
             throw new \InvalidArgumentException("Statut de visite invalide: {$newStatusFr}");
        }

        Log::info("Service: Bailleur updating visite status", [
            'visite_id' => $visite->id, 'new_status_fr' => $newStatusFr,
            'bailleur_id' => $user->bailleur->id
        ]);

        $visite->statut_fr = $newStatusFr; // Le modèle traduira en _en
        $visite->confirmation_bailleur = true; // Le bailleur a agi
        $visite->commentaire_bailleur = $bailleurComment;

        if ($newStatusFr === Visite::STATUT_REPORTEE_BAILLEUR || $newStatusFr === Visite::STATUT_CONTRE_PROPOSITION) {
            if (empty($proposedDate) || empty($proposedTime)) {
                throw new \InvalidArgumentException("Une date et une heure doivent être proposées pour un report ou une contre-proposition.");
            }
            $visite->date_proposee_bailleur = Carbon::parse($proposedDate)->toDateString();
            $visite->heure_proposee_bailleur = Carbon::createFromFormat('H:i', $proposedTime)->format('H:i:s');
        } else {
            $visite->date_proposee_bailleur = null;
            $visite->heure_proposee_bailleur = null;
        }

        $visite->save(); // Le modèle gère la traduction du statut
        Log::info("Service: Visite status updated", ['visite_id' => $visite->id, 'new_status' => $visite->statut]);

        if ($newStatusFr === Visite::STATUT_EFFECTUEE) {
            VisitCompleted::dispatch($visite);
        }

        // TODO: Notifier le locataire du changement de statut
        // $visite->locataire->user->notify(new VisiteStatusUpdatedNotification($visite));

        return $visite->fresh(['logement', 'locataire.user']);
    }

    /**
     * Un locataire annule sa propre demande de visite (si encore possible).
     */
    public function cancelVisiteByLocataire(Visite $visite): Visite
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user || !$user->isLocataire() || !$user->locataire || $visite->locaId !== $user->locataire->id) {
            throw new AuthorizationException("Vous n'êtes pas autorisé à annuler cette visite.");
        }
        // On ne peut annuler que si c'est 'DEMANDEE' ou 'PROGRAMMEE' (ou 'CONTRE_PROPOSITION')
        if (!in_array($visite->statut_fr, [Visite::STATUT_DEMANDEE, Visite::STATUT_PROGRAMMEE, Visite::STATUT_CONTRE_PROPOSITION])) {
            throw new \LogicException("Impossible d'annuler une visite avec le statut actuel: {$visite->statut_fr}");
        }

        Log::info("Service: Locataire cancelling visite", ['visite_id' => $visite->id, 'locataire_id' => $user->locataire->id]);

        $visite->statut_fr = Visite::STATUT_ANNULEE_LOCATAIRE;
        $visite->save(); // Le modèle traduit

        // TODO: Notifier le bailleur de l'annulation
        // $visite->logement->bailleur->user->notify(new VisiteCancelledByLocataireNotification($visite));

        return $visite->fresh(['logement', 'locataire.user']);
    }

    /**
     * Lister les demandes de visite pour un logement spécifique (pour le bailleur).
     */
    public function getVisiteRequestsForLogement(int $logementId, int $perPage = 15): LengthAwarePaginator
    {
        /** @var User|null $user */
        $user = Auth::user();
        $logement = Logement::findOrFail($logementId);
        if (!$user || !$user->isBailleur() || !$user->bailleur || $logement->bailId !== $user->bailleur->id) {
            throw new AuthorizationException("Accès non autorisé aux demandes de visite de ce logement.");
        }

        return Visite::where('logId', $logementId)
                     ->with(['locataire.user', 'logement']) // Charger infos locataire et logement
                     ->latest('dateVisite') // Trier par date de visite souhaitée
                     ->paginate($perPage);
    }

    /**
     * Lister les visites demandées par le locataire connecté.
     */
    public function getMyVisiteRequests(int $perPage = 15): LengthAwarePaginator
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user || !$user->isLocataire() || !$user->locataire) {
            throw new AuthorizationException("Seuls les locataires peuvent voir leurs demandes de visite.");
        }

        return Visite::where('locaId', $user->locataire->id)
                     ->with(['logement.quartier.ville', 'logement.bailleur.user']) // Charger détails logement
                     ->latest('created_at') // Trier par date de création de la demande
                     ->paginate($perPage);
    }
}
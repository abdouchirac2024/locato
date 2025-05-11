<?php

namespace App\Services;

use App\Models\Logement;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log; // Pour le logging
use Illuminate\Auth\Access\AuthorizationException; // Pour les erreurs d'autorisation


class LogementService
{
    /**
     * Récupère une liste paginée de logements avec leurs relations essentielles.
     */
    public function getAllLogements(int $perPage = 15): LengthAwarePaginator
    {
        Log::info('Fetching all logements with pagination.', ['perPage' => $perPage]);
        // Charge les relations fréquemment utilisées pour éviter N+1
        return Logement::with(['typeLogement', 'quartier.ville', 'bailleur.user'])
                       ->latest() // Ordonne par date de création décroissante
                       ->paginate($perPage);
    }

    /**
     * Crée un nouveau logement associé au bailleur authentifié.
     *
     * @param array $data Données validées provenant du StoreLogementRequest.
     * @return Logement Le logement nouvellement créé.
     * @throws AuthorizationException Si l'utilisateur n'est pas un bailleur autorisé.
     * @throws \Exception Pour d'autres erreurs de création.
     */
    public function createLogement(array $data): Logement
    {
        /** @var User|null $user */
        $user = Auth::user();

        // Vérification rigoureuse de l'autorisation
        if (!$user || !$user->isBailleur() || !$user->bailleur) {
            Log::warning('Unauthorized attempt to create logement.', ['user_id' => $user->id ?? null]);
            throw new AuthorizationException("Seul un bailleur connecté peut créer un logement.");
        }

        Log::info('Creating new logement.', ['bailleur_id' => $user->bailleur->id, 'data' => $data]);

        // Associe l'ID du bailleur connecté aux données
        $data['bailId'] = $user->bailleur->id;

        // La traduction est gérée par l'événement 'creating' dans le modèle Logement
        try {
            $logement = Logement::create($data);
            // Charge les relations pour que la ressource API ait les données
            $logement->load(['typeLogement', 'quartier.ville', 'bailleur.user']);
            Log::info('Logement created successfully.', ['logement_id' => $logement->id]);
            return $logement;
        } catch (\Exception $e) {
             Log::error('Error creating logement.', ['error' => $e->getMessage(), 'data' => $data]);
             throw new \Exception("Impossible de créer le logement : " . $e->getMessage());
        }
    }

    /**
     * Récupère un logement spécifique par son ID avec des relations détaillées.
     *
     * @param int $id ID du logement.
     * @return Logement Le logement trouvé.
     * @throws ModelNotFoundException Si le logement n'est pas trouvé.
     */
    public function getLogementById(int $id): Logement
    {
        Log::info('Fetching logement by ID.', ['logement_id' => $id]);
        try {
            // Charge des relations plus complètes pour la vue détaillée
            return Logement::with([
                            'typeLogement',
                            'quartier.ville',
                            'bailleur.user',
                            'imagesLogement', // Charger les images associées
                            'avis.locataire.user' // Charger les avis et l'utilisateur qui a laissé l'avis
                           ])
                           ->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            Log::warning('Logement not found.', ['logement_id' => $id]);
            // Relance l'exception pour que le contrôleur la gère (404)
            throw new ModelNotFoundException("Le logement avec l'ID {$id} n'a pas été trouvé.");
        }
    }

    /**
     * Met à jour un logement existant après vérification des autorisations.
     *
     * @param int $id ID du logement à mettre à jour.
     * @param array $data Données validées provenant du UpdateLogementRequest.
     * @return Logement Le logement mis à jour.
     * @throws ModelNotFoundException Si le logement n'est pas trouvé.
     * @throws AuthorizationException Si l'utilisateur n'est pas le propriétaire du logement.
     * @throws \Exception Pour d'autres erreurs de mise à jour.
     */
    public function updateLogement(int $id, array $data): Logement
    {
        Log::info('Attempting to update logement.', ['logement_id' => $id, 'data' => $data]);
        // Récupère le logement ou lève une ModelNotFoundException
        $logement = $this->getLogementById($id);

        /** @var User|null $user */
        $user = Auth::user();

        // Vérification : Seul le bailleur propriétaire du logement peut le modifier
        if (!$user || !$user->isBailleur() || !$user->bailleur || $logement->bailId !== $user->bailleur->id) {
            Log::warning('Unauthorized attempt to update logement.', [
                'logement_id' => $id,
                'user_id' => $user->id ?? null,
                'logement_bailleur_id' => $logement->bailId,
                'user_bailleur_id' => $user->bailleur->id ?? null
             ]);
            throw new AuthorizationException("Vous n'êtes pas autorisé à modifier ce logement.");
        }

        // La traduction est gérée par l'événement 'updating' dans le modèle Logement si _fr change
        try {
             $logement->update($data);
             // Recharger les relations pour la réponse API
             $logement->load(['typeLogement', 'quartier.ville', 'bailleur.user']);
             Log::info('Logement updated successfully.', ['logement_id' => $id]);
             return $logement;
        } catch (\Exception $e) {
             Log::error('Error updating logement.', ['logement_id' => $id, 'error' => $e->getMessage(), 'data' => $data]);
             throw new \Exception("Impossible de mettre à jour le logement : " . $e->getMessage());
        }
    }

    /**
     * Supprime un logement après vérification des autorisations.
     *
     * @param int $id ID du logement à supprimer.
     * @return bool True si la suppression a réussi.
     * @throws ModelNotFoundException Si le logement n'est pas trouvé.
     * @throws AuthorizationException Si l'utilisateur n'est pas le propriétaire du logement.
     * @throws \Exception Pour d'autres erreurs de suppression.
     */
    public function deleteLogement(int $id): bool
    {
         Log::info('Attempting to delete logement.', ['logement_id' => $id]);
        // Récupère le logement ou lève une ModelNotFoundException
        $logement = $this->getLogementById($id);

        /** @var User|null $user */
        $user = Auth::user();

        // Vérification : Seul le bailleur propriétaire peut supprimer
         if (!$user || !$user->isBailleur() || !$user->bailleur || $logement->bailId !== $user->bailleur->id) {
             Log::warning('Unauthorized attempt to delete logement.', [
                'logement_id' => $id,
                'user_id' => $user->id ?? null,
                'logement_bailleur_id' => $logement->bailId,
                'user_bailleur_id' => $user->bailleur->id ?? null
             ]);
             throw new AuthorizationException("Vous n'êtes pas autorisé à supprimer ce logement.");
        }

        try {
            // Logique de suppression additionnelle si nécessaire (ex: supprimer fichiers associés)
            // ...

            $deleted = $logement->delete();
            if ($deleted) {
                 Log::info('Logement deleted successfully.', ['logement_id' => $id]);
            } else {
                Log::warning('Logement deletion failed.', ['logement_id' => $id]);
            }
            return $deleted;
        } catch (\Exception $e) {
             Log::error('Error deleting logement.', ['logement_id' => $id, 'error' => $e->getMessage()]);
             // Ne pas exposer les détails de l'erreur de base de données directement
             throw new \Exception("Impossible de supprimer le logement.");
        }
    }
}

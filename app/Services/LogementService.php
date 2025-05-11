<?php
namespace App\Services;

use App\Models\Logement;
use App\Models\ImageLogement;
use App\Models\User;
use App\Models\Bailleur;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\AdminLogementPendingNotification;
use App\Mail\BailleurLogementStatusNotification;
use App\Mail\BailleurLogementCreatedNotification;
use App\Mail\BailleurLogementSubmittedNotification; // Nouvel import
use Illuminate\Http\UploadedFile;

class LogementService
{
    /**
     * Récupère les logements PUBLICS (approuvés) avec pagination et relations de base.
     */
    public function getAllLogements(int $perPage = 15): LengthAwarePaginator
    {
        Log::info('Service: Fetching all PUBLIC logements.', ['perPage' => $perPage]);
        return Logement::with(['typeLogement', 'quartier.ville', 'bailleur.user'])
                       ->approuve()
                       ->latest()
                       ->paginate($perPage);
    }

    /**
     * Crée un nouveau logement soumis par un BAILLEUR.
     * Le statut est défini sur 'en_attente'.
     * Gère le stockage des images associées.
     * Notifie les administrateurs ET LE BAILLEUR de la soumission.
     */
    public function createLogement(array $data, ?array $images = null): Logement
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user || !$user->isBailleur() || !$user->bailleur) {
            throw new AuthorizationException("Seul un bailleur connecté et profilé peut créer un logement.");
        }
        Log::info('Service: Bailleur creating Logement.', ['bailleur_id' => $user->bailleur->id, 'data_keys' => array_keys($data), 'has_images' => !empty($images)]);

        $logement = DB::transaction(function () use ($data, $images, $user) {
            $data['bailId'] = $user->bailleur->id;
            $data['validation_status'] = Logement::STATUS_EN_ATTENTE;
            if (empty($data['reference'])) { $data['reference'] = 'LOG-' . strtoupper(uniqid()); }
            $logement = Logement::create($data);
            $this->storeLogementImages($logement, $images);
            return $logement;
        });

        // Notifier l'admin que le logement est en attente
        $this->notifyAdminPending($logement);

        // Notifier le bailleur que son logement a été soumis
        $this->notifyBailleurSubmitted($logement);

        $logement->load(['typeLogement', 'quartier.ville', 'bailleur.user', 'imagesLogement']);
        Log::info('Service: Logement created by bailleur, pending approval.', ['logement_id' => $logement->id]);
        return $logement;
    }

    /**
     * Crée un nouveau logement soumis par un ADMIN pour un bailleur spécifique.
     * Le statut est défini sur 'approuve'.
     * Gère le stockage des images associées.
     * Notifie le bailleur concerné.
     */
    public function createLogementAsAdmin(array $data, ?array $images = null): Logement
    {
        $bailleurId = $data['bailId'];
        Log::info('Service: Admin creating Logement.', ['admin_id' => Auth::id(), 'bailleur_id' => $bailleurId, 'data_keys' => array_keys($data), 'has_images' => !empty($images)]);

        $bailleur = Bailleur::find($bailleurId);
        if (!$bailleur) { throw new \InvalidArgumentException("Le bailleur avec l'ID {$bailleurId} n'existe pas."); }

        $logement = DB::transaction(function () use ($data, $images, $bailleur) {
            $data['validation_status'] = Logement::STATUS_APPROUVE;
            $data['admin_notes'] = "Créé et approuvé par l'administrateur.";
            if (empty($data['reference'])) { $data['reference'] = 'LOG-ADM-' . strtoupper(uniqid()); }
            $logement = Logement::create($data);
            $this->storeLogementImages($logement, $images);
            return $logement;
        });

        $this->notifyBailleurCreatedByAdmin($logement);

        $logement->load(['typeLogement', 'quartier.ville', 'bailleur.user', 'imagesLogement']);
        Log::info('Service: Logement created and approved by admin.', ['logement_id' => $logement->id]);
        return $logement;
    }

    /**
     * Récupère un logement spécifique par son ID avec les relations détaillées.
     */
    public function getLogementById(int $id): Logement
    {
        Log::info('Service: Fetching logement by ID.', ['logement_id' => $id]);
        try {
            return Logement::with([
                            'typeLogement',
                            'quartier.ville',
                            'bailleur.user',
                            'imagesLogement',
                            'avis.locataire.user'
                           ])->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            Log::warning('Service: Logement not found.', ['logement_id' => $id]);
            throw new ModelNotFoundException("Le logement avec l'ID {$id} n'a pas été trouvé.");
        }
    }

    /**
     * Met à jour un logement existant (par son propriétaire Bailleur).
     * Ne modifie pas le statut de validation.
     */
    public function updateLogement(int $id, array $data): Logement
    {
        Log::info('Service: Updating logement.', ['logement_id' => $id, 'data_keys' => array_keys($data)]);
        $logement = $this->getLogementById($id);
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user || !$user->isBailleur() || !$user->bailleur || $logement->bailId !== $user->bailleur->id) {
            throw new AuthorizationException("Vous n'êtes pas autorisé à modifier ce logement.");
        }

        unset($data['validation_status'], $data['admin_notes']);

        try {
            $logement->update($data);
            $logement->load(['typeLogement', 'quartier.ville', 'bailleur.user', 'imagesLogement']);
            Log::info('Service: Logement updated by owner.', ['logement_id' => $id]);
            return $logement->fresh();
        } catch (\Exception $e) {
            Log::error('Service: Error updating logement.', ['logement_id' => $id, 'error' => $e->getMessage()]);
            throw new \RuntimeException("Impossible de mettre à jour le logement. Veuillez vérifier vos données.");
        }
    }

    /**
     * Supprime un logement (par son propriétaire Bailleur).
     * Supprime également les images physiques associées.
     */
    public function deleteLogement(int $id): bool
    {
        Log::info('Service: Deleting logement.', ['logement_id' => $id]);
        $logement = $this->getLogementById($id);
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user || !$user->isBailleur() || !$user->bailleur || $logement->bailId !== $user->bailleur->id) {
            throw new AuthorizationException("Vous n'êtes pas autorisé à supprimer ce logement.");
        }

        try {
            $this->deleteLogementImages($logement);
            $deleted = $logement->delete();
            if ($deleted) {
                Log::info('Service: Logement deleted by owner.', ['id' => $id]);
            } else {
                Log::warning('Service: Logement deletion failed.', ['id' => $id]);
            }
            return $deleted;
        } catch (\Exception $e) {
            Log::error('Service: Error deleting logement.', ['logement_id' => $id, 'error' => $e->getMessage()]);
            throw new \RuntimeException("Impossible de supprimer le logement en raison d'une erreur interne.");
        }
    }

    /**
     * Liste les logements en attente de validation.
     */
    public function listPendingLogements(int $perPage = 15): LengthAwarePaginator
    {
        Log::info('Service: Fetching pending logements for Admin.');
        return Logement::with(['typeLogement', 'quartier.ville', 'bailleur.user'])
                       ->enAttente()
                       ->latest()
                       ->paginate($perPage);
    }

    /**
     * Approuve un logement spécifique.
     */
    public function approveLogement(Logement $logement): Logement
    {
        if ($logement->validation_status !== Logement::STATUS_EN_ATTENTE) {
            throw new \LogicException("Ce logement n'est plus en attente de validation (Statut actuel: {$logement->validation_status}).");
        }
        Log::info('Service: Admin approving logement.', ['id' => $logement->id]);

        $logement->update([
            'validation_status' => Logement::STATUS_APPROUVE,
            'admin_notes' => null
        ]);

        $this->notifyBailleur($logement, 'approuve');

        return $logement->fresh(['typeLogement', 'quartier.ville', 'bailleur.user', 'imagesLogement']);
    }

    /**
     * Rejette un logement spécifique.
     */
    public function rejectLogement(Logement $logement, ?string $notes = null): Logement
    {
        if ($logement->validation_status !== Logement::STATUS_EN_ATTENTE) {
            throw new \LogicException("Ce logement n'est plus en attente de validation (Statut actuel: {$logement->validation_status}).");
        }
        Log::info('Service: Admin rejecting logement.', ['id' => $logement->id, 'notes' => $notes]);

        $logement->update([
            'validation_status' => Logement::STATUS_REJETE,
            'admin_notes' => $notes
        ]);

        $this->notifyBailleur($logement, 'rejete', $notes);

        return $logement->fresh(['typeLogement', 'quartier.ville', 'bailleur.user', 'imagesLogement']);
    }

    /**
     * Stocke les images uploadées pour un logement donné.
     */
    private function storeLogementImages(Logement $logement, ?array $images): void
    {
        if (empty($images)) {
            Log::debug("Helper storeLogementImages: No images provided for Logement ID: {$logement->id}.");
            return;
        }

        Log::debug("Helper storeLogementImages: Processing images", ['logement_id' => $logement->id, 'count' => count($images)]);
        $isFirstImage = !$logement->imagesLogement()->where('is_principale', true)->exists();

        foreach ($images as $index => $imageFile) {
            if ($imageFile instanceof UploadedFile && $imageFile->isValid()) {
                Log::debug("Processing valid UploadedFile at index {$index}", ['name' => $imageFile->getClientOriginalName()]);
                try {
                    $path = $imageFile->store("logement_images/{$logement->id}", 'public');
                    if ($path) {
                        ImageLogement::create([
                            'logId' => $logement->id,
                            'urlImage' => $path,
                            'taille' => $imageFile->getSize(),
                            'is_principale' => $isFirstImage,
                        ]);
                        $isFirstImage = false;
                        Log::info("Helper storeLogementImages: Image stored successfully.", ['path' => $path]);
                    } else {
                        Log::error("Helper storeLogementImages: Storage::store() returned false.", ['original_name' => $imageFile->getClientOriginalName()]);
                    }
                } catch (\Exception $e) {
                    Log::error("Helper storeLogementImages: Exception during storage/save.", ['error' => $e->getMessage(), 'original_name' => $imageFile->getClientOriginalName()]);
                    throw new \RuntimeException("Erreur interne lors de la sauvegarde d'une image.");
                }
            } else {
                $type = is_object($imageFile) ? get_class($imageFile) : gettype($imageFile);
                $errorCode = ($imageFile instanceof UploadedFile) ? $imageFile->getError() : 'N/A';
                $errorMsg = ($imageFile instanceof UploadedFile) ? $imageFile->getErrorMessage() : 'N/A';
                Log::warning("Helper storeLogementImages: Skipped invalid file at index {$index}.", [
                    'received_type' => $type,
                    'is_valid_uploaded_file_check' => ($imageFile instanceof UploadedFile),
                    'php_upload_error_code' => $errorCode,
                    'php_upload_error_message' => $errorMsg,
                ]);
            }
        }
    }

    /**
     * Supprime les fichiers images physiques associés à un logement.
     */
    private function deleteLogementImages(Logement $logement): void
    {
        $images = $logement->imagesLogement()->get(['urlImage']);
        if ($images->isNotEmpty()) {
            $paths = $images->pluck('urlImage')->filter()->toArray();
            if (!empty($paths)) {
                Log::info("Helper: Deleting physical images for Logement ID {$logement->id}", ['paths' => $paths]);
                Storage::disk('public')->delete($paths);
            }
        }
    }

    /**
     * Met en file d'attente la notification pour l'admin.
     */
    private function notifyAdminPending(Logement $logement): void
    {
        try {
            $admins = User::where('role', 'ADMIN')->get();
            if ($admins->isNotEmpty()) {
                $logement->loadMissing('bailleur.user');
                foreach ($admins as $admin) {
                    if ($admin->email) {
                        Mail::to($admin->email)->queue(new AdminLogementPendingNotification($logement));
                    }
                }
                Log::info("Admin notification queued for pending Logement.", ['logement_id' => $logement->id]);
            } else {
                Log::warning("No admins found for notification.", ['logement_id' => $logement->id]);
            }
        } catch (\Exception $e) {
            Log::error("Failed queueing admin notification.", ['logement_id' => $logement->id, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Met en file d'attente la notification pour le bailleur (statut changé).
     */
    private function notifyBailleur(Logement $logement, string $status, ?string $notes = null): void
    {
        try {
            $logement->loadMissing('bailleur.user');
            $bailleurUser = $logement->bailleur?->user;
            if ($bailleurUser && $bailleurUser->email) {
                Mail::to($bailleurUser->email)->queue(new BailleurLogementStatusNotification($logement, $status, $notes));
                Log::info("Bailleur status notification queued.", ['logement_id' => $logement->id, 'status' => $status]);
            } else {
                Log::warning("Cannot notify bailleur (status): User or email missing.", ['logement_id' => $logement->id]);
            }
        } catch (\Exception $e) {
            Log::error("Failed queueing bailleur status notification.", ['logement_id' => $logement->id, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Met en file d'attente la notification pour le bailleur (créé par admin).
     */
    private function notifyBailleurCreatedByAdmin(Logement $logement): void
    {
        try {
            $logement->loadMissing('bailleur.user');
            $bailleurUser = $logement->bailleur?->user;
            if ($bailleurUser && $bailleurUser->email) {
                Mail::to($bailleurUser->email)->queue(new BailleurLogementCreatedNotification($logement));
                Log::info("Bailleur created-by-admin notification queued.", ['logement_id' => $logement->id]);
            } else {
                Log::warning("Cannot notify bailleur (admin creation): User or email missing.", ['logement_id' => $logement->id]);
            }
        } catch (\Exception $e) {
            Log::error("Failed queueing bailleur created-by-admin notification.", ['logement_id' => $logement->id, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Met en file d'attente la notification pour le bailleur (soumission du logement).
     */
    private function notifyBailleurSubmitted(Logement $logement): void
    {
        try {
            $logement->loadMissing('bailleur.user');
            $bailleurUser = $logement->bailleur?->user;

            if ($bailleurUser && $bailleurUser->email) {
                Mail::to($bailleurUser->email)->queue(new BailleurLogementSubmittedNotification($logement));
                Log::info("Bailleur submission notification queued.", ['logement_id' => $logement->id, 'bailleur_email' => $bailleurUser->email]);
            } else {
                Log::warning("Cannot notify bailleur (submission): User or email missing for bailleur.", ['logement_id' => $logement->id, 'bailleur_id' => $logement->bailId]);
            }
        } catch (\Exception $e) {
            Log::error("Failed queueing bailleur submission notification.", ['logement_id' => $logement->id, 'error' => $e->getMessage()]);
        }
    }
}

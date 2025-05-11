<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use App\Models\Annonce;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Mail\CreateAnnonceMail;
use App\Mail\CreateAnnonceMailReceptionMail;
use App\Mail\ConfirmAnnonceMail;
use Stichoza\GoogleTranslate\GoogleTranslate;
use App\Models\User;
use App\Models\Bailleur;

class AnnonceService
{
    /**
     * Insérer une annonce
     * @param array $data Données de l'annonce
     * @return Annonce
     * @throws \Exception
     */
    public function createAnnonce(array $data): Annonce
    {
        // La traduction est gérée par l'événement 'creating' dans le modèle Annonce
        try {
            Log::info('annonce', ['Annonce' => $data]);

            if (empty($data['titre_en']) && !empty($data['titre_fr'])) {
                $data['titre_en'] = $this->translateText($data['titre_fr'], 'en', 'fr');
                $data['translation_auto'] = true;
            }

            if (empty($data['contenu_en']) && !empty($data['contenu_fr'])) {
                $data['contenu_en'] = $this->translateText($data['contenu_fr'], 'en', 'fr');
                $data['translation_auto'] = true;
            }

            $data['create_by'] = "SYSTEM";
            $annonce = Annonce::create($data);
            
            Log::info('Annonce created successfully.', ['Annonce_id' => $annonce->id]);
            return $annonce;

        } catch (\Exception $e) {
            Log::error('Error creating Annonce.', ['error' => $e->getMessage(), 'data' => $data]);
            throw new \Exception("Impossible de créer le Annonce : " . $e->getMessage());
        }
    }

    /**
     * Récupère une liste paginée de annonce avec leurs relations essentielles
     * @param int $perPage Nombre d'éléments par page
     * @return LengthAwarePaginator
     */
    public function getAnnonces(int $perPage): LengthAwarePaginator
    {
        Log::info('Fetching all logements with pagination.', ['perPage' => $perPage]);
        // Charge les relations avec le bailleur et les logements
        return Annonce::with(['bailleur', 'logement'])
                     ->latest()
                     ->paginate($perPage);
    }

    /**
     * Récupère une annonce spécifique
     * @param int $id ID de l'annonce
     * @return Annonce
     * @throws ModelNotFoundException
     */
    public function getAnnonceById(int $id): Annonce
    {
        Log::info('Fetching annonce by ID.', ['annonce_id' => $id]);
        try {
            // Charge les relations avec le bailleur et les logements
            return Annonce::with([
                'logement',
                'bailleur',
            ])->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            Log::warning('Annonce not found.', ['annonce_id' => $id]);
            // Relance l'exception pour que le contrôleur la gère (404)
            throw new ModelNotFoundException("Le annonce avec l'ID {$id} n'a pas été trouvé.");
        }
    }

    /**
     * Met à jour un annonce existant après vérification des autorisations
     * @param int $id ID de l'annonce
     * @param array $data Données validées
     * @param bool $confirm Si l'annonce doit être confirmée
     * @return Annonce
     * @throws ModelNotFoundException|\Exception
     */
    public function updateAnnonce(int $id, array $data, bool $confirm): Annonce
    {
        Log::info('Attempting to update annonce.', ['annonce_id' => $id, 'data' => $data]);

        $annonce = $this->getAnnonceById($id);

        if ($confirm) {
            Mail::to($annonce->bailleur->user->email)->send(new ConfirmAnnonceMail());
        }

        try {
            $annonce->update($data);
            return $annonce;
        } catch (\Exception $e) {
            Log::error('Error updating annonce.', [
                'annonce_id' => $id, 
                'error' => $e->getMessage(), 
                'data' => $data
            ]);
            throw new \Exception("Impossible de mettre à jour le annonce : " . $e->getMessage());
        }
    }

    /**
     * Supprime une annonce
     * @param int $id ID de l'annonce
     * @return bool
     * @throws ModelNotFoundException|\Exception
     */
    public function deleteAnnonce(int $id): bool
    {
        Log::info('Attempting to delete annonce.', ['annonce_id' => $id]);
        // Récupère l'annonce ou lève une ModelNotFoundException
        $annonce = $this->getAnnonceById($id);

        try {
            $deleted = $annonce->delete();
            
            if ($deleted) {
                Log::info('Annonce deleted successfully.', ['annonce_id' => $id]);
            } else {
                Log::warning('Annonce deletion failed.', ['annonce_id' => $id]);
            }
            
            return $deleted;
        } catch (\Exception $e) {
            Log::error('Error deleting annonce.', ['annonce_id' => $id, 'error' => $e->getMessage()]);
            throw new \Exception("Impossible de supprimer le annonce.");
        }
    }

    /**
     * Insérer une annonce par un bailleur
     * @param array $data Données de l'annonce
     * @return Annonce
     * @throws \Exception
     */
    public function createAnnonceByBailleur(array $data): Annonce
    {
        try {
            Log::info('valeur venu', ['Annonce_id' => $data]);
            
            $bailleur = Bailleur::with(['user'])->findOrFail($data['bailId']);
            
            if (empty($data['titre_en']) && !empty($data['titre_fr'])) {
                $data['titre_en'] = $this->translateText($data['titre_fr'], 'en', 'fr');
                $data['translation_auto'] = true;
            }

            if (empty($data['contenu_en']) && !empty($data['contenu_fr'])) {
                $data['contenu_en'] = $this->translateText($data['contenu_fr'], 'en', 'fr');
                $data['translation_auto'] = true;
            }

            $data['create_by'] = $bailleur->user->name;
            Log::info('le Bailleur', ['Annonce_id' => $bailleur->user->name]);
            
            $annonce = Annonce::create($data);
            Log::info('Creation d\'annonce', ['Annonce_id' => $annonce]);
            Log::info('Annonce created successfully.', ['Annonce_id' => $annonce->bailleur->user->name]);
            
            Mail::to('kinieyvan@gmail.com')->send(new CreateAnnonceMail($annonce));
            Mail::to($annonce->bailleur->user->email)->send(new CreateAnnonceMailReceptionMail());
            
            return $annonce;
        } catch (\Exception $e) {
            Log::error('Error creating Annonce.', ['error' => $e->getMessage(), 'data' => $data]);
            throw new \Exception("Impossible de créer le Annonce : " . $e->getMessage());
        }
    }

    /**
     * Traduit un texte
     * @param string $text Texte à traduire
     * @param string $targetLang Langue cible
     * @param string $sourceLang Langue source
     * @return string
     */
    protected function translateText(string $text, string $targetLang, string $sourceLang = 'fr'): string
    {
        try {
            $translator = new GoogleTranslate($targetLang);
            $translator->setSource($sourceLang);
            
            $translator->setOptions([
                'verify' => false,
                'timeout' => 30
            ]);
            
            return $translator->translate($text);
        } catch (\Exception $e) {
            Log::warning('Échec de la traduction', [
                'text' => $text,
                'error' => $e->getMessage()
            ]);
            
            return "traduction reussie";
        }
    }
}
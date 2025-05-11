<?php
namespace App\Services;

use App\Models\TypeLogement;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class TypeLogementService
{
    // Colonnes sources
    private string $libelleColumn = 'libelle_logement';
    private string $standingColumn = 'standing';

    /**
     * Récupère les types, recherche sur libelle_logement via le paramètre 'libelle'.
     */
    public function getAllTypes(array $searchParams = []): Collection
    {
        Log::info('Service: Fetching TypeLogements', ['search' => $searchParams]);
        $query = TypeLogement::query();

        if (!empty($searchParams['libelle'])) {
            $searchTerm = '%' . strtolower($searchParams['libelle']) . '%';
            $query->whereRaw('LOWER('.$this->libelleColumn.') LIKE ?', [$searchTerm]);
        }

        return $query->orderBy($this->libelleColumn)->get();
    }

    /**
     * Crée un type. $data doit contenir 'libelle_logement', peut contenir 'standing'.
     */
    public function createType(array $data): TypeLogement
    {
        Log::info('Service: Creating TypeLogement.', ['data_keys' => array_keys($data)]);
        if (!isset($data[$this->libelleColumn])) {
             throw new \InvalidArgumentException("Le champ '{$this->libelleColumn}' est obligatoire.");
        }

        // Vérifier unicité libellé
        $existingLibelle = TypeLogement::whereRaw('LOWER('.$this->libelleColumn.') = ?', [strtolower($data[$this->libelleColumn])])->first();
         if ($existingLibelle) {
             throw new \InvalidArgumentException("Le libellé '{$data[$this->libelleColumn]}' existe déjà.");
         }

        // Vérifier unicité standing (si fourni et non vide)
        if (!empty($data[$this->standingColumn])) {
            $existingStanding = TypeLogement::whereRaw('LOWER('.$this->standingColumn.') = ?', [strtolower($data[$this->standingColumn])])->first();
            if ($existingStanding) {
                 // Adaptez la règle métier : erreur ou simple avertissement ?
                 throw new \InvalidArgumentException("Le standing '{$data[$this->standingColumn]}' existe déjà.");
             }
        }

        $type = TypeLogement::create($data); // Le modèle traduit
        Log::info('Service: TypeLogement created.', ['id' => $type->id]);
        return $type;
    }

    public function getTypeById(int $id): TypeLogement
    {
         Log::info('Service: Fetching TypeLogement by ID.', ['id' => $id]);
         try {
             return TypeLogement::findOrFail($id);
         } catch (ModelNotFoundException $e) {
             throw new ModelNotFoundException("Type de logement avec l'ID {$id} non trouvé.");
         }
    }

    /**
     * Met à jour un type. $data peut contenir 'libelle_logement', 'standing'.
     */
    public function updateType(int $id, array $data): TypeLogement
    {
        Log::info('Service: Updating TypeLogement.', ['id' => $id, 'data_keys' => array_keys($data)]);
        $type = $this->getTypeById($id);

        // Vérifier unicité si libelle_logement est modifié
         if (isset($data[$this->libelleColumn])) {
             $existingLibelle = TypeLogement::whereRaw('LOWER('.$this->libelleColumn.') = ?', [strtolower($data[$this->libelleColumn])])
                                   ->where('id', '!=', $id)
                                   ->first();
             if ($existingLibelle) {
                 throw new \InvalidArgumentException("Le libellé '{$data[$this->libelleColumn]}' est déjà utilisé.");
             }
         }

         // Vérifier unicité si standing est modifié (et non vide/null)
         if (array_key_exists($this->standingColumn, $data)) { // Vérifie si la clé existe (même si null)
            if (!empty($data[$this->standingColumn])) { // Si non vide, on vérifie l'unicité
                $existingStanding = TypeLogement::whereRaw('LOWER('.$this->standingColumn.') = ?', [strtolower($data[$this->standingColumn])])
                                        ->where('id', '!=', $id)
                                        ->first();
                if ($existingStanding) {
                    // Adaptez la règle métier : erreur ou avertissement ?
                    throw new \InvalidArgumentException("Le standing '{$data[$this->standingColumn]}' est déjà utilisé.");
                }
            }
            // Si $data[$this->standingColumn] est vide ou null, l'unicité n'est pas vérifiée, on mettra à jour.
         }

        $type->update($data); // Le modèle traduit
        Log::info('Service: TypeLogement updated.', ['id' => $id]);
        return $type->fresh();
    }

    public function deleteType(int $id): bool
    {
        Log::info('Service: Deleting TypeLogement.', ['id' => $id]);
        $type = $this->getTypeById($id);
        if ($type->logements()->exists()) {
             throw new \RuntimeException("Impossible de supprimer ce type car il est associé à des logements.");
        }
        $deleted = $type->delete();
        Log::info('Service: TypeLogement deleted.', ['id' => $id, 'result' => $deleted]);
        return $deleted;
    }
}

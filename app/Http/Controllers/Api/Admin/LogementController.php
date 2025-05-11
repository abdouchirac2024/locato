<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Logement; // Pour Route Model Binding
use App\Services\LogementService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use App\Http\Requests\Admin\StoreLogementAsAdminRequest; // Request pour création par admin
use App\Http\Resources\LogementResource; // Peut utiliser la même ressource
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\App; // Pour la locale dans show

class LogementController extends Controller
{
    protected $logementService;

    public function __construct(LogementService $logementService)
    {
        $this->logementService = $logementService;
        // Le middleware 'admin' est appliqué au niveau des routes (dans api.php)
    }

    /**
     * Crée un logement en tant qu'Admin (statut 'approuve').
     */
    public function store(StoreLogementAsAdminRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $images = $request->file('images'); // Récupère les fichiers images
            $logement = $this->logementService->createLogementAsAdmin($validatedData, $images);
            // Optionnel: Définir la locale pour la réponse
            $this->setLocaleFromHeader($request);
            return (new LogementResource($logement))
                    ->response()
                    ->setStatusCode(Response::HTTP_CREATED);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\RuntimeException $e) {
            Log::error("Admin/LogementController@store: Runtime Exception - " . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            Log::error("Admin/LogementController@store: General Exception - " . $e->getMessage());
            return response()->json(['message' => 'Erreur inattendue lors de la création du logement par l\'admin.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Liste les logements en attente de validation (non soft-deleted).
     */
    public function listPending(Request $request): JsonResponse
    {
        $this->setLocaleFromHeader($request);
        try {
            $perPage = $request->query('per_page', 15);
            $pendingLogements = $this->logementService->listPendingLogements((int)$perPage);
            return LogementResource::collection($pendingLogements)
                   ->response()
                   ->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error("Admin/LogementController@listPending: " . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération des logements en attente.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Lister tous les logements pour l'admin (y compris soft-deleted, avec filtres).
     */
    public function indexAll(Request $request): JsonResponse
    {
        $this->setLocaleFromHeader($request);
        try {
            $perPage = $request->query('per_page', 15);
            // Le service listAllLogementsForAdmin prend la Request entière pour les filtres
            $logements = $this->logementService->listAllLogementsForAdmin($request, (int)$perPage);
            return LogementResource::collection($logements)
                   ->response()
                   ->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error("Admin/LogementController@indexAll: " . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération de tous les logements.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Approuve un logement spécifique.
     * Utilise le Route Model Binding.
     */
    public function approve(Logement $logement): JsonResponse // Injection du modèle Logement
    {
        // $logement sera automatiquement résolu (même s'il est soft-deleted si la route est configurée pour)
        // ou une 404 sera levée si non trouvé (même avec trashed)
        try {
            // Le service peut restaurer si nécessaire avant d'approuver
            $approvedLogement = $this->logementService->approveLogement($logement);
            // Optionnel: Définir la locale pour la réponse
            // $this->setLocaleFromHeader(request()); // Si on veut respecter Accept-Language
            return response()->json([
                'message' => 'Logement approuvé avec succès.',
                'logement' => new LogementResource($approvedLogement)
            ]);
        } catch (\LogicException $e) { // Ex: Logement non en attente
             return response()->json(['message' => $e->getMessage()], Response::HTTP_CONFLICT); // 409
        } catch (\Exception $e) {
             Log::error("Admin/LogementController@approve({$logement->id}): " . $e->getMessage());
             return response()->json(['message' => 'Erreur lors de l\'approbation du logement.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Rejette un logement spécifique.
     * Utilise le Route Model Binding.
     */
    public function reject(Request $request, Logement $logement): JsonResponse // Injection
    {
         $validated = $request->validate([
             'notes' => ['nullable', 'string', 'max:1000']
         ]);
        try {
            $rejectedLogement = $this->logementService->rejectLogement($logement, $validated['notes'] ?? null);
            // $this->setLocaleFromHeader($request); // Pour la réponse
             return response()->json([
                 'message' => 'Logement rejeté avec succès.',
                 'logement' => new LogementResource($rejectedLogement)
             ]);
        } catch (\LogicException $e) { // Ex: Logement non en attente
             return response()->json(['message' => $e->getMessage()], Response::HTTP_CONFLICT); // 409
        } catch (\Exception $e) {
             Log::error("Admin/LogementController@reject({$logement->id}): " . $e->getMessage());
             return response()->json(['message' => 'Erreur lors du rejet du logement.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Affiche les détails d’un logement spécifique (Admin).
     * Le Route Model Binding peut nécessiter withTrashed() au niveau de la route.
     */
    public function show(Request $request, string $id): JsonResponse // Utilise l'ID ici pour withTrashed()
    {
        $this->setLocaleFromHeader($request);
        try {
            if (!ctype_digit($id) || (int)$id <= 0) { return response()->json(['message' => "ID invalide."], 400); }
            // Récupérer avec les soft-deleted pour l'admin
            $logement = Logement::withTrashed()->with(['typeLogement', 'quartier.ville', 'bailleur.user', 'imagesLogement', 'avis'])->findOrFail((int)$id);
            return response()->json(new LogementResource($logement));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Logement non trouvé.'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error("Admin/LogementController@show({$id}): " . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération du logement.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Restaure un logement "soft-deleted".
     */
    public function restore(string $id): JsonResponse
    {
        try {
            if (!ctype_digit($id) || (int)$id <= 0) { return response()->json(['message' => "ID invalide."], 400); }
            $logement = $this->logementService->restoreLogement((int)$id);
            // $this->setLocaleFromHeader(request()); // Pour la réponse
            return response()->json([
                'message' => 'Logement restauré avec succès.',
                'logement' => new LogementResource($logement)
            ]);
        } catch (ModelNotFoundException $e) { return response()->json(['message' => 'Logement à restaurer non trouvé.'], 404); }
          catch (\LogicException $e) { return response()->json(['message' => $e->getMessage()], 400); }
          catch (\Exception $e) { Log::error("Admin/LogementController@restore({$id}): " . $e->getMessage()); return response()->json(['message' => 'Erreur restauration.'], 500); }
    }

    /**
     * Supprime définitivement un logement.
     */
    public function forceDelete(string $id): JsonResponse
    {
        try {
            if (!ctype_digit($id) || (int)$id <= 0) { return response()->json(['message' => "ID invalide."], 400); }
            $this->logementService->forceDeleteLogement((int)$id);
            return response()->json(null, Response::HTTP_NO_CONTENT); // 204
        } catch (ModelNotFoundException $e) { return response()->json(['message' => 'Logement non trouvé.'], 404); }
          catch (\Exception $e) { Log::error("Admin/LogementController@forceDelete({$id}): " . $e->getMessage()); return response()->json(['message' => 'Erreur suppression définitive.'], 500); }
    }

    private function setLocaleFromHeader(Request $request): void
    {
        $localeHeader = $request->header('Accept-Language');
        $supportedLocales = config('app.available_locales', ['fr', 'en']);
        $defaultLocale = config('app.fallback_locale', 'fr');
        $locale = $defaultLocale;
        if ($localeHeader) {
            $preferredLocale = explode(',', $localeHeader)[0];
            $primaryLocale = strtolower(explode('-', $preferredLocale)[0]);
            if (in_array($primaryLocale, $supportedLocales)) { $locale = $primaryLocale; }
        }
        App::setLocale($locale);
    }
}

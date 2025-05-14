<?php

namespace App\Http\Controllers\Api;

use App\Models\Logement; // Utilisé pour le type-hinting dans les routes si Route Model Binding
use Illuminate\Http\Request;
use App\Services\LogementService;
use App\Http\Controllers\Controller;
use App\Http\Resources\LogementResource;
use App\Http\Requests\StoreLogementRequest;
use App\Http\Requests\UpdateLogementRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class LogementController extends Controller
{
    protected $logementService;

    public function __construct(LogementService $logementService)
    {
        $this->logementService = $logementService;
        // Authentification pour les actions d'écriture
        $this->middleware('auth:sanctum')->except(['index', 'show']);
    }

    /**
     * Affiche une liste paginée des logements PUBLICS (approuvés et non soft-deleted).
     */
    public function index(Request $request): JsonResponse
    {
        $this->setLocaleFromHeader($request);
        try {
            $perPage = $request->query('per_page', 15);
            $logements = $this->logementService->getAllLogements((int)$perPage);
            return LogementResource::collection($logements)
                   ->response()
                   ->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('LogementController@index: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération des logements.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Crée un nouveau logement (soumis par un Bailleur).
     * Gère l'upload d'images. Statut = 'en_attente'.
     */
    public function store(StoreLogementRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $images = $request->file('images');
            $logement = $this->logementService->createLogement($validatedData, $images);
            $this->setLocaleFromHeader($request);
            return (new LogementResource($logement))
                    ->response()
                    ->setStatusCode(Response::HTTP_CREATED);
        } catch (AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_FORBIDDEN);
        } catch (\RuntimeException | \Exception $e) {
            Log::error('LogementController@store: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage() ?: 'Erreur création logement.'], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Affiche un logement spécifique (public, si approuvé et non soft-deleted).
     * Pour voir un logement non approuvé ou soft-deleted, utiliser les routes admin.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $this->setLocaleFromHeader($request);
        try {
            if (!ctype_digit($id) || (int)$id <= 0) {
                return response()->json(['message' => "ID invalide."], Response::HTTP_BAD_REQUEST);
            }
            // Le service getLogementById ne trouvera que les logements non soft-deleted.
            // Et la liste publique ne montre que les approuvés.
            // Si on veut afficher un logement non approuvé via cet endpoint public, il faudrait
            // modifier la logique de getLogementById ou ajouter une condition ici.
            // Pour l'instant, on assume que le service gère la visibilité publique.
            $logement = $this->logementService->getLogementById((int)$id);

            // Vérifier si le logement est approuvé pour l'affichage public
             if ($logement->validation_status !== Logement::STATUS_APPROUVE) {
                 // Si l'utilisateur est le bailleur, il peut le voir, sinon 404
                 if (!Auth::check() || Auth::id() !== $logement->bailleur?->user_id) {
                     throw new ModelNotFoundException("Logement non trouvé ou non disponible pour affichage public.");
                 }
                 // Si c'est le bailleur, on le laisse voir son propre logement même non approuvé
             }

            return (new LogementResource($logement))
                    ->response()
                    ->setStatusCode(Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error("LogementController@show({$id}): " . $e->getMessage());
            return response()->json(['message' => 'Erreur récupération logement.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Met à jour un logement (par son propriétaire Bailleur).
     */
    public function update(UpdateLogementRequest $request, string $id): JsonResponse
    {
        try {
            if (!ctype_digit($id) || (int)$id <= 0) { return response()->json(['message' => "ID invalide."], 400); }
            $validatedData = $request->validated();
            $logement = $this->logementService->updateLogement((int)$id, $validatedData);
            $this->setLocaleFromHeader($request);
            return (new LogementResource($logement))->response()->setStatusCode(Response::HTTP_OK);
        } catch (ModelNotFoundException $e) { return response()->json(['message' => $e->getMessage()], 404); }
          catch (AuthorizationException $e) { return response()->json(['message' => $e->getMessage()], 403); }
          catch (\RuntimeException | \Exception $e) { Log::error("LogementController@update({$id}): " . $e->getMessage()); return response()->json(['message' => $e->getMessage() ?: 'Erreur mise à jour.'], 400); }
    }

    /**
     * "Supprime" logiquement un logement (par son propriétaire Bailleur).
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            if (!ctype_digit($id) || (int)$id <= 0) { return response()->json(['message' => "ID invalide."], 400); }
            $deleted = $this->logementService->deleteLogement((int)$id); // Effectue un soft delete
            if ($deleted) {
                 return response()->json(null, Response::HTTP_NO_CONTENT);
            }
            return response()->json(['message' => 'La suppression logique a échoué.'], 500);
        } catch (ModelNotFoundException $e) { return response()->json(['message' => $e->getMessage()], 404); }
          catch (AuthorizationException $e) { return response()->json(['message' => $e->getMessage()], 403); }
          catch (\RuntimeException | \Exception $e) { Log::error("LogementController@destroy({$id}): " . $e->getMessage()); return response()->json(['message' => $e->getMessage() ?: 'Erreur suppression.'], 500); }
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

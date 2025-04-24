<?php

namespace App\Http\Controllers\Api;

use App\Models\Logement;
use Illuminate\Http\Request;
use App\Services\LogementService; // Service injecté
use App\Http\Controllers\Controller;
use App\Http\Resources\LogementResource; // Ressource pour le formatage
use App\Http\Requests\StoreLogementRequest; // Validation création
use App\Http\Requests\UpdateLogementRequest; // Validation mise à jour
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response; // Pour les constantes HTTP_...
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Access\AuthorizationException; // Le service peut la lancer
use Illuminate\Support\Facades\App; // Pour définir la locale dynamiquement
use Illuminate\Support\Facades\Log; // Pour le logging
// Auth n'est plus directement utilisé ici, mais le service en dépend
// use Illuminate\Support\Facades\Auth;

class LogementController extends Controller
{
    protected $logementService;

    // Injection de dépendance du LogementService
    public function __construct(LogementService $logementService)
    {
        $this->logementService = $logementService;

        // --- AUTHENTIFICATION RÉACTIVÉE ---
        // Applique le middleware d'authentification Sanctum aux méthodes
        // 'store', 'update', et 'destroy'. Les méthodes 'index' et 'show'
        // restent publiques grâce à 'except'.
        $this->middleware('auth:sanctum')->except(['index', 'show']);
        // --- ---
    }

    /**
     * Affiche une liste paginée des logements.
     * Gère le header Accept-Language pour définir la locale de la réponse.
     * (Reste publique)
     */
    public function index(Request $request): JsonResponse
    {
        $this->setLocaleFromHeader($request);

        try {
            $perPage = $request->query('per_page', 15);
            $logements = $this->logementService->getAllLogements((int)$perPage);

            return LogementResource::collection($logements)
                   ->response()
                   ->setStatusCode(Response::HTTP_OK); // 200

        } catch (\Exception $e) {
            Log::error('Error in LogementController@index: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération des logements.'], Response::HTTP_INTERNAL_SERVER_ERROR); // 500
        }
    }

    /**
     * Crée et sauvegarde un nouveau logement.
     * Protégé par 'auth:sanctum'.
     * Utilise StoreLogementRequest pour la validation et l'autorisation de base.
     */
    public function store(StoreLogementRequest $request): JsonResponse
    {
        // La validation et l'autorisation (est connecté ? est Bailleur ?) sont gérées
        // par le middleware et/ou StoreLogementRequest::authorize()
        try {
            $validatedData = $request->validated();
            // Le service va maintenant utiliser Auth::user() correctement
            $logement = $this->logementService->createLogement($validatedData);

            return (new LogementResource($logement))
                    ->response()
                    ->setStatusCode(Response::HTTP_CREATED); // 201

        } catch (AuthorizationException $e) {
             // Si StoreLogementRequest::authorize() renvoie false
            return response()->json(['message' => $e->getMessage()], Response::HTTP_FORBIDDEN); // 403
        } catch (\Exception $e) {
            Log::error('Error in LogementController@store: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de la création du logement.',
                'error_details' => $e->getMessage()
                ], Response::HTTP_BAD_REQUEST); // 400 ou 500
        }
    }

    /**
     * Affiche un logement spécifique.
     * Gère le header Accept-Language pour définir la locale de la réponse.
     * (Reste publique)
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $this->setLocaleFromHeader($request);

        try {
            if (!ctype_digit($id) || (int)$id <= 0) {
                 return response()->json(['message' => "L'ID du logement doit être un entier positif."], Response::HTTP_BAD_REQUEST); // 400
             }
            $logement = $this->logementService->getLogementById((int)$id);

            return (new LogementResource($logement))
                    ->response()
                    ->setStatusCode(Response::HTTP_OK); // 200

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_NOT_FOUND); // 404
        } catch (\Exception $e) {
             Log::error("Error in LogementController@show for ID {$id}: " . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération du logement.'], Response::HTTP_INTERNAL_SERVER_ERROR); // 500
        }
    }

    /**
     * Met à jour un logement spécifique.
     * Protégé par 'auth:sanctum'.
     * Utilise UpdateLogementRequest pour la validation et l'autorisation de base.
     * Le service vérifie la propriété du logement.
     */
    public function update(UpdateLogementRequest $request, string $id): JsonResponse
    {
        // La validation et l'autorisation sont gérées par le middleware et/ou UpdateLogementRequest
        try {
             if (!ctype_digit($id) || (int)$id <= 0) {
                 return response()->json(['message' => "L'ID du logement doit être un entier positif."], Response::HTTP_BAD_REQUEST); // 400
             }
            $validatedData = $request->validated();
            // Le service utilisera Auth::user() pour vérifier la propriété
            $logement = $this->logementService->updateLogement((int)$id, $validatedData);

            return (new LogementResource($logement))
                    ->response()
                    ->setStatusCode(Response::HTTP_OK); // 200

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_NOT_FOUND); // 404
        } catch (AuthorizationException $e) {
             // Peut venir de UpdateLogementRequest::authorize() OU du LogementService
            return response()->json(['message' => $e->getMessage()], Response::HTTP_FORBIDDEN); // 403
        } catch (\Exception $e) {
            Log::error("Error in LogementController@update for ID {$id}: " . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du logement.',
                'error_details' => $e->getMessage()
                ], Response::HTTP_BAD_REQUEST); // 400 ou 500
        }
    }

    /**
     * Supprime un logement spécifique.
     * Protégé par 'auth:sanctum'.
     * Le service vérifie la propriété du logement.
     */
    public function destroy(string $id): JsonResponse
    {
        // L'authentification est gérée par le middleware
        try {
             if (!ctype_digit($id) || (int)$id <= 0) {
                 return response()->json(['message' => "L'ID du logement doit être un entier positif."], Response::HTTP_BAD_REQUEST); // 400
             }
             // Le service utilisera Auth::user() pour vérifier la propriété
            $this->logementService->deleteLogement((int)$id);

            return response()->json(null, Response::HTTP_NO_CONTENT); // 204

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_NOT_FOUND); // 404
        } catch (AuthorizationException $e) {
             // Le service a déterminé que l'utilisateur n'est pas propriétaire
            return response()->json(['message' => $e->getMessage()], Response::HTTP_FORBIDDEN); // 403
        } catch (\Exception $e) {
            Log::error("Error in LogementController@destroy for ID {$id}: " . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de la suppression du logement.',
                 'error_details' => $e->getMessage()
                 ], Response::HTTP_INTERNAL_SERVER_ERROR); // 500
        }
    }

    /**
     * Méthode privée pour définir la locale basée sur le header Accept-Language.
     */
    private function setLocaleFromHeader(Request $request): void
    {
        $localeHeader = $request->header('Accept-Language');
        $supportedLocales = config('app.available_locales', ['fr', 'en']);
        $defaultLocale = config('app.fallback_locale', 'fr');
        $locale = $defaultLocale;

        if ($localeHeader) {
            $preferredLocale = explode(',', $localeHeader)[0];
            $primaryLocale = strtolower(explode('-', $preferredLocale)[0]);
            if (in_array($primaryLocale, $supportedLocales)) {
                $locale = $primaryLocale;
            }
        }
        App::setLocale($locale);
    }
}

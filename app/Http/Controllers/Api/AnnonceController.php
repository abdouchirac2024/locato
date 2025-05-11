<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreAnnonceRequest;
use App\Http\Requests\UpdateAnnonceRequest;
use App\Services\AnnonceService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log; 
use App\Http\Resources\AnnonceResource;
use Illuminate\Http\Response;

class AnnonceController extends Controller
{
    protected $Annonceservice;

    public function __construct(AnnonceService $Annonceservice)
    {
        $this->Annonceservice = $Annonceservice;
    }

    /**
     * Liste toutes les Annonces
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Pagination par 10
            $perPage = $request->query('per_page', 10);
            $annonces = $this->Annonceservice->getAnnonces((int)$perPage);

            return AnnonceResource::collection($annonces)
                   ->response()
                   ->setStatusCode(Response::HTTP_OK); // 200

        } catch (\Exception $e) {
            Log::error('Error in AnnonceController@index: ' . $e->getMessage());
            return response()->json(
                ['message' => 'Erreur lors de la récupération des annonces.'], 
                Response::HTTP_INTERNAL_SERVER_ERROR
            ); // 500
        }
    }

    /**
     * Crée une Annonce
     */
    public function store(StoreAnnonceRequest $request): JsonResponse
    { 
        try {
            $validatedData = $request->validated();
            $annonce = $this->Annonceservice->createAnnonce($validatedData);

            return (new AnnonceResource($annonce))
                    ->response()
                    ->setStatusCode(Response::HTTP_CREATED); // 201

        } catch (AuthorizationException $e) {
            return response()->json(
                ['message' => $e->getMessage()], 
                Response::HTTP_FORBIDDEN
            ); // 403
        } catch (\Exception $e) {
            Log::error('Error in AnnonceController@store: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de la création d une annonce.',
                'error_details' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST); // 400 ou 500
        }
    }

    /**
     * Retourne une Annonce
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $annonce = $this->Annonceservice->getAnnonceById((int)$id);

            return (new AnnonceResource($annonce))
                    ->response()
                    ->setStatusCode(Response::HTTP_OK); // 200

        } catch (ModelNotFoundException $e) {
            return response()->json(
                ['message' => $e->getMessage()], 
                Response::HTTP_NOT_FOUND
            ); // 404
        } catch (\Exception $e) {
            Log::error("Error in AnnonceController@show for ID {$id}: " . $e->getMessage());
            return response()->json(
                ['message' => 'Erreur lors de la récupération du logement.'], 
                Response::HTTP_INTERNAL_SERVER_ERROR
            ); // 500
        }
    }

    /**
     * Modifier une Annonce
     */
    public function update(UpdateAnnonceRequest $request, string $id): JsonResponse
    {
        $confirm = false;
        try {
            $validatedData = $request->validated();
            $annonce = $this->Annonceservice->updateAnnonce((int)$id, $validatedData, $confirm);

            return (new AnnonceResource($annonce))
                    ->response()
                    ->setStatusCode(Response::HTTP_OK); // 200

        } catch (ModelNotFoundException $e) {
            return response()->json(
                ['message' => $e->getMessage()], 
                Response::HTTP_NOT_FOUND
            ); // 404
        }
    }

    /**
     * Supprime une annonce spécifique
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->Annonceservice->deleteAnnonce((int)$id);

            return response()->json(null, Response::HTTP_NO_CONTENT); // 204

        } catch (\Exception $e) {
            Log::error("Error in LogementController@destroy for ID {$id}: " . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de la suppression du logement.',
                'error_details' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR); // 500
        }
    }

    /**
     * Crée une Annonce par un bailleur
     */
   /**
 * Crée une annonce pour un bailleur authentifié
 *
 * @param StoreAnnonceRequest $request Requête validée
 * @return JsonResponse
 */
public function storeByBailleur(StoreAnnonceRequest $request): JsonResponse
{ 
    try {
        // Validation des données
        $validatedData = $request->validated();
        
        // Récupération et vérification de l'utilisateur
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(
                ['message' => 'Authentification requise'], 
                Response::HTTP_UNAUTHORIZED
            );
        }

        // Vérification du rôle bailleur
        if (!$user->bailleur) {
            return response()->json(
                ['message' => 'Accès réservé aux bailleurs'], 
                Response::HTTP_FORBIDDEN
            );
        }

        // Ajout de l'ID bailleur aux données validées
        $validatedData['bailId'] = $user->bailleur->id;
        
        // Création de l'annonce via le service
        $annonce = $this->Annonceservice->createAnnonceByBailleur($validatedData);

        // Retour de la réponse avec la ressource
        return (new AnnonceResource($annonce))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);

    } catch (AuthorizationException $e) {
        // Gestion des erreurs d'autorisation
        return response()->json(
            ['message' => $e->getMessage()], 
            Response::HTTP_FORBIDDEN
        );
    } catch (\Exception $e) {
        // Journalisation et retour des erreurs générales
        Log::error('Annonce creation failed', [
            'error' => $e->getMessage(),
            'user_id' => $user->id ?? null,
            'data' => $validatedData
        ]);
        
        return response()->json([
            'message' => 'Échec de la création de l\'annonce',
            'error' => config('app.debug') ? $e->getMessage() : null
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
    /**
     * Confirmer une annonce
     */
    public function confirmAnnonce(UpdateAnnonceRequest $request, string $id): JsonResponse
    {
        $confirm = true;
        try {
            $validatedData = $request->validated();
            $annonce = $this->Annonceservice->updateAnnonce((int)$id, $validatedData, $confirm);

            return (new AnnonceResource($annonce))
                    ->response()
                    ->setStatusCode(Response::HTTP_OK); // 200

        } catch (ModelNotFoundException $e) {
            return response()->json(
                ['message' => $e->getMessage()], 
                Response::HTTP_NOT_FOUND
            ); // 404
        }
    }
}
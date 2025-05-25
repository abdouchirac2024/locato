<?php
namespace App\Http\Controllers\Api\Visite; // Namespace pour les contrôleurs de Visite

use App\Http\Controllers\Controller;
use App\Models\Visite;
use App\Models\Logement; // Pour vérifier la propriété du logement par le bailleur
use App\Services\VisiteService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use App\Http\Requests\Visite\StoreVisiteRequest;
use App\Http\Requests\Visite\UpdateVisiteStatusRequest;
use App\Http\Resources\VisiteResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class VisiteController extends Controller
{
    protected $visiteService;

    public function __construct(VisiteService $visiteService)
    {
        $this->visiteService = $visiteService;
        // Appliquer le middleware d'authentification à toutes les méthodes
        $this->middleware('auth:sanctum');
    }

    /**
     * Un locataire demande une visite.
     */
    public function store(StoreVisiteRequest $request): JsonResponse
    {
        // L'autorisation (est locataire ?) est gérée par le FormRequest
        try {
            $visite = $this->visiteService->requestVisite($request->validated());
            return (new VisiteResource($visite))
                    ->response()
                    ->setStatusCode(Response::HTTP_CREATED);
        } catch (AuthorizationException $e) {
             return response()->json(['message' => $e->getMessage()], Response::HTTP_FORBIDDEN);
        } catch (\LogicException | \InvalidArgumentException $e) { // Ex: Visite déjà existante, date passée
             return response()->json(['message' => $e->getMessage()], Response::HTTP_CONFLICT); // 409 ou 422
        } catch (\Exception $e) {
            Log::error("VisiteController@store: " . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la demande de visite.'], 500);
        }
    }

    /**
     * Un locataire liste ses propres demandes de visite.
     */
    public function listMyVisites(Request $request): JsonResponse
    {
        try {
            $perPage = $request->query('per_page', 10);
            $visites = $this->visiteService->getMyVisiteRequests((int)$perPage);
            return VisiteResource::collection($visites)->response()->setStatusCode(Response::HTTP_OK);
        } catch (AuthorizationException $e) { // Si pas locataire (devrait être bloqué par service)
            return response()->json(['message' => $e->getMessage()], Response::HTTP_FORBIDDEN);
        } catch (\Exception $e) {
            Log::error("VisiteController@listMyVisites: " . $e->getMessage());
            return response()->json(['message' => 'Erreur récupération de vos demandes de visite.'], 500);
        }
    }

    /**
     * Un bailleur liste les demandes de visite pour un de SES logements.
     */
    public function listRequestsForLogement(Request $request, int $logementId): JsonResponse
    {
        try {
            $perPage = $request->query('per_page', 10);
            $visites = $this->visiteService->getVisiteRequestsForLogement($logementId, (int)$perPage);
            return VisiteResource::collection($visites)->response()->setStatusCode(Response::HTTP_OK);
        } catch (ModelNotFoundException $e) { // Si le logement n'existe pas
            return response()->json(['message' => "Logement non trouvé."], Response::HTTP_NOT_FOUND);
        } catch (AuthorizationException $e) { // Si pas le bailleur du logement
            return response()->json(['message' => $e->getMessage()], Response::HTTP_FORBIDDEN);
        } catch (\Exception $e) {
            Log::error("VisiteController@listRequestsForLogement({$logementId}): " . $e->getMessage());
            return response()->json(['message' => 'Erreur récupération des demandes pour ce logement.'], 500);
        }
    }

    /**
     * Un bailleur met à jour le statut d'une visite.
     * Utilise le Route Model Binding pour $visite.
     */
    public function updateStatus(UpdateVisiteStatusRequest $request, Visite $visite): JsonResponse
    {
         // L'autorisation (est bailleur ?) est gérée par UpdateVisiteStatusRequest
         // La propriété de la visite (via le logement) est gérée dans le service.
        try {
            $validated = $request->validated();
            $updatedVisite = $this->visiteService->updateVisiteStatus(
                $visite,
                $validated['new_status_fr'],
                $validated['commentaire_bailleur'] ?? null,
                $validated['date_proposee_bailleur'] ?? null,
                $validated['heure_proposee_bailleur'] ?? null
            );
            return (new VisiteResource($updatedVisite))->response()->setStatusCode(Response::HTTP_OK);
        } catch (AuthorizationException $e) {
             return response()->json(['message' => $e->getMessage()], Response::HTTP_FORBIDDEN);
        } catch (\LogicException | \InvalidArgumentException $e) {
             return response()->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST); // 400 ou 409
        } catch (\Exception $e) {
            Log::error("VisiteController@updateStatus({$visite->id}): " . $e->getMessage());
            return response()->json(['message' => 'Erreur mise à jour statut visite.'], 500);
        }
    }

    /**
     * Un locataire annule sa propre demande de visite.
     * Utilise le Route Model Binding pour $visite.
     */
    public function cancelByLocataire(Visite $visite): JsonResponse
    {
        try {
            $cancelledVisite = $this->visiteService->cancelVisiteByLocataire($visite);
             return response()->json([
                'message' => 'Demande de visite annulée.',
                'visite' => new VisiteResource($cancelledVisite)
            ]);
        } catch (AuthorizationException $e) {
             return response()->json(['message' => $e->getMessage()], Response::HTTP_FORBIDDEN);
        } catch (\LogicException $e) { // Si ne peut pas annuler
             return response()->json(['message' => $e->getMessage()], Response::HTTP_CONFLICT);
        } catch (\Exception $e) {
            Log::error("VisiteController@cancelByLocataire({$visite->id}): " . $e->getMessage());
            return response()->json(['message' => 'Erreur annulation visite.'], 500);
        }
    }

    // Un admin pourrait avoir un show() ou un index() pour voir toutes les visites
    // public function adminShow(Visite $visite) { ... }
    // public function adminIndex(Request $request) { ... }
}
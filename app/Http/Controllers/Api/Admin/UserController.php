<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuthService; // Ou un UserService dédié si vous en avez un
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use App\Http\Requests\Admin\UpdateUserRoleRequest;
use App\Http\Resources\UserResource; // Ressource standard pour l'utilisateur
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends Controller
{
    protected $authService; // Ou UserService

    // Injecter le service nécessaire
    public function __construct(AuthService $authService) // Adaptez si vous utilisez un UserService
    {
        $this->authService = $authService;
        // Le middleware 'admin' est appliqué au niveau des routes
    }

    /**
     * Met à jour le rôle d'un utilisateur spécifique.
     * L'ID de l'utilisateur à mettre à jour vient de la route.
     */
    public function updateUserRole(UpdateUserRoleRequest $request, string $userId): JsonResponse
    {
        try {
            if (!ctype_digit($userId) || (int)$userId <= 0) {
                return response()->json(['message' => "ID utilisateur invalide."], Response::HTTP_BAD_REQUEST);
            }

            // Interdire à l'admin de changer son propre rôle via cet endpoint (sécurité)
            if ((int)$userId === Auth::id()) {
                 return response()->json(['message' => "Vous ne pouvez pas modifier votre propre rôle via cet endpoint."], Response::HTTP_FORBIDDEN);
            }

            $user = $this->authService->updateUserRole((int)$userId, $request->validated()['role']);

            return response()->json([
                'message' => 'Rôle de l\'utilisateur mis à jour avec succès.',
                'user' => new UserResource($user)
            ], Response::HTTP_OK);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => "Utilisateur non trouvé."], Response::HTTP_NOT_FOUND);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            Log::error("Admin/UserController@updateUserRole: " . $e->getMessage(), ['user_id' => $userId]);
            return response()->json(['message' => 'Erreur lors de la mise à jour du rôle.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // TODO: Ajouter d'autres méthodes admin pour les utilisateurs si nécessaire (index, show, status, etc.)
    // Par exemple, la méthode updateUserStatus que vous aviez dans votre AuthController
    // pourrait être déplacée ici pour une meilleure séparation des préoccupations.

    /**
     * Met à jour le statut (active/inactive) d'un utilisateur.
     */
    public function updateUserStatus(Request $request, string $userId): JsonResponse
    {
         $request->validate([
             'status' => ['required', Rule::in(['active', 'inactive'])],
         ]);

         try {
             if (!ctype_digit($userId) || (int)$userId <= 0) { return response()->json(['message' => "ID invalide."], 400); }

             $user = User::findOrFail((int)$userId);

             // Empêcher l'admin de désactiver son propre compte
             if ($user->id === Auth::id() && $request->status === 'inactive') {
                  return response()->json(['message' => "Vous ne pouvez pas désactiver votre propre compte."], 403);
             }

             $user->status = $request->status;
             $user->save();

             return response()->json([
                 'message' => 'Statut de l\'utilisateur mis à jour.',
                 'user' => new UserResource($user)
             ]);

         } catch (ModelNotFoundException $e) {
             return response()->json(['message' => "Utilisateur non trouvé."], 404);
         } catch (\Exception $e) {
              Log::error("Admin/UserController@updateUserStatus: " . $e->getMessage(), ['user_id' => $userId]);
              return response()->json(['message' => 'Erreur lors de la mise à jour du statut.'], 500);
         }
    }
}
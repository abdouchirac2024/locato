<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\VerifyBailleurRequest;

class BailleurController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
        $this->middleware('auth:sanctum');
        $this->middleware('admin');
    }

    public function verify(VerifyBailleurRequest $request): JsonResponse
    {
        $user = User::findOrFail($request->user_id);

        if ($this->authService->verifyBailleur($user)) {
            return response()->json([
                'message' => 'Le bailleur a été vérifié avec succès. Un email a été envoyé avec les nouvelles informations de connexion.'
            ]);
        }

        return response()->json([
            'message' => 'Impossible de vérifier ce bailleur. Vérifiez que l\'utilisateur est bien un bailleur.'
        ], 400);
    }
} 
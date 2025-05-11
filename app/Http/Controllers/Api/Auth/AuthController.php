<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use App\Mail\VerificationCodeMail;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Enregistrement d'un nouvel utilisateur
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register($request->validated());

        return response()->json([
            'message' => 'Inscription réussie. Un code de vérification a été envoyé à votre email.',
            'user' => new UserResource($user),
        ], 201);
    }

    /**
     * Vérification de l'email avec le code
     */
    public function verifyEmail(VerifyEmailRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Utilisateur non trouvé'
            ], 404);
        }

        if ($user->verification_code !== $request->code) {
            return response()->json([
                'status' => 'error',
                'message' => 'Code de vérification incorrect'
            ], 422);
        }

        $user->update([
            'email_verified_at' => now(),
            'verification_code' => null,
            'status' => 'active'  // Activer l'utilisateur après vérification
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Email vérifié avec succès'
        ]);
    }

    /**
     * Connexion de l'utilisateur
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->login($request->login, $request->password);
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Connexion réussie',
                'user' => new UserResource($user),
                'token' => $token
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Déconnexion de l'utilisateur
     */
    public function logout(): JsonResponse
    {
        auth()->user()->tokens()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie'
        ]);
    }

    /**
     * Récupérer les informations de l'utilisateur connecté
     */
    public function user(): JsonResponse
    {
        return response()->json([
            'user' => new UserResource(auth()->user())
        ]);
    }

    /**
     * Mettre à jour le profil de l'utilisateur
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->authService->updateProfile(auth()->user(), $request->validated());

        return response()->json([
            'message' => 'Profil mis à jour avec succès',
            'user' => new UserResource($user)
        ]);
    }

    /**
     * Renvoyer le code de vérification
     */
    public function resendVerificationCode(): JsonResponse
    {
        $user = auth()->user();
        
        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'Votre email est déjà vérifié'
            ], 400);
        }

        $user->verification_code = rand(1000, 9999);
        $user->save();

        if ($user->email) {
            try {
                Mail::to($user->email)->send(new VerificationCodeMail($user->verification_code));
                return response()->json([
                    'message' => 'Un nouveau code de vérification a été envoyé à votre email'
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Erreur lors de l\'envoi du code de vérification'
                ], 500);
            }
        }

        return response()->json([
            'message' => 'Aucun email associé à ce compte'
        ], 400);
    }

    public function updateUserStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:active,inactive'
        ]);

        $user = User::findOrFail($id);
        $user->update(['status' => $request->status]);

        return response()->json([
            'status' => 'success',
            'message' => 'Statut de l\'utilisateur mis à jour avec succès',
            'data' => new UserResource($user)
        ]);
    }
} 
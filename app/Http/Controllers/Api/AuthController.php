<?php

namespace App\Http\Controllers\Api;

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
        $user = User::where('email', $request->email)
            ->orWhere('telephone', $request->telephone)
            ->firstOrFail();

        if ($this->authService->verifyEmail($user, $request->code)) {
            return response()->json([
                'message' => 'Email vérifié avec succès. Bienvenue!',
                'user' => new UserResource($user),
            ]);
        }

        return response()->json([
            'message' => 'Code de vérification invalide',
        ], 422);
    }

    /**
     * Connexion de l'utilisateur
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->login($request->login, $request->password);

            if (!$user) {
                return response()->json([
                    'message' => 'Identifiants incorrects',
                ], 401);
            }

            // Vérifier si l'email est vérifié (si email existe)
            if ($user->email && !$user->hasVerifiedEmail()) {
                return response()->json([
                    'message' => 'Veuillez vérifier votre email avant de vous connecter',
                    'user' => new UserResource($user),
                ], 403);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Connexion réussie',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => new UserResource($user),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 401);
        }
    }

    /**
     * Déconnexion de l'utilisateur
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie',
        ]);
    }


    
    /**
     * Récupérer l'utilisateur connecté
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()),
        ]);
    }

    /**
     * Mettre à jour le profil de l'utilisateur
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->authService->updateProfile($request->user(), $request->validated());

        return response()->json([
            'message' => 'Profil mis à jour avec succès',
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Envoyer à nouveau le code de vérification
     */
    public function resendVerificationCode(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email déjà vérifié',
            ], 400);
        }

        $user->update(['verification_code' => rand(1000, 9999)]);

        if ($user->email) {
            Mail::to($user->email)->send(new VerificationCodeMail($user->verification_code));
        }

        return response()->json([
            'message' => 'Un nouveau code de vérification a été envoyé à votre email',
        ]);
    }
}

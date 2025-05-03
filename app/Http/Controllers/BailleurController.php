<?php

namespace App\Http\Controllers;

use App\Models\Bailleur;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BailleurController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function index(Request $request)
    {
        try {
            $query = Bailleur::with('user');

            // Filtre par statut
            if ($request->has('statut')) {
                $statut = $request->get('statut');
                if ($statut === 'en_attente') {
                    $query->where('verif', false)
                          ->orWhere('statut_fr', 'en_attente');
                } elseif ($statut === 'verifie') {
                    $query->where('verif', true)
                          ->where('statut_fr', 'verifie');
                }
            }

            // Filtre par nom ou email
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->whereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $bailleurs = $query->paginate(10);

            return response()->json([
                'status' => 'success',
                'data' => $bailleurs
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des bailleurs', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la récupération des bailleurs'
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $bailleur = Bailleur::findOrFail($id);
            $user = $bailleur->user;

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Utilisateur non trouvé'
                ], 404);
            }

            $action = $request->get('action');

            if ($action === 'verify') {
                $this->authService->verifyBailleur($user);
                $message = 'Bailleur vérifié avec succès';
            } elseif ($action === 'unverify') {
                $bailleur->verif = false;
                $bailleur->statut_fr = 'en_attente';
                $bailleur->statut_en = 'pending';
                $bailleur->save();
                $message = 'Bailleur mis en attente avec succès';
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Action non valide'
                ], 400);
            }

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => $bailleur->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du statut du bailleur', [
                'bailleur_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la mise à jour du statut'
            ], 500);
        }
    }
} 
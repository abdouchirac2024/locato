<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Avis;
use App\Models\Location;
use App\Models\Logement;
use App\Models\Visite;
use App\Models\Locataire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AvisController extends Controller
{
    /**
     * Créer un nouvel avis
     */
    public function store(Request $request)
    {
        // Vérifier si l'utilisateur est un locataire
        $locataire = Locataire::where('user_id', Auth::id())->first();
        if (!$locataire) {
            return response()->json([
                'message' => 'Seuls les locataires peuvent donner des avis.',
                'errors' => [
                    'user' => ['Vous devez être un locataire pour pouvoir donner un avis.']
                ]
            ], 403);
        }

        // Validation des données
        $validator = Validator::make($request->all(), [
            'logId' => 'required|integer|exists:logements,id',
            'coment_fr' => 'required|string|min:10',
            'coment_en' => 'nullable|string|min:10',
            'note' => 'required|integer|min:1|max:5',
            'visible' => 'boolean',
            'dateEnv' => 'nullable|date'
        ], [
            'logId.required' => 'L\'identifiant du logement est requis.',
            'logId.integer' => 'L\'identifiant du logement doit être un nombre entier.',
            'logId.exists' => 'Le logement spécifié n\'existe pas.',
            'coment_fr.required' => 'Le commentaire en français est requis.',
            'coment_fr.min' => 'Le commentaire en français doit contenir au moins 10 caractères.',
            'coment_en.min' => 'Le commentaire en anglais doit contenir au moins 10 caractères.',
            'note.required' => 'La note est requise.',
            'note.integer' => 'La note doit être un nombre entier.',
            'note.min' => 'La note doit être comprise entre 1 et 5.',
            'note.max' => 'La note doit être comprise entre 1 et 5.',
            'visible.boolean' => 'Le champ visible doit être un booléen.',
            'dateEnv.date' => 'La date doit être au format valide.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Les données fournies sont invalides.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Vérifier si le logement a été loué par ce locataire
        $location = Location::where('logId', $request->logId)
            ->where('locaId', $locataire->id)
            ->first();

        // Vérifier si le locataire a visité le logement
        $visite = Visite::where('logId', $request->logId)
            ->where('locaId', $locataire->id)
            ->first();

        // Vérifier les conditions pour donner un avis
        if (!$location && !$visite) {
            return response()->json([
                'message' => 'Vous ne pouvez pas donner un avis pour ce logement car vous ne l\'avez ni loué ni visité.',
                'errors' => [
                    'logId' => ['Vous devez avoir loué ou visité ce logement pour pouvoir donner un avis.']
                ]
            ], 403);
        }

        // Vérifier si le locataire a déjà donné un avis pour ce logement
        $existingAvis = Avis::where('logId', $request->logId)
            ->where('locaId', $locataire->id)
            ->first();

        if ($existingAvis) {
            return response()->json([
                'message' => 'Vous avez déjà donné un avis pour ce logement.',
                'errors' => [
                    'logId' => ['Un avis existe déjà pour ce logement.']
                ]
            ], 403);
        }

        // Créer l'avis
        $avis = Avis::create([
            'logId' => $request->logId,
            'locaId' => $locataire->id,
            'coment_fr' => $request->coment_fr,
            'coment_en' => $request->coment_en,
            'note' => $request->note,
            'visible' => $request->visible ?? true,
            'dateEnv' => $request->dateEnv ?? now(),
        ]);

        return response()->json([
            'message' => 'Avis créé avec succès',
            'avis' => $avis->load('locataire') // Charger les informations du locataire
        ], 201);
    }

    /**
     * Récupérer les avis d'un logement avec filtrage
     */
    public function index(Request $request)
    {
        $query = Avis::with(['locataire', 'logement'])
            ->where('visible', true);

        // Filtrage par logement
        if ($request->has('logId')) {
            $query->where('logId', $request->logId);
        }

        // Filtrage par note
        if ($request->has('note')) {
            $query->where('note', $request->note);
        }

        // Filtrage par date
        if ($request->has('date_debut')) {
            $query->where('dateEnv', '>=', $request->date_debut);
        }
        if ($request->has('date_fin')) {
            $query->where('dateEnv', '<=', $request->date_fin);
        }

        // Tri
        $sortBy = $request->get('sort_by', 'dateEnv');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $avis = $query->paginate(10);

        return response()->json($avis);
    }

    /**
     * Récupérer un avis spécifique
     */
    public function show($id)
    {
        try {
            $avis = Avis::withTrashed()
                ->with(['locataire', 'logement'])
                ->findOrFail($id);

            // Check if the avis is soft deleted
            if ($avis->trashed()) {
                return response()->json([
                    'message' => 'Cet avis a été supprimé.',
                    'error' => 'avis_deleted'
                ], 404);
            }

            // Check if the avis is not visible (skip this check for admins)
            if (!$avis->visible && !Auth::user()->isAdmin()) {
                return response()->json([
                    'message' => 'Cet avis n\'est pas visible.',
                    'error' => 'avis_not_visible'
                ], 404);
            }

            return response()->json($avis);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Avis non trouvé.',
                'error' => 'avis_not_found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de la récupération de l\'avis.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Modifier un avis existant
     */
    public function update(Request $request, $id)
    {
        // Vérifier si l'utilisateur est un locataire
        $locataire = Locataire::where('user_id', Auth::id())->first();
        if (!$locataire) {
            return response()->json([
                'message' => 'Seuls les locataires peuvent modifier des avis.',
                'errors' => [
                    'user' => ['Vous devez être un locataire pour pouvoir modifier un avis.']
                ]
            ], 403);
        }

        // Trouver l'avis
        $avis = Avis::withTrashed()->findOrFail($id);

        // Vérifier si l'avis est supprimé
        if ($avis->trashed()) {
            return response()->json([
                'message' => 'Cet avis a été supprimé.',
                'errors' => [
                    'avis' => ['Vous ne pouvez pas modifier un avis supprimé.']
                ]
            ], 403);
        }

        // Vérifier si l'utilisateur est le propriétaire de l'avis
        if ($avis->locaId !== $locataire->id) {
            return response()->json([
                'message' => 'Vous n\'êtes pas autorisé à modifier cet avis.',
                'errors' => [
                    'avis' => ['Vous ne pouvez modifier que vos propres avis.']
                ]
            ], 403);
        }

        // Validation des données
        $validator = Validator::make($request->all(), [
            'coment_fr' => 'required|string|min:10',
            'coment_en' => 'nullable|string|min:10',
            'note' => 'required|integer|min:1|max:5',
        ], [
            'coment_fr.required' => 'Le commentaire en français est requis.',
            'coment_fr.min' => 'Le commentaire en français doit contenir au moins 10 caractères.',
            'coment_en.min' => 'Le commentaire en anglais doit contenir au moins 10 caractères.',
            'note.required' => 'La note est requise.',
            'note.integer' => 'La note doit être un nombre entier.',
            'note.min' => 'La note doit être comprise entre 1 et 5.',
            'note.max' => 'La note doit être comprise entre 1 et 5.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Les données fournies sont invalides.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Mettre à jour l'avis
        $avis->update([
            'coment_fr' => $request->coment_fr,
            'coment_en' => $request->coment_en,
            'note' => $request->note,
        ]);

        return response()->json([
            'message' => 'Avis modifié avec succès',
            'avis' => $avis->load('locataire')
        ]);
    }

    /**
     * Supprimer un avis (soft delete)
     */
    public function destroy($id)
    {
        // Vérifier si l'utilisateur est un locataire
        $locataire = Locataire::where('user_id', Auth::id())->first();
        if (!$locataire) {
            return response()->json([
                'message' => 'Seuls les locataires peuvent supprimer des avis.',
                'errors' => [
                    'user' => ['Vous devez être un locataire pour pouvoir supprimer un avis.']
                ]
            ], 403);
        }

        // Trouver l'avis
        $avis = Avis::withTrashed()->findOrFail($id);

        // Vérifier si l'avis est déjà supprimé
        if ($avis->trashed()) {
            return response()->json([
                'message' => 'Cet avis a déjà été supprimé.',
                'errors' => [
                    'avis' => ['Vous ne pouvez pas supprimer un avis déjà supprimé.']
                ]
            ], 403);
        }

        // Vérifier si l'utilisateur est le propriétaire de l'avis
        if ($avis->locaId !== $locataire->id) {
            return response()->json([
                'message' => 'Vous n\'êtes pas autorisé à supprimer cet avis.',
                'errors' => [
                    'avis' => ['Vous ne pouvez supprimer que vos propres avis.']
                ]
            ], 403);
        }

        // Supprimer l'avis (soft delete)
        $avis->delete();

        return response()->json([
            'message' => 'Avis supprimé avec succès'
        ]);
    }

    /**
     * Changer le statut visible d'un avis (admin seulement)
     */
    public function updateVisibility(Request $request, $id)
    {
        // Vérifier si l'utilisateur est admin
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'message' => 'Accès non autorisé.',
                'errors' => [
                    'user' => ['Seuls les administrateurs peuvent modifier la visibilité des avis.']
                ]
            ], 403);
        }

        // Validation des données
        $validator = Validator::make($request->all(), [
            'visible' => 'required|boolean',
        ], [
            'visible.required' => 'Le statut de visibilité est requis.',
            'visible.boolean' => 'Le statut de visibilité doit être un booléen.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Les données fournies sont invalides.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Trouver l'avis
        $avis = Avis::withTrashed()->findOrFail($id);

        // Vérifier si l'avis est supprimé
        if ($avis->trashed()) {
            return response()->json([
                'message' => 'Cet avis a été supprimé.',
                'errors' => [
                    'avis' => ['Vous ne pouvez pas modifier la visibilité d\'un avis supprimé.']
                ]
            ], 403);
        }

        // Mettre à jour la visibilité
        $avis->update([
            'visible' => $request->visible
        ]);

        return response()->json([
            'message' => 'Visibilité de l\'avis mise à jour avec succès',
            'avis' => $avis->load('locataire')
        ]);
    }

    /**
     * Restaurer un avis supprimé (admin seulement)
     */
    public function restore($id)
    {
        // Vérifier si l'utilisateur est admin
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'message' => 'Accès non autorisé.',
                'errors' => [
                    'user' => ['Seuls les administrateurs peuvent restaurer les avis.']
                ]
            ], 403);
        }

        // Trouver l'avis supprimé
        $avis = Avis::withTrashed()->findOrFail($id);

        // Vérifier si l'avis est déjà restauré
        if (!$avis->trashed()) {
            return response()->json([
                'message' => 'Cet avis n\'a pas été supprimé.',
                'errors' => [
                    'avis' => ['Vous ne pouvez restaurer que les avis supprimés.']
                ]
            ], 403);
        }

        // Restaurer l'avis
        $avis->restore();

        return response()->json([
            'message' => 'Avis restauré avec succès',
            'avis' => $avis->load('locataire')
        ]);
    }

    /**
     * Lister les avis supprimés (admin seulement)
     */
    public function trashed()
    {
        // Vérifier si l'utilisateur est admin
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'message' => 'Accès non autorisé.',
                'errors' => [
                    'user' => ['Seuls les administrateurs peuvent voir les avis supprimés.']
                ]
            ], 403);
        }

        try {
            $avis = Avis::onlyTrashed()
                ->with(['locataire', 'logement'])
                ->orderBy('deleted_at', 'desc')
                ->paginate(10);

            if ($avis->isEmpty()) {
                return response()->json([
                    'message' => 'Aucun avis supprimé trouvé.',
                    'data' => []
                ]);
            }

            return response()->json([
                'message' => 'Liste des avis supprimés récupérée avec succès.',
                'data' => $avis
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de la récupération des avis supprimés.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 
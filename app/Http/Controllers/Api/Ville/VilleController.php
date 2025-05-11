<?php

namespace App\Http\Controllers\Api\Ville;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVilleRequest;
use App\Http\Resources\VilleResource;
use App\Models\Ville;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class VilleController extends Controller
{
    // Afficher toutes les villes actives
    public function index(): JsonResponse
    {
        $villes = Ville::active()->with('creator')->get();
        return response()->json([
            'status' => 'success',
            'data' => VilleResource::collection($villes)
        ], 200);
    }

    // Afficher toutes les villes supprimées
    public function deleted(): JsonResponse
    {
        $villes = Ville::where('del_yn', 'Y')->with('creator')->get();
        return response()->json([
            'status' => 'success',
            'data' => VilleResource::collection($villes)
        ], 200);
    }

    // Afficher une ville par ID (inclut les supprimées)
    public function show(int $id): JsonResponse
    {
        $ville = Ville::with('creator')->findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data' => new VilleResource($ville)
        ], 200);
    }

    // Créer une nouvelle ville
    public function store(StoreVilleRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['created_by'] = Auth::id();
        
        $ville = Ville::create($data);
        $ville->load('creator');
        
        return response()->json([
            'status' => 'success',
            'message' => 'Ville créée avec succès',
            'data' => new VilleResource($ville)
        ], 201);
    }

    // Mettre à jour une ville
    public function update(StoreVilleRequest $request, int $id): JsonResponse
    {
        $ville = Ville::findOrFail($id);
        $ville->update($request->validated());
        return response()->json([
            'status' => 'success',
            'message' => 'Ville mise à jour avec succès',
            'data' => new VilleResource($ville)
        ], 200);
    }

    // Supprimer une ville (soft delete)
    public function destroy(int $id): JsonResponse
    {
        $ville = Ville::findOrFail($id);
        $ville->del_yn = 'Y';
        $ville->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Ville supprimée avec succès'
        ], 200);
    }

    // Restaurer une ville supprimée
    public function restore(int $id): JsonResponse
    {
        $ville = Ville::where('del_yn', 'Y')->findOrFail($id);
        $ville->update(['del_yn' => 'N']);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Ville restaurée avec succès',
            'data' => new VilleResource($ville)
        ], 200);
    }
} 
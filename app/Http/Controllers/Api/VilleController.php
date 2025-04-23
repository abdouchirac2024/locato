<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVilleRequest;
use App\Http\Resources\VilleResource;
use App\Models\Ville;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class VilleController extends Controller
{
    // Afficher toutes les villes
    public function index(): JsonResponse
    {
        $villes = Ville::all();
        return response()->json([
            'status' => 'success',
            'data' => VilleResource::collection($villes)
        ], 200);
    }

    // Afficher une ville par ID
    public function show(int $id): JsonResponse
    {
        $ville = Ville::findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data' => new VilleResource($ville)
        ], 200);
    }

    // Créer une nouvelle ville
    public function store(StoreVilleRequest $request): JsonResponse
    {
        $ville = Ville::create($request->validated());
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

    // Supprimer une ville
    public function destroy(int $id): JsonResponse
    {
        $ville = Ville::findOrFail($id);
        $ville->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Ville supprimée avec succès'
        ], 200);
    }
}

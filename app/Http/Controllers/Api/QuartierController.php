<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuartierRequest;
use App\Http\Resources\QuartierResource;
use App\Models\Quartier;
use Illuminate\Http\JsonResponse;

class QuartierController extends Controller
{
    // Afficher tous les quartiers
    public function index(): JsonResponse
    {
        $quartiers = Quartier::with('ville')->get();

        return response()->json([
            'status' => 'success',
            'data' => QuartierResource::collection($quartiers)
        ], 200);
    }

    // Afficher un quartier par ID
    public function show(int $id): JsonResponse
    {
        $quartier = Quartier::with('ville')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => new QuartierResource($quartier)
        ], 200);
    }

    // Créer un nouveau quartier
    public function store(StoreQuartierRequest $request): JsonResponse
    {
        $quartier = Quartier::create($request->validated());
        $quartier->load('ville');

        return response()->json([
            'status' => 'success',
            'message' => 'Quartier créé avec succès',
            'data' => new QuartierResource($quartier)
        ], 201);
    }

    // Mettre à jour un quartier
    public function update(StoreQuartierRequest $request, int $id): JsonResponse
    {
        $quartier = Quartier::findOrFail($id);
        $quartier->update($request->validated());
        $quartier->load('ville');

        return response()->json([
            'status' => 'success',
            'message' => 'Quartier mis à jour avec succès',
            'data' => new QuartierResource($quartier)
        ], 200);
    }

    // Supprimer un quartier
    public function destroy(int $id): JsonResponse
    {
        $quartier = Quartier::findOrFail($id);
        $quartier->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Quartier supprimé avec succès'
        ], 200);
    }
}

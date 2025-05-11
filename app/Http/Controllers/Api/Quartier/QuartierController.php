<?php

namespace App\Http\Controllers\Api\Quartier;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuartierRequest;
use App\Http\Resources\QuartierResource;
use App\Models\Quartier;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class QuartierController extends Controller
{
    // Afficher tous les quartiers actifs
    public function index(): JsonResponse
    {
        $quartiers = Quartier::active()->with(['ville', 'creator'])->get();

        return response()->json([
            'status' => 'success',
            'data' => QuartierResource::collection($quartiers)
        ], 200);
    }

    // Afficher tous les quartiers supprimés
    public function deleted(): JsonResponse
    {
        $quartiers = Quartier::where('del_yn', 'Y')->with(['ville', 'creator'])->get();
        return response()->json([
            'status' => 'success',
            'data' => QuartierResource::collection($quartiers)
        ], 200);
    }

    // Afficher un quartier par ID (inclut les supprimés)
    public function show(int $id): JsonResponse
    {
        $quartier = Quartier::with(['ville', 'creator'])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => new QuartierResource($quartier)
        ], 200);
    }

    // Créer un nouveau quartier
    public function store(StoreQuartierRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['created_by'] = Auth::id();
        
        $quartier = Quartier::create($data);
        $quartier->load(['ville', 'creator']);

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

    // Supprimer un quartier (soft delete)
    public function destroy(int $id): JsonResponse
    {
        $quartier = Quartier::findOrFail($id);
        $quartier->del_yn = 'Y';
        $quartier->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Quartier supprimé avec succès'
        ], 200);
    }

    // Restaurer un quartier supprimé
    public function restore(int $id): JsonResponse
    {
        $quartier = Quartier::where('del_yn', 'Y')->findOrFail($id);
        $quartier->update(['del_yn' => 'N']);
        $quartier->load('ville');
        
        return response()->json([
            'status' => 'success',
            'message' => 'Quartier restauré avec succès',
            'data' => new QuartierResource($quartier)
        ], 200);
    }
} 
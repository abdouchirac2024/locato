<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\TypeLogementService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use App\Http\Requests\Admin\StoreTypeLogementRequest;
use App\Http\Requests\Admin\UpdateTypeLogementRequest;
use App\Http\Resources\Admin\TypeLogementResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;

class TypeLogementController extends Controller
{
    protected $typeLogementService;

    public function __construct(TypeLogementService $typeLogementService)
    {
        $this->typeLogementService = $typeLogementService;
        // Middleware appliqué via les routes
    }

    public function index(Request $request): JsonResponse
    {
        $this->setLocaleFromHeader($request);
        try {
            // La recherche utilise le paramètre 'libelle' mais cherche dans 'libelle_logement'
            $searchParams = $request->only(['libelle']);
            $types = $this->typeLogementService->getAllTypes($searchParams);
            return TypeLogementResource::collection($types)
                   ->response()
                   ->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error("Admin/TypeLogementController@index: " . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération des types.'], 500);
        }
    }

    public function store(StoreTypeLogementRequest $request): JsonResponse
    {
        // $request->validated() contient ['libelle_logement' => ..., 'standing' => ... (ou null)]
        try {
            $type = $this->typeLogementService->createType($request->validated());
            $this->setLocaleFromHeader($request);
            return (new TypeLogementResource($type))
                    ->response()
                    ->setStatusCode(Response::HTTP_CREATED);
        } catch (\InvalidArgumentException $e) {
             return response()->json(['message' => $e->getMessage()], Response::HTTP_CONFLICT); // 409 pour duplicata
        } catch (\Exception $e) {
            Log::error("Admin/TypeLogementController@store: " . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la création du type.'], 500);
        }
    }

    public function show(Request $request, string $id): JsonResponse
    {
         $this->setLocaleFromHeader($request);
        try {
             if (!ctype_digit($id) || (int)$id <= 0) { return response()->json(['message' => "ID invalide."], 400); }
            $type = $this->typeLogementService->getTypeById((int)$id);
            return (new TypeLogementResource($type))
                    ->response()
                    ->setStatusCode(Response::HTTP_OK);
        } catch (ModelNotFoundException $e) { return response()->json(['message' => $e->getMessage()], 404); }
          catch (\Exception $e) { Log::error("Admin/TypeLogementController@show({$id}): " . $e->getMessage()); return response()->json(['message' => 'Erreur serveur.'], 500); }
    }

    public function update(UpdateTypeLogementRequest $request, string $id): JsonResponse
    {
        try {
             if (!ctype_digit($id) || (int)$id <= 0) { return response()->json(['message' => "ID invalide."], 400); }
             // $request->validated() contient ['libelle_logement' => ...] et/ou ['standing' => ...]
            $type = $this->typeLogementService->updateType((int)$id, $request->validated());
            $this->setLocaleFromHeader($request);
            return (new TypeLogementResource($type))
                    ->response()
                    ->setStatusCode(Response::HTTP_OK);
        } catch (ModelNotFoundException $e) { return response()->json(['message' => $e->getMessage()], 404); }
          catch (\InvalidArgumentException $e) { return response()->json(['message' => $e->getMessage()], 409); } // Conflit unicité
          catch (\Exception $e) { Log::error("Admin/TypeLogementController@update({$id}): " . $e->getMessage()); return response()->json(['message' => 'Erreur serveur.'], 500); }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
             if (!ctype_digit($id) || (int)$id <= 0) { return response()->json(['message' => "ID invalide."], 400); }
            $this->typeLogementService->deleteType((int)$id);
            return response()->json(null, Response::HTTP_NO_CONTENT); // 204
        } catch (ModelNotFoundException $e) { return response()->json(['message' => $e->getMessage()], 404); }
          catch (\RuntimeException $e) { return response()->json(['message' => $e->getMessage()], 409); } // Conflit utilisé
          catch (\Exception $e) { Log::error("Admin/TypeLogementController@destroy({$id}): " . $e->getMessage()); return response()->json(['message' => 'Erreur serveur.'], 500); }
    }

    // Méthode helper pour la langue
    private function setLocaleFromHeader(Request $request): void
    {
        $localeHeader = $request->header('Accept-Language');
        $supportedLocales = config('app.available_locales', ['fr', 'en']);
        $defaultLocale = config('app.fallback_locale', 'fr');
        $locale = $defaultLocale;
        if ($localeHeader) {
            $preferredLocale = explode(',', $localeHeader)[0];
            $primaryLocale = strtolower(explode('-', $preferredLocale)[0]);
            if (in_array($primaryLocale, $supportedLocales)) { $locale = $primaryLocale; }
        }
        App::setLocale($locale);
    }
}

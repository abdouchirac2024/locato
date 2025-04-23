<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VilleController;
use App\Http\Controllers\Api\QuartierController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Routes API Resource pour Ville
Route::apiResource('villes', VilleController::class);

// Routes API Resource pour Quartier
Route::apiResource('quartiers', QuartierController::class);






// Routes protégées par Sanctum
// Route::middleware('auth:sanctum')->group(function () {
//     // Routes API Resource pour Ville
//     Route::apiResource('villes', VilleController::class);
    
//     // Routes API Resource pour Quartier
//     Route::apiResource('quartiers', QuartierController::class);
// });

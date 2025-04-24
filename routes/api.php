<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VilleController;
use App\Http\Controllers\Api\QuartierController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\LogementController; // Importez le contrôleur

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Route de base pour vérifier que l'API fonctionne
Route::get('/', function () {
    return response()->json(['message' => 'Locato API is running!']);
});

// --- Routes d'Authentification ---
Route::prefix('auth')->name('auth.')->group(function () { // Ajout de name() pour préfixe de nom
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/verify-email', [AuthController::class, 'verifyEmail'])->name('verify-email');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->middleware('throttle:3,1')->name('password.email');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/user', [AuthController::class, 'user'])->name('user');
        Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
        Route::post('/resend-verification-code', [AuthController::class, 'resendVerificationCode'])->name('resend-verification');
    });
});


// --- Routes pour Villes et Quartiers (publiques) ---
Route::apiResource('villes', VilleController::class);
Route::apiResource('quartiers', QuartierController::class);


// --- Routes pour Logements ---
// La protection est maintenant gérée par le middleware appliqué
// DANS le constructeur du LogementController pour store, update, destroy.
Route::apiResource('logements', LogementController::class);


// --- Autres Routes (Exemple) ---
// Route::middleware('auth:sanctum')->group(function () {
//     Route::apiResource('visites', VisiteController::class);
//     Route::apiResource('avis', AvisController::class);
//     // etc.
// });


// Route Fallback (si aucune autre route ne correspond)
Route::fallback(function(){
    return response()->json(['message' => 'Route non trouvée.'], 404);
})->name('fallback');

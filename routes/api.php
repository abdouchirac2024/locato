<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Ville\VilleController;
use App\Http\Controllers\Api\Quartier\QuartierController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\LogementController;
use App\Http\Controllers\Api\BailleurController;

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
Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/verify-email', [AuthController::class, 'verifyEmail'])->name('verify-email');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->middleware('throttle:3,1')->name('password.email');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');

    // Routes protégées
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/user', [AuthController::class, 'user'])->name('user');
        Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
        Route::post('/resend-verification-code', [AuthController::class, 'resendVerificationCode'])->name('resend-verification');

        // Route pour gérer le statut des utilisateurs (admin seulement)
        Route::middleware('admin')->group(function () {
            Route::put('/users/{id}/status', [AuthController::class, 'updateUserStatus'])->name('users.status');
        });
    });
});

// Routes pour la gestion des bailleurs
Route::prefix('bailleurs')->name('bailleurs.')->group(function () {
    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::get('/', [BailleurController::class, 'index'])->name('index');
        Route::put('/{id}/status', [BailleurController::class, 'updateStatus'])->name('status.update');
    });
});

// --- Routes pour Villes et Quartiers ---
Route::prefix('villes')->group(function () {
    Route::get('/', [VilleController::class, 'index']);
    Route::get('/deleted', [VilleController::class, 'deleted']);
    Route::get('/{id}', [VilleController::class, 'show']);
    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::post('/', [VilleController::class, 'store']);
        Route::put('/{id}', [VilleController::class, 'update']);
        Route::delete('/{id}', [VilleController::class, 'destroy']);
        Route::put('/{id}/restore', [VilleController::class, 'restore']);
    });
});

Route::prefix('quartiers')->group(function () {
    Route::get('/', [QuartierController::class, 'index']);
    Route::get('/deleted', [QuartierController::class, 'deleted']);
    Route::get('/{id}', [QuartierController::class, 'show']);
    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::post('/', [QuartierController::class, 'store']);
        Route::put('/{id}', [QuartierController::class, 'update']);
        Route::delete('/{id}', [QuartierController::class, 'destroy']);
        Route::put('/{id}/restore', [QuartierController::class, 'restore']);
    });
});

// --- Routes pour Logements ---
Route::apiResource('logements', LogementController::class);

// Route Fallback
Route::fallback(function(){
    return response()->json(['message' => 'Route non trouvée.'], 404);
})->name('fallback');

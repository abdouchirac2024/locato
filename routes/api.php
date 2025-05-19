<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\LogementController;
use App\Http\Controllers\Api\BailleurController;



use App\Http\Controllers\Api\VilleController;
use App\Http\Controllers\Api\QuartierController;


use App\Http\Controllers\Api\Annonce\AnnonceController;

// Contrôleurs Admin
use App\Http\Controllers\Api\Admin\TypeLogementController as AdminTypeLogementController;
use App\Http\Controllers\Api\Admin\LogementController as AdminLogementController;

// Routes pour les avis
use App\Http\Controllers\Api\AvisController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn() => response()->json(['message' => config('app.name', 'Locato') . ' API is running!']));


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


// ======================= ROUTES PUBLIQUES =======================
Route::apiResource('villes', VilleController::class)->only(['index', 'show']);
Route::apiResource('quartiers', QuartierController::class)->only(['index', 'show']);

// Logements: index() et show() sont publics (protection gérée dans le contrôleur pour index/show)
Route::get('/logements', [LogementController::class, 'index'])->name('logements.index.public');
Route::get('/logements/{logement}', [LogementController::class, 'show'])->name('logements.show.public')
       ->where('logement', '[0-9]+');

// ======================= UTILISATEURS CONNECTÉS (BAILLEURS) =======================
Route::middleware('auth:sanctum')->group(function () {
    // CRUD Logement pour le bailleur authentifié
    Route::post('/logements', [LogementController::class, 'store'])->name('logements.store');
    Route::put('/logements/{logement}', [LogementController::class, 'update'])->name('logements.update')->where('logement', '[0-9]+');
    Route::patch('/logements/{logement}', [LogementController::class, 'update'])->where('logement', '[0-9]+');
    Route::delete('/logements/{logement}', [LogementController::class, 'destroy'])->name('logements.destroy')->where('logement', '[0-9]+');
});

// ======================= ADMINISTRATION =======================
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth:sanctum', 'admin'])
    ->group(function () {

        Route::apiResource('types-logement', AdminTypeLogementController::class)->parameters(['types-logement' => 'typeLogement']);

        // --- GESTION DES LOGEMENTS PAR L'ADMIN ---
        Route::get('/logements/pending', [AdminLogementController::class, 'listPending'])->name('logements.pending');
        Route::get('/logements/all', [AdminLogementController::class, 'indexAll'])->name('logements.indexAll');
        Route::post('/logements', [AdminLogementController::class, 'store'])->name('logements.store'); // Le nom sera 'admin.logements.store'

        // Routes pour un logement spécifique (admin)
        Route::prefix('logements/{logement}')->name('logements.')->group(function () {
            Route::get('/', [AdminLogementController::class, 'show'])->name('show')->where('logement', '[0-9]+');
            Route::put('/approve', [AdminLogementController::class, 'approve'])->name('approve')->where('logement', '[0-9]+');
            Route::put('/reject', [AdminLogementController::class, 'reject'])->name('reject')->where('logement', '[0-9]+');
            Route::put('/restore', [AdminLogementController::class, 'restore'])->name('restore')->where('logement', '[0-9]+');
            Route::delete('/force-delete', [AdminLogementController::class, 'forceDelete'])->name('forceDelete')->where('logement', '[0-9]+');
        });
    });

// ======================= FALLBACK =======================
Route::fallback(function () {
    return response()->json(['message' => 'Endpoint non trouvé.'], 404);
})->name('fallback');

// ===== FIN VERSION develop =====
// Route des annonces par ABEL 02/05/2025p
Route::apiResource('annonces', AnnonceController::class);
// Route::post('/create-annonce', [AnnonceController::class, 'storeByBailleur']);
Route::put('/confirm-annonce/{id}', [AnnonceController::class, 'confirmAnnonce']);

Route::middleware('auth:sanctum')->group(function() {
    Route::post('/create-annonce', [AnnonceController::class, 'storeByBailleur']);
});

// Routes pour les avis
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/avis', [AvisController::class, 'index']);
    Route::post('/avis', [AvisController::class, 'store']);

    // Routes admin pour les avis
    Route::middleware('admin')->group(function () {
        Route::get('/avis/trashed', [AvisController::class, 'trashed']);
        Route::put('/avis/{id}/visibility', [AvisController::class, 'updateVisibility']);
        Route::post('/avis/{id}/restore', [AvisController::class, 'restore']);
    });

    // Routes pour les avis spécifiques (doivent être après les routes spécifiques)
    Route::get('/avis/{id}', [AvisController::class, 'show']);
    Route::put('/avis/{id}', [AvisController::class, 'update']);
    Route::delete('/avis/{id}', [AvisController::class, 'destroy']);
});
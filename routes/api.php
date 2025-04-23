<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VilleController;
use App\Http\Controllers\Api\QuartierController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PasswordResetController;
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






Route::prefix('auth')->group(function () {
    // Routes publiques
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/login', [AuthController::class, 'login']);
    
    // Mot de passe oublié/réinitialisation
    // Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])
    ->middleware('throttle:3,1'); // 3 tentatives max par minute
    Route::post('/reset-password', [PasswordResetController::class, 'reset']);

    // Routes protégées
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/resend-verification-code', [AuthController::class, 'resendVerificationCode']);
    });
});



//Routes protégées par Sanctum
Route::middleware('auth:sanctum')->group(function () {
    // Routes API Resource pour Ville
    Route::apiResource('villes', VilleController::class);
    
    // Routes API Resource pour Quartier
    Route::apiResource('quartiers', QuartierController::class);
});

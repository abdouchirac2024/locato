<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// --- CONTRÔLEURS PRINCIPAUX (Adaptez les chemins si nécessaire) ---
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\PasswordResetController; // S'il est à la racine de Api/
use App\Http\Controllers\Api\Logement\LogementController; // Mis dans Api/Logement/
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\BailleurController;      // Semble être à la racine de Api/
use App\Http\Controllers\Api\Ville\VilleController;         // Mis dans Api/Ville/
use App\Http\Controllers\Api\Quartier\QuartierController;   // Mis dans Api/Quartier/
use App\Http\Controllers\Api\Annonce\AnnonceController; // Dans Api/Annonce/
use App\Http\Controllers\Api\AvisController;           // Semble être à la racine de Api/
use App\Http\Controllers\Api\Visite\VisiteController;    // Nouveau, dans Api/Visite/

// --- CONTRÔLEURS ADMIN ---
use App\Http\Controllers\Api\Admin\TypeLogementController as AdminTypeLogementController;
use App\Http\Controllers\Api\Admin\LogementController as AdminLogementController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController; // Nouveau pour la gestion des rôles/statuts par admin

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Ensemble des routes pour l'API RESTful de l'application Locato.
| Les protections sont appliquées via des groupes de middleware.
|
*/

// --- ROUTE DE TEST DE L'API ---
Route::get('/', fn() => response()->json(['message' => config('app.name', 'Locato') . ' API is running!']));

// ======================= AUTHENTIFICATION =======================
Route::prefix('auth')->name('auth.')->group(function () {
    // Routes Publiques d'Authentification
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/verify-email', [AuthController::class, 'verifyEmail'])->name('verify-email');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->middleware('throttle:3,1')->name('password.email');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');

    // Routes Protégées (nécessitent d'être authentifié via Sanctum)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/user', [AuthController::class, 'user'])->name('user'); // Récupérer l'utilisateur connecté
        Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update'); // Mettre à jour son propre profil
        Route::post('/resend-verification-code', [AuthController::class, 'resendVerificationCode'])->name('resend-verification');
    });
});

// ======================= ROUTES PUBLIQUES (Pas d'authentification requise) =======================
// Villes : Lister et afficher une ville
Route::get('/villes', [VilleController::class, 'index'])->name('villes.index.public');
Route::get('/villes/{ville}', [VilleController::class, 'show'])->name('villes.show.public')->where('ville', '[0-9]+');

// Quartiers : Lister et afficher un quartier
Route::get('/quartiers', [QuartierController::class, 'index'])->name('quartiers.index.public');
Route::get('/quartiers/{quartier}', [QuartierController::class, 'show'])->name('quartiers.show.public')->where('quartier', '[0-9]+');

// Logements : Lister les logements approuvés et afficher un logement spécifique approuvé
Route::get('/logements', [LogementController::class, 'index'])->name('logements.index.public');
Route::get('/logements/{logement}', [LogementController::class, 'show'])->name('logements.show.public')->where('logement', '[0-9]+');

// Annonces : Lister les annonces (suppose que l'index est public) et afficher une annonce
Route::get('/annonces', [AnnonceController::class, 'index'])->name('annonces.index.public');
Route::get('/annonces/{annonce}', [AnnonceController::class, 'show'])->name('annonces.show.public')->where('annonce', '[0-9]+');

// Avis : Lister les avis visibles
Route::get('/avis', [AvisController::class, 'index'])->name('avis.index.public'); // Peut nécessiter des filtres (ex: par logement)
Route::get('/avis/{avis}', [AvisController::class, 'show'])->name('avis.show.public')->where('avis', '[0-9]+'); // Montre seulement si visible (logique dans contrôleur)


// ======================= ROUTES PROTÉGÉES (Nécessitent Authentification Sanctum) =======================
Route::middleware('auth:sanctum')->group(function () {

    // --- Gestion des Logements par le Bailleur ---
    Route::prefix('logements')->name('logements.')->group(function () {
        Route::post('/', [LogementController::class, 'store'])->name('store.bailleur');
        Route::put('/{logement}', [LogementController::class, 'update'])->name('update.bailleur')->where('logement', '[0-9]+');
        Route::patch('/{logement}', [LogementController::class, 'update'])->where('logement', '[0-9]+'); // Alias pour update
        Route::delete('/{logement}', [LogementController::class, 'destroy'])->name('destroy.bailleur')->where('logement', '[0-9]+'); // Soft delete
    });

    // --- Gestion des Annonces par le Bailleur ---
    Route::post('/create-annonce', [AnnonceController::class, 'storeByBailleur'])->name('annonces.storeByBailleur');
    // Les autres actions sur les annonces (update/delete par bailleur) peuvent être ajoutées ici ou dans apiResource AnnonceController

    // --- Gestion des Avis par le Locataire ---
    Route::post('/avis', [AvisController::class, 'store'])->name('avis.store.locataire');
    Route::put('/avis/{avis}', [AvisController::class, 'update'])->name('avis.update.locataire')->where('avis', '[0-9]+'); // Si le locataire peut modifier son avis
    Route::delete('/avis/{avis}', [AvisController::class, 'destroy'])->name('avis.destroy.locataire')->where('avis', '[0-9]+'); // Si le locataire peut supprimer son avis

    // --- Gestion des Visites (Locataire et Bailleur) ---
    Route::prefix('visites')->name('visites.')->group(function () {
        Route::post('/', [VisiteController::class, 'store'])->name('store'); // Locataire demande une visite
        Route::get('/my-requests', [VisiteController::class, 'listMyVisites'])->name('myRequests'); // Locataire voit ses demandes
        Route::get('/logement/{logementId}/requests', [VisiteController::class, 'listRequestsForLogement'])
             ->name('requestsForLogement')->where('logementId', '[0-9]+'); // Bailleur voit demandes pour SON logement
        Route::put('/{visite}/status', [VisiteController::class, 'updateStatus'])->name('updateStatus')->where('visite', '[0-9]+'); // Bailleur change statut
        Route::put('/{visite}/cancel', [VisiteController::class, 'cancelByLocataire'])->name('cancelByLocataire')->where('visite', '[0-9]+'); // Locataire annule
    });

    // Invoices
    Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');

});


// ======================= ADMINISTRATION (Nécessite Authentification Sanctum ET Middleware 'admin') =======================
Route::prefix('admin')
    ->name('admin.') // Préfixe de nom de route (ex: admin.users.updateRole)
    ->middleware(['auth:sanctum', 'admin']) // Applique les deux middlewares
    ->group(function () {

        // CRUD pour les Types de Logement
        Route::apiResource('types-logement', AdminTypeLogementController::class)
             ->parameters(['types-logement' => 'typeLogement']); // Renomme le paramètre de route

        // Gestion des Logements par l'Admin
        Route::get('/logements/pending', [AdminLogementController::class, 'listPending'])->name('logements.pending');
        Route::get('/logements/all', [AdminLogementController::class, 'indexAll'])->name('logements.indexAll');
        Route::post('/logements', [AdminLogementController::class, 'store'])->name('logements.store'); // Création par Admin

        Route::prefix('logements/{logement}')->name('logements.')->where(['logement' => '[0-9]+'])->group(function () {
            Route::get('/', [AdminLogementController::class, 'show'])->name('show');
            Route::put('/approve', [AdminLogementController::class, 'approve'])->name('approve');
            Route::put('/reject', [AdminLogementController::class, 'reject'])->name('reject');
            Route::put('/restore', [AdminLogementController::class, 'restore'])->name('restore');
            Route::delete('/force-delete', [AdminLogementController::class, 'forceDelete'])->name('forceDelete');
        });

        // Gestion des Utilisateurs par l'Admin
        Route::prefix('users/{userId}')->name('users.')->where(['userId' => '[0-9]+'])->group(function() {
            Route::put('/role', [AdminUserController::class, 'updateUserRole'])->name('updateRole');
            Route::put('/status', [AdminUserController::class, 'updateUserStatus'])->name('updateStatus');
        });
         // Optionnel : Route pour lister tous les utilisateurs (admin)
         // Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');


        // Gestion des Villes par l'Admin (si vous ne voulez pas utiliser les routes publiques pour modif/suppr)
        // Route::apiResource('villes', AdminVilleController::class)->except(['index', 'show']);

        // Gestion des Quartiers par l'Admin
        // Route::apiResource('quartiers', AdminQuartierController::class)->except(['index', 'show']);

        // Gestion des Avis par l'Admin
        Route::prefix('avis')->name('avis.')->group(function () {
            Route::get('/trashed', [AvisController::class, 'trashed'])->name('trashed');
            Route::put('/{avis}/visibility', [AvisController::class, 'updateVisibility'])->name('visibility')->where('avis', '[0-9]+');
            Route::post('/{avis}/restore', [AvisController::class, 'restore'])->name('restore')->where('avis', '[0-9]+');
            // Admin peut aussi vouloir supprimer un avis (hard delete ou via une méthode dédiée)
            // Route::delete('/{avis}/force-delete', [AvisController::class, 'forceDeleteAdmin'])->name('forceDelete.admin')->where('avis', '[0-9]+');
        });

        // Gestion des Annonces par l'Admin (Ex: confirmer/valider une annonce)
        // La route confirmAnnonce est déjà définie plus haut, mais il faudrait la protéger avec le middleware admin si c'est une action admin.
        // Si confirmAnnonce est pour l'admin, déplacez-la ici :
        // Route::put('/annonces/{annonce}/confirm', [AnnonceController::class, 'confirmAnnonce'])->name('annonces.confirmAdmin')->where('annonce', '[0-9]+');

    });


// ======================= FALLBACK (Toujours à la fin) =======================
Route::fallback(function () {
    return response()->json(['message' => 'Endpoint non trouvé.'], 404);
})->name('fallback');
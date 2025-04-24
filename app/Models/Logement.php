<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Stichoza\GoogleTranslate\GoogleTranslate; // Importez la classe
use Illuminate\Support\Facades\Log; // Pour logger les erreurs de traduction

class Logement extends Model
{
    use HasFactory;

    protected $fillable = [
        'libelle',
        'nbrMois',
        'prix',
        'nbrpieces',
        'descrip_fr', // Champ source français
        'descrip_en', // Champ traduit anglais
        'contPrep',
        'forage',
        'parking',
        'gardien',
        'dispo_fr',   // Champ source français
        'dispo_en',   // Champ traduit anglais
        'typLogId',
        'quartierId',
        'bailId',
    ];

    /**
     * Les attributs qui devraient être castés.
     * Utile pour les booléens et les dates.
     */
    protected $casts = [
        'contPrep' => 'boolean',
        'forage' => 'boolean',
        'parking' => 'boolean',
        'gardien' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    // --- Relations Eloquent ---

    public function typeLogement(): BelongsTo
    {
        // Assurez-vous que le modèle TypeLogement existe
        return $this->belongsTo(TypeLogement::class, 'typLogId');
    }

    public function quartier(): BelongsTo
    {
         // Assurez-vous que le modèle Quartier existe
        return $this->belongsTo(Quartier::class, 'quartierId');
    }

    public function bailleur(): BelongsTo
    {
        // Assurez-vous que le modèle Bailleur existe
        return $this->belongsTo(Bailleur::class, 'bailId');
    }

    public function annonces(): HasMany
    {
         // Assurez-vous que le modèle Annonce existe
        return $this->hasMany(Annonce::class, 'logId');
    }

    public function imagesLogement(): HasMany
    {
        // Assurez-vous que le modèle ImageLogement existe
        return $this->hasMany(ImageLogement::class, 'logId');
    }

    public function avis(): HasMany
    {
         // Assurez-vous que le modèle Avis existe
        return $this->hasMany(Avis::class, 'logId');
    }

    public function visites(): HasMany
    {
        // Assurez-vous que le modèle Visite existe
         return $this->hasMany(Visite::class, 'logId');
    }

    // --- Logique de Traduction Automatique ---
    protected static function boot()
    {
        parent::boot();

        // Traduction lors de la création
        static::creating(function ($logement) {
            self::translateAttributes($logement);
        });

        // Traduction lors de la mise à jour si les champs français changent
        static::updating(function ($logement) {
            // Vérifie si les champs sources ont été modifiés
            if ($logement->isDirty('descrip_fr') || $logement->isDirty('dispo_fr')) {
                self::translateAttributes($logement);
            }
        });
    }

    /**
     * Fonction helper protégée pour traduire les attributs.
     */
    protected static function translateAttributes(Logement $logement)
    {
        try {
            // Initialise le traducteur vers l'anglais depuis le français
            $translator = new GoogleTranslate('en', 'fr');

            // Traduire la description si elle existe
            if (!empty($logement->descrip_fr)) {
                $logement->descrip_en = $translator->translate($logement->descrip_fr);
            } else {
                $logement->descrip_en = null; // Met à null si la source est vide
            }

            // Traduire la disponibilité (mapping des valeurs enum)
            if (!empty($logement->dispo_fr)) {
                $logement->dispo_en = match (strtoupper($logement->dispo_fr)) {
                    'LIBRE' => 'FREE',
                    'OCCUPE' => 'RENTED',
                    default => null, // Cas par défaut si la valeur source n'est pas reconnue
                };
            } else {
                 $logement->dispo_en = null; // Met à null si la source est vide
            }

        } catch (\Throwable $e) { // Attrape toutes les erreurs/exceptions possibles
            // Log l'erreur pour le débogage sans bloquer l'opération
            Log::error("Translation failed for Logement ID {$logement->id}: " . $e->getMessage());

            // Stratégie de fallback : copier les valeurs françaises si la traduction échoue
            // pour éviter d'avoir des champs EN vides si FR n'est pas vide.
            if (empty($logement->descrip_en) && !empty($logement->descrip_fr)) {
                 $logement->descrip_en = $logement->descrip_fr; // Fallback simple
            }
             if (empty($logement->dispo_en) && !empty($logement->dispo_fr)) {
                 $logement->dispo_en = match (strtoupper($logement->dispo_fr)) {
                    'LIBRE' => 'FREE',
                    'OCCUPE' => 'RENTED',
                    default => null,
                }; // Fallback simple
            }
        }
    }

    // --- Accesseurs pour la langue courante ---

    /**
     * Accesseur : Récupère la description dans la langue de l'application.
     * S'appelle via $logement->description
     */
    public function getDescriptionAttribute(): ?string // Ajout du type de retour nullable
    {
        $locale = app()->getLocale(); // Obtient la locale actuelle ('fr', 'en')
        $descriptionField = "descrip_{$locale}"; // Construit le nom du champ (descrip_fr, descrip_en)

        // Retourne la valeur du champ localisé si elle existe et n'est pas vide.
        // Sinon, essaie la version anglaise comme fallback.
        // Sinon, essaie la version française comme dernier fallback.
        return $this->{$descriptionField} ?: $this->descrip_en ?: $this->descrip_fr;
    }

    /**
     * Accesseur : Récupère la disponibilité dans la langue de l'application.
     * S'appelle via $logement->dispo
     */
    public function getDispoAttribute(): ?string // Ajout du type de retour nullable
    {
        $locale = app()->getLocale();
        $dispoField = "dispo_{$locale}";

        // Même logique de fallback que pour la description
        return $this->{$dispoField} ?: $this->dispo_en ?: $this->dispo_fr;
    }
}

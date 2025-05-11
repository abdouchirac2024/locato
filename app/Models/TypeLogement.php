<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stichoza\GoogleTranslate\GoogleTranslate;
use Illuminate\Support\Facades\Log;

class TypeLogement extends Model
{
    use HasFactory;

    protected $table = 'type_logements'; // Explicite le nom de table

    protected $fillable = [
        'libelle_logement',    // Source FR
        'libelle_logement_en', // Traduit EN
        'standing',            // Source FR (nullable)
        'standing_en',         // Traduit EN (nullable)
    ];

    protected $casts = [
         'created_at' => 'datetime',
         'updated_at' => 'datetime',
     ];

    public function logements(): HasMany
    {
        return $this->hasMany(Logement::class, 'typLogId');
    }

    // --- Logique de Traduction Automatique ---
    protected static function boot()
    {
        parent::boot();

        // Utilisation de 'saving' pour couvrir la création ET la mise à jour
        static::saving(function ($model) {
            // Traduire seulement si un champ source FR a changé ou est initialisé
            if ($model->isDirty('libelle_logement') || $model->isDirty('standing') || !$model->exists) {
                self::translateAttributes($model);
            }
        });
    }

    /**
     * Fonction helper pour traduire les attributs 'libelle_logement' et 'standing'.
     */
    protected static function translateAttributes(self $model)
    {
        try {
            // Traduire libelle_logement si FR existe
            if (!empty($model->libelle_logement)) {
                // Seulement si EN est vide ou si FR a changé
                if (empty($model->libelle_logement_en) || $model->isDirty('libelle_logement')) {
                    $translatorLibelle = new GoogleTranslate('en', 'fr');
                    $model->libelle_logement_en = $translatorLibelle->translate($model->libelle_logement);
                }
            } else {
                $model->libelle_logement_en = null; // Assurer la cohérence si FR est vidé
            }

            // Traduire standing si FR existe
            if (!empty($model->standing)) {
                // Seulement si EN est vide ou si FR a changé
                if (empty($model->standing_en) || $model->isDirty('standing')) {
                    $translatorStanding = new GoogleTranslate('en', 'fr');
                    $model->standing_en = $translatorStanding->translate($model->standing);
                }
            } else {
                $model->standing_en = null; // Assurer la cohérence si FR est vidé ou null
            }

        } catch (\Throwable $e) {
            Log::error("Translation failed for TypeLogement ID {$model->id}: " . $e->getMessage());
            // Fallback simple (ne pas écraser une traduction existante lors d'un échec de mise à jour)
            if (empty($model->libelle_logement_en) && !empty($model->libelle_logement)) {
                 $model->libelle_logement_en = $model->libelle_logement;
            }
            if (empty($model->standing_en) && !empty($model->standing)) {
                 $model->standing_en = $model->standing;
            }
        }
    }

    // --- Accesseurs pour la langue courante ---

    /**
     * Accesseur : Récupère le libellé dans la langue de l'application.
     * Appel via $typeLogement->libelle
     */
    public function getLibelleAttribute(): ?string
    {
        $locale = app()->getLocale();
        $field = "libelle_logement_{$locale}";
        // Priorité: Champ localisé -> Champ EN -> Champ FR
        return $this->{$field} ?: $this->libelle_logement_en ?: $this->libelle_logement;
    }

    /**
     * Accesseur : Récupère le standing dans la langue de l'application.
     * Appel via $typeLogement->standing_display
     */
    public function getStandingDisplayAttribute(): ?string // Nom différent requis
    {
        $locale = app()->getLocale();
        $field = "standing_{$locale}";
        // Priorité: Champ localisé -> Champ EN -> Champ FR
        return $this->{$field} ?: $this->standing_en ?: $this->standing; // Accède à la colonne FR brute pour le dernier fallback
    }
}

<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Stichoza\GoogleTranslate\GoogleTranslate;
use Illuminate\Support\Facades\Log;

class Logement extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * Constantes pour les statuts de validation.
     */
    public const STATUS_EN_ATTENTE = 'en_attente';
    public const STATUS_APPROUVE = 'approuve';
    public const STATUS_REJETE = 'rejete';

    /**
     * Les attributs qui sont assignables en masse.
     * Assurez-vous que cela correspond exactement aux colonnes de votre table.
     * @var array<int, string>
     */
    protected $fillable = [
        'libelle',
        // 'libelle_en', // Décommentez si cette colonne existe dans votre migration
        'reference',
        'latitude',
        'longitude',
        'nbrpieces',
        'superficie',
        'nbr_salles_bain',
        'nbr_chambres',
        'climatisation',
        'meuble',
        'adresse',
        'prix',
        'nbrMois',
        'descrip_fr',
        'descrip_en',
        'contPrep',
        'forage',
        'parking',
        'gardien',
        'dispo_fr',
        'dispo_en',
        'typLogId',
        'quartierId',
        'bailId',
        'validation_status',
        'admin_notes',
    ];

    /**
     * Les attributs qui doivent être castés en types natifs.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'contPrep' => 'boolean',
        'forage' => 'boolean',
        'parking' => 'boolean',
        'gardien' => 'boolean',
        'climatisation' => 'boolean',
        'meuble' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime', // Cast pour soft delete
        'validation_status' => 'string',
        'prix' => 'float',
        'nbrMois' => 'integer',
        'nbrpieces' => 'integer',
        'superficie' => 'integer',
        'nbr_salles_bain' => 'integer',
        'nbr_chambres' => 'integer',
        'typLogId' => 'integer',
        'quartierId' => 'integer',
        'bailId' => 'integer',
    ];

    // --------------------------------------------------------------------------
    // RELATIONS ELOQUENT
    // --------------------------------------------------------------------------

    /**
     * Obtient le type de logement associé.
     */
    public function typeLogement(): BelongsTo
    {
        return $this->belongsTo(TypeLogement::class, 'typLogId');
    }

    /**
     * Obtient le quartier associé.
     */
    public function quartier(): BelongsTo
    {
        return $this->belongsTo(Quartier::class, 'quartierId');
    }

    /**
     * Obtient le bailleur (propriétaire) associé.
     */
    public function bailleur(): BelongsTo
    {
        return $this->belongsTo(Bailleur::class, 'bailId');
    }

    /**
     * Obtient les images associées au logement.
     */
    public function imagesLogement(): HasMany
    {
        return $this->hasMany(ImageLogement::class, 'logId');
    }

    /**
     * Obtient les avis associés au logement.
     */
    public function avis(): HasMany
    {
        return $this->hasMany(Avis::class, 'logId');
    }

    /**
     * Obtient les annonces associées au logement.
     */
    public function annonces(): HasMany
    {
        return $this->hasMany(Annonce::class, 'logId');
    }

    /**
     * Obtient les visites programmées pour ce logement.
     */
    public function visites(): HasMany
    {
        return $this->hasMany(Visite::class, 'logId');
    }

    /**
     * Obtient l'historique des locations pour ce logement.
     */
    public function locations(): HasMany
    {
        return $this->hasMany(Location::class, 'logId');
    }

    // --------------------------------------------------------------------------
    // LOGIQUE DE TRADUCTION AUTOMATIQUE (BOOT)
    // --------------------------------------------------------------------------

    protected static function boot()
    {
        parent::boot();

        /**
         * Exécuté avant la sauvegarde (création ou mise à jour).
         * Gère la traduction des champs si nécessaire.
         */
        static::saving(function ($logement) {
            // Traduire descrip et dispo si le champ FR a changé ou est nouveau
            if ($logement->isDirty('descrip_fr') || $logement->isDirty('dispo_fr') || !$logement->exists) {
                self::translateDescriptionAndDispo($logement);
            }

            // Traduire libelle si la colonne _en existe et si FR a changé ou est nouveau
            if (property_exists($logement, 'libelle_en') && ($logement->isDirty('libelle') || !$logement->exists)) {
                self::translateLibelle($logement);
            }
        });
    }

    /**
     * Helper pour traduire la description et la disponibilité.
     */
    protected static function translateDescriptionAndDispo(self $logement): void
    {
        try {
            $translator = new GoogleTranslate('en', 'fr'); // Cible EN, Source FR

            // Traduire Description
            if (!empty($logement->descrip_fr)) {
                if (empty($logement->descrip_en) || $logement->isDirty('descrip_fr')) {
                    $logement->descrip_en = $translator->translate($logement->descrip_fr);
                }
            } else {
                $logement->descrip_en = null;
            }

            // Traduire Disponibilité
            if (!empty($logement->dispo_fr)) {
                if (empty($logement->dispo_en) || $logement->isDirty('dispo_fr')) {
                    $logement->dispo_en = match (strtoupper($logement->dispo_fr)) {
                        'LIBRE' => 'FREE',
                        'OCCUPE' => 'RENTED',
                        default => $logement->dispo_fr, // Garde la valeur FR si non reconnue
                    };
                }
            } else {
                $logement->dispo_en = null;
            }
        } catch (\Throwable $e) {
            Log::error("Translation failed (Desc/Dispo) for Logement ID {$logement->id}: " . $e->getMessage());
            // Fallback simple (ne pas écraser si échec sur update)
            if (empty($logement->descrip_en) && !empty($logement->descrip_fr)) {
                $logement->descrip_en = $logement->descrip_fr;
            }
            if (empty($logement->dispo_en) && !empty($logement->dispo_fr)) {
                $logement->dispo_en = match (strtoupper($logement->dispo_fr)) {
                    'LIBRE' => 'FREE',
                    'OCCUPE' => 'RENTED',
                    default => null,
                };
            }
        }
    }

    /**
     * Helper pour traduire le libellé (si libelle_en existe).
     */
    protected static function translateLibelle(self $logement): void
    {
        // Vérifie à nouveau au cas où la propriété n'existe pas malgré l'appel initial
        if (!property_exists($logement, 'libelle_en')) return;

        try {
            if (!empty($logement->libelle)) {
                if (empty($logement->libelle_en) || $logement->isDirty('libelle')) {
                    $translator = new GoogleTranslate('en', 'fr');
                    $logement->libelle_en = $translator->translate($logement->libelle);
                }
            } else {
                $logement->libelle_en = null;
            }
        } catch (\Throwable $e) {
            Log::error("Translation failed (Libelle) for Logement ID {$logement->id}: " . $e->getMessage());
            if (empty($logement->libelle_en) && !empty($logement->libelle)) {
                $logement->libelle_en = $logement->libelle;
            } // Fallback
        }
    }

    // --------------------------------------------------------------------------
    // ACCESSEURS POUR LA LANGUE
    // --------------------------------------------------------------------------

    /**
     * Récupère la description dans la langue courante.
     * Appel via $logement->description
     */
    public function getDescriptionAttribute(): ?string
    {
        $locale = app()->getLocale();
        $field = "descrip_{$locale}";
        // Priorité: Champ localisé -> Champ EN -> Champ FR
        return $this->{$field} ?: $this->descrip_en ?: $this->descrip_fr;
    }

    /**
     * Récupère la disponibilité dans la langue courante.
     * Appel via $logement->dispo
     */
    public function getDispoAttribute(): ?string
    {
        $locale = app()->getLocale();
        $field = "dispo_{$locale}";
        // Priorité: Champ localisé -> Champ EN -> Champ FR
        return $this->{$field} ?: $this->dispo_en ?: $this->dispo_fr;
    }

    /**
     * Récupère le libellé dans la langue courante.
     * Appel via $logement->libelle_display (pour éviter conflit avec colonne 'libelle')
     * Si la colonne libelle_en n'existe pas, retourne simplement la colonne libelle.
     */
    public function getLibelleDisplayAttribute(): ?string
    {
        // Utilise la valeur brute de la colonne 'libelle' comme fallback final
        $fallbackValue = $this->attributes['libelle'] ?? null;

        if (!property_exists($this, 'libelle_en')) {
            return $fallbackValue;
        }

        $locale = app()->getLocale();
        $field = "libelle_{$locale}"; // libelle_fr ou libelle_en

        // Attention: la colonne source s'appelle 'libelle', pas 'libelle_fr'
        $valueFr = $this->attributes['libelle'] ?? null;
        $valueEn = $this->libelle_en ?? null;

        if ($locale === 'en' && $valueEn) {
            return $valueEn;
        }
        if ($locale === 'fr' && $valueFr) {
            return $valueFr;
        }
        // Fallbacks
        return $valueEn ?: $valueFr; // Priorité à l'anglais si localisé non trouvé
    }

    // --------------------------------------------------------------------------
    // SCOPES ELOQUENT
    // --------------------------------------------------------------------------

    /**
     * Scope pour ne récupérer que les logements approuvés.
     */
    public function scopeApprouve($query)
    {
        return $query->where('validation_status', self::STATUS_APPROUVE);
    }

    /**
     * Scope pour ne récupérer que les logements en attente.
     */
    public function scopeEnAttente($query)
    {
        return $query->where('validation_status', self::STATUS_EN_ATTENTE);
    }

    /**
     * Scope pour ne récupérer que les logements rejetés.
     */
    public function scopeRejete($query)
    {
        return $query->where('validation_status', self::STATUS_REJETE);
    }
}

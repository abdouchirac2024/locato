<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stichoza\GoogleTranslate\GoogleTranslate;
use Illuminate\Support\Facades\Log;

class Visite extends Model
{
    use HasFactory;

    public const STATUT_PROGRAMMEE = 'PROGRAMMEE';
    public const STATUT_ANNULEE_LOCATAIRE = 'ANNULEE_LOCATAIRE';
    public const STATUT_ANNULEE_BAILLEUR = 'ANNULEE_BAILLEUR';
    public const STATUT_REPORTEE_BAILLEUR = 'REPORTEE_BAILLEUR';
    public const STATUT_EFFECTUEE = 'EFFECTUEE';
    // Statut si le bailleur propose une nouvelle date/heure
    public const STATUT_CONTRE_PROPOSITION = 'CONTRE_PROPOSITION';


    protected $fillable = [
        'logId',            // Logement concerné
        'locaId',           // Locataire qui demande
        // 'payId',         // Si le paiement est un prérequis, sinon peut être nullable ou enlevé pour une V1
        'dateVisite',       // Date souhaitée par le locataire
        'heureVisite',      // Heure souhaitée par le locataire
        'statut_fr',        // Statut en français
        'statut_en',        // Statut traduit
        'confirmation_bailleur', // Booléen : le bailleur a-t-il confirmé/répondu ?
        'commentaire_locataire', // Commentaire du locataire lors de la demande
        'commentaire_bailleur', // Commentaire du bailleur (ex: motif report/annulation)
        'motifRejet_fr',    // Si le bailleur rejette directement (moins utilisé si on a commentaire_bailleur)
        'motifRejet_en',
        'date_proposee_bailleur', // Si le bailleur reporte et propose une nouvelle date
        'heure_proposee_bailleur',// Si le bailleur reporte et propose une nouvelle heure
    ];

    protected $casts = [
        'dateVisite' => 'date:Y-m-d',
        'heureVisite' => 'datetime:H:i', // Stocke juste l'heure, mais le cast datetime est pratique
        'confirmation_bailleur' => 'boolean',
        'date_proposee_bailleur' => 'date:Y-m-d',
        'heure_proposee_bailleur' => 'datetime:H:i',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function logement(): BelongsTo
    {
        return $this->belongsTo(Logement::class, 'logId');
    }

    public function locataire(): BelongsTo
    {
        return $this->belongsTo(Locataire::class, 'locaId');
    }

    // Optionnel : Si une visite est liée à un paiement
    // public function payement(): BelongsTo
    // {
    //     return $this->belongsTo(Payement::class, 'payId');
    // }

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($visite) {
            if ($visite->isDirty('statut_fr') || (!$visite->exists && !empty($visite->statut_fr))) {
                self::translateAndSet($visite, 'statut_fr', 'statut_en');
            }
            if ($visite->isDirty('motifRejet_fr') || (!$visite->exists && !empty($visite->motifRejet_fr))) {
                self::translateAndSet($visite, 'motifRejet_fr', 'motifRejet_en');
            }
        });
    }

    protected static function translateAndSet(self $model, string $sourceField, string $targetField): void
    {
        if (!empty($model->{$sourceField})) {
            try {
                $translator = new GoogleTranslate('en', 'fr');
                $model->{$targetField} = $translator->translate($model->{$sourceField});
            } catch (\Throwable $e) {
                Log::error("Translation failed for Visite {$sourceField} ID {$model->id}: " . $e->getMessage());
                $model->{$targetField} = $model->{$sourceField}; // Fallback
            }
        } else {
            $model->{$targetField} = null;
        }
    }

    public function getStatutAttribute(): ?string
    {
        $locale = app()->getLocale();
        $field = "statut_{$locale}";
        return $this->{$field} ?: $this->statut_en ?: $this->statut_fr;
    }

    public function getMotifRejetAttribute(): ?string
    {
        $locale = app()->getLocale();
        $field = "motifRejet_{$locale}";
        return $this->{$field} ?: $this->motifRejet_en ?: $this->motifRejet_fr;
    }
}
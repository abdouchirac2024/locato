<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stichoza\GoogleTranslate\GoogleTranslate;
class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'montant',
        'dateDeb',
        'dateFin',
        'caution',
        'statut_fr',
        'statut_en',
        'locaId',
        'bailId',
        'logId',
    ];

    /**
     * Boot method to translate statut fields
     */
    protected static function boot()
    {
        parent::boot();

        // On creating a new location, we might want to handle statut translations
        static::creating(function ($location) {
            if ($location->statut_fr && empty($location->statut_en)) {
                // Assuming you want a default translation for statut_fr if statut_en is empty
                $translator = new \Stichoza\GoogleTranslate\GoogleTranslate('en');
                $translator->setSource('fr');
                $location->statut_en = $translator->translate($location->statut_fr);
            }
        });

        // On updating an existing location, handle statut translations
        static::updating(function ($location) {
            if ($location->isDirty('statut_fr')) {
                $translator = new \Stichoza\GoogleTranslate\GoogleTranslate('en');
                $translator->setSource('fr');
                $location->statut_en = $translator->translate($location->statut_fr);
            }
        });
    }

    /**
     * Relation avec le locataire
     */
    public function locataire()
    {
        return $this->belongsTo(Locataire::class, 'locaId');
    }

    /**
     * Relation avec le bailleur
     */
    public function bailleur()
    {
        return $this->belongsTo(Bailleur::class, 'bailId');
    }

    /**
     * Relation avec le logement
     */
    public function logement()
    {
        return $this->belongsTo(Logement::class, 'logId');
    }

    /**
     * Accesseur pour le statut dans la langue courante
     */
    public function getStatutAttribute()
    {
        $locale = app()->getLocale();
        $statutField = "statut_{$locale}";

        // Retourne le statut dans la langue actuelle, sinon retourne le statut en français
        return $this->$statutField ?? $this->statut_fr;
    }
}

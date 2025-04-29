<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stichoza\GoogleTranslate\GoogleTranslate;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Logement extends Model
{
    use HasFactory;

    protected $fillable = [
        'libelle',
        'libelle_en',
        'latitude',
        'longitude',
        'nbrpieces',
        'superficie',
        'nbr_salles_bain',
        'nbr_chambres',
        'climatisation',
        'meuble',
        'reference',
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
        'bailId'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($logement) {
            $translator = new GoogleTranslate('en');
            $translator->setSource('fr');

            if ($logement->libelle && !$logement->libelle_en) {
                $logement->libelle_en = $translator->translate($logement->libelle);
            }

            if ($logement->descrip_fr && !$logement->descrip_en) {
                $logement->descrip_en = $translator->translate($logement->descrip_fr);
            }

            if ($logement->dispo_fr && !$logement->dispo_en) {
                if ($logement->dispo_fr === 'LIBRE') {
                    $logement->dispo_en = 'FREE';
                } elseif ($logement->dispo_fr === 'OCCUPE') {
                    $logement->dispo_en = 'RENTED';
                }
            }
        });
    }

    // Relations

    public function typeLogement(): BelongsTo
    {
        return $this->belongsTo(TypeLogement::class, 'typLogId');
    }

    public function quartier(): BelongsTo
    {
        return $this->belongsTo(Quartier::class, 'quartierId');
    }

    public function bailleur(): BelongsTo
    {
        return $this->belongsTo(Bailleur::class, 'bailId');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ImageLogement::class, 'logId');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stichoza\GoogleTranslate\GoogleTranslate;

class Contrat extends Model
{
    use HasFactory;

    protected $fillable = [
        'refContrat_fr',
        'fichierJoint',
        'dateSignature',
        'bailId',
        'typContId',
        'logId',
        'refContrat_en',
    ];

    protected static function boot()
    {
        parent::boot();

        // Traduction automatique du champ refContrat_fr en refContrat_en si nécessaire
        static::creating(function ($contrat) {
            if (empty($contrat->refContrat_en) && !empty($contrat->refContrat_fr)) {
                $translator = new GoogleTranslate('en'); // Traduction vers l'anglais
                $translator->setSource('fr');
                $contrat->refContrat_en = $translator->translate($contrat->refContrat_fr);
            }
        });

        static::updating(function ($contrat) {
            if ($contrat->isDirty('refContrat_fr')) {
                $translator = new GoogleTranslate('en');
                $translator->setSource('fr');
                $contrat->refContrat_en = $translator->translate($contrat->refContrat_fr);
            }
        });
    }

    /**
     * Relation avec les bailleurs
     */
    public function bailleur()
    {
        return $this->belongsTo(Bailleur::class, 'bailId');
    }

    /**
     * Relation avec les typeContrats
     */
    public function typeContrat()
    {
        return $this->belongsTo(TypeContrat::class, 'typContId');
    }

    /**
     * Relation avec les logements
     */
    public function logement()
    {
        return $this->belongsTo(Logement::class, 'logId');
    }

    /**
     * Accesseur pour la référence du contrat dans la langue courante
     */
    public function getRefContratAttribute()
    {
        $locale = app()->getLocale();  // Langue courante de l'application
        $refContratField = "refContrat_{$locale}";  // Dynamique : 'refContrat_fr' ou 'refContrat_en'
        
        // Retourne la référence du contrat dans la langue actuelle, sinon retourne en français par défaut
        return $this->$refContratField ?? $this->refContrat_fr;
    }
}

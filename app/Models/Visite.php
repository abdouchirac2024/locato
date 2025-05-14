<?php

namespace App\Models;

use Stichoza\GoogleTranslate\GoogleTranslate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visite extends Model
{
    use HasFactory;

    protected $fillable = [
        'dateVisite',
        'heureVisite',
        'confirmation',
       
        'logId',
        'motifRejet_fr',
        'motifRejet_en',
        'statut_fr',
        'statut_en'
    ];

    /**
     * Relation avec les logements
     */
    public function logement()
    {
        return $this->belongsTo(Logement::class);
    }

   

    /**
     * Accesseur pour le statut dans la langue courante
     */
    public function getStatutAttribute()
    {
        $locale = app()->getLocale();
        $statutField = "statut_{$locale}";
        
        // Retourne le statut en français si la langue n'est pas disponible
        return $this->$statutField ?? $this->statut_fr;
    }

    /**
     * Accesseur pour le motif de rejet dans la langue courante
     */
    public function getMotifRejetAttribute()
    {
        $locale = app()->getLocale();
        $motifRejetField = "motifRejet_{$locale}";
        
        // Retourne le motif de rejet en français si la langue n'est pas disponible
        return $this->$motifRejetField ?? $this->motifRejet_fr;
    }

    protected static function boot()
    {
        parent::boot();

        // Lors de la création d'une visite, si les champs en anglais sont vides, les traduire à partir du français
        static::creating(function ($visite) {
            if (empty($visite->statut_en) && !empty($visite->statut_fr)) {
                $translator = new GoogleTranslate('en');
                $translator->setSource('fr');
                $visite->statut_en = $translator->translate($visite->statut_fr);
            }
            if (empty($visite->motifRejet_en) && !empty($visite->motifRejet_fr)) {
                $translator = new GoogleTranslate('en');
                $translator->setSource('fr');
                $visite->motifRejet_en = $translator->translate($visite->motifRejet_fr);
            }
        });

        // Lors de la mise à jour d'une visite, si le statut ou le motif de rejet en français sont modifiés, traduire en anglais
        static::updating(function ($visite) {
            if ($visite->isDirty('statut_fr')) {
                $translator = new GoogleTranslate('en');
                $translator->setSource('fr');
                $visite->statut_en = $translator->translate($visite->statut_fr);
            }
            if ($visite->isDirty('motifRejet_fr')) {
                $translator = new GoogleTranslate('en');
                $translator->setSource('fr');
                $visite->motifRejet_en = $translator->translate($visite->motifRejet_fr);
            }
        });
    }
}

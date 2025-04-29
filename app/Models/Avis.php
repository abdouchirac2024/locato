<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stichoza\GoogleTranslate\GoogleTranslate;

class Avis extends Model
{
    use HasFactory;

    protected $fillable = [
        'coment_fr',
        'note',
        'visible',
        'locaId',
        'logId',
        'coment_en',
    ];

    protected $casts = [
        'dateEnv' => 'datetime', // Cast pour la date
    ];

    // Relation avec les locataires
    public function locataire()
    {
        return $this->belongsTo(Locataire::class);
    }

    // Relation avec les logements
    public function logement()
    {
        return $this->belongsTo(Logement::class);
    }

    /**
     * Accesseur pour le commentaire dans la langue courante
     */
    public function getComentAttribute()
    {
        $locale = app()->getLocale();  // Récupère la langue courante
        $comentField = "coment_{$locale}";  // Détermine le champ de commentaire correspondant à la langue

        // Retourne le commentaire dans la langue courante, ou le français si la langue n'est pas disponible
        return $this->$comentField ?? $this->coment_fr;
    }

    protected static function boot()
    {
        parent::boot();

        // Lors de la création d'un avis, traduire le commentaire en anglais si le champ en anglais est vide
        static::creating(function ($avis) {
            if (empty($avis->coment_en) && !empty($avis->coment_fr)) {
                $translator = new GoogleTranslate('en');
                $translator->setSource('fr');
                $avis->coment_en = $translator->translate($avis->coment_fr);
            }
        });

        // Lors de la mise à jour d'un avis, traduire le commentaire en anglais si le champ en français est modifié
        static::updating(function ($avis) {
            if ($avis->isDirty('coment_fr')) {
                $translator = new GoogleTranslate('en');
                $translator->setSource('fr');
                $avis->coment_en = $translator->translate($avis->coment_fr);
            }
        });
    }
}

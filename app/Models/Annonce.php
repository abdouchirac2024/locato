<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stichoza\GoogleTranslate\GoogleTranslate;

class Annonce extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre_fr',
        'titre_en',
        'contenu_fr',
        'contenu_en',
        'date_publication',
        'date_expiration',
        'is_active',
        'logId',
        'bailId',
    ];

    /**
     * Définition des relations avec les bailleurs
     */
    public function bailleur()
    {
        return $this->belongsTo(Bailleur::class, 'bailId');
    }

    /**
     * Définition des relations avec les logements
     */
    public function logement()
    {
        return $this->belongsTo(Logement::class, 'logId');
    }

    /**
     * Accesseur pour récupérer les titres et contenus dans la langue actuelle
     */
    public function getTitreAttribute()
    {
        $locale = app()->getLocale();
        $titreField = "titre_{$locale}";

        // Retourne le titre dans la langue actuelle, sinon retourne le titre en français
        return $this->$titreField ?? $this->titre_fr;
    }

    public function getContenuAttribute()
    {
        $locale = app()->getLocale();
        $contenuField = "contenu_{$locale}";

        // Retourne le contenu dans la langue actuelle, sinon retourne le contenu en français
        return $this->$contenuField ?? $this->contenu_fr;
    }

    /**
     * Boot method for automatic translation
     */
    protected static function boot()
    {
        parent::boot();

        // Lors de la création d'une annonce, si le titre ou le contenu en anglais est manquant, on traduit à partir du français
        static::creating(function ($annonce) {
            if (empty($annonce->titre_en) && !empty($annonce->titre_fr)) {
                $translator = new GoogleTranslate('en');
                $translator->setSource('fr');
                $annonce->titre_en = $translator->translate($annonce->titre_fr);
            }
            if (empty($annonce->contenu_en) && !empty($annonce->contenu_fr)) {
                $translator = new GoogleTranslate('en');
                $translator->setSource('fr');
                $annonce->contenu_en = $translator->translate($annonce->contenu_fr);
            }
        });

        // Lors de la mise à jour d'une annonce, traduire à nouveau le titre et contenu si le français a été mis à jour
        static::updating(function ($annonce) {
            if ($annonce->isDirty('titre_fr')) {
                $translator = new GoogleTranslate('en');
                $translator->setSource('fr');
                $annonce->titre_en = $translator->translate($annonce->titre_fr);
            }
            if ($annonce->isDirty('contenu_fr')) {
                $translator = new GoogleTranslate('en');
                $translator->setSource('fr');
                $annonce->contenu_en = $translator->translate($annonce->contenu_fr);
            }
        });
    }
}

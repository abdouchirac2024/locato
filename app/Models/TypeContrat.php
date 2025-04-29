<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stichoza\GoogleTranslate\GoogleTranslate;

class TypeContrat extends Model
{
    use HasFactory;

    protected $fillable = [
        'libelle', 
        'description_fr', 
        'option', 
        'pourcentage', 
        'description_en',  // Ajout de la colonne en anglais
    ];

    /**
     * Relation avec les contrats (un type de contrat peut avoir plusieurs contrats associés)
     */
    public function contrat()
    {
        return $this->hasMany(Contrat::class);
    }

    /**
     * Accesseur pour obtenir la description dans la langue courante
     */
    public function getDescriptionAttribute()
    {
        $locale = app()->getLocale();  // Récupère la langue courante de l'application
        $descriptionField = "description_{$locale}";  // Détermine le champ de description selon la langue

        // Retourne la description dans la langue courante, sinon retourne la description en français
        return $this->$descriptionField ?? $this->description_fr;
    }

    /**
     * Lors de la création ou mise à jour du modèle, traduire le champ description si nécessaire
     */
    protected static function boot()
    {
        parent::boot();

        // Lors de la création, traduire le champ 'description_fr' vers l'anglais si ce dernier est vide
        static::creating(function ($typeContrat) {
            if (empty($typeContrat->description_en) && !empty($typeContrat->description_fr)) {
                $translator = new GoogleTranslate('en');
                $translator->setSource('fr');
                $typeContrat->description_en = $translator->translate($typeContrat->description_fr);
            }
        });

        // Lors de la mise à jour, traduire le champ 'description_fr' vers l'anglais si ce dernier est modifié
        static::updating(function ($typeContrat) {
            if ($typeContrat->isDirty('description_fr')) {
                $translator = new GoogleTranslate('en');
                $translator->setSource('fr');
                $typeContrat->description_en = $translator->translate($typeContrat->description_fr);
            }
        });
    }
}

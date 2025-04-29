<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stichoza\GoogleTranslate\GoogleTranslate;

class TypeLogement extends Model
{
    use HasFactory;

    protected $table = 'type_logements';

    protected $fillable = [
        'libelle_logement',
        'libelle_logement_en',
        'standing',
        'standing_en',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($typeLogement) {
            $translator = new GoogleTranslate('en');

            // Traduire automatiquement libelle_logement s'il existe et libelle_logement_en est vide
            if (!empty($typeLogement->libelle_logement) && empty($typeLogement->libelle_logement_en)) {
                $typeLogement->libelle_logement_en = $translator->translate($typeLogement->libelle_logement);
            }

            // Traduire automatiquement standing s'il existe et standing_en est vide
            if (!empty($typeLogement->standing) && empty($typeLogement->standing_en)) {
                $typeLogement->standing_en = $translator->translate($typeLogement->standing);
            }
        });
    }

    /**
     * Relation avec Logement
     */
    public function logement(): HasMany
    {
        return $this->hasMany(Logement::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeContrat extends Model
{
    protected $fillable = [
        'libelle',
        'description_fr',
        'option',
        'pourcentage',
        'description_en',
    ];
    public function contrat(): HasMany
    {
        return $this->hasMany(Contrat::class);
    }

    
       /**
     * Accesseur pour le nom dans la langue courante
     */
    public function getDescriptionAttribute()
    {
        $locale = app()->getLocale();
        $descriptionField = "description_{$locale}";
        
        // Retourne le français si la langue n'est pas dispo
        return $this->$descriptionField ?? $this->description_fr;
    }

    use HasFactory;
}

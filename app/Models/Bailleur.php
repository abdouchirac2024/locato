<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bailleur extends Model
{
    protected $fillable = [
        'verif',
        'numFiscal',
        'note',
        'description_fr',
        'nbrLog',
        'description_en',
    ];

    // Relation avec le logement
    public function logement(): HasMany
    {
        return $this->hasMany(Logement::class);
    }

    // Relation avec le contrat
    public function contrat(): HasMany
    {
        return $this->hasMany(Contrat::class);
    }

    // Relation avec la notification
    public function notification(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    // Relation avec la location
    public function location(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    // Relation avec le message
    public function message(): HasMany
    {
        return $this->hasMany(Message::class);
    }

     // Relation avec l' annonce
    public function annonce(): HasMany
    {
        return $this->hasMany(Annonce::class);
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

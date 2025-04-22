<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Logement extends Model
{
    protected $fillable = [
        'libelle',
        'nbrMois',
        'prix',
        'nbrpieces',
        'descrip_fr',
        'contPrep',
        'forage',
        'parking',
        'gardien',
        'dispo_fr',
        'typLogId',
        'quartierId',
        'bailId',
        'descrip_en',
        'dispo_en'
    ];

    // Relation avec les images
    public function typeLogement()
    {
        return $this->belongsTo(TypeLogement::class);
    }

       // Relation avec les quartiers
    public function quartier()
    {
        return $this->belongsTo(Quartier::class);
    }

       // Relation avec les bailleurs
    public function bailleur()
    {
        return $this->belongsTo(Bailleur::class);
    }

      // Relation avec les annonces
    public function annonce(): HasMany
    {
        return $this->hasMany(Annonce::class);
    }

    /**
     * Accesseur pour le nom dans la langue courante
     */
    public function getDescripAttribute()
    {
        $locale = app()->getLocale();
        $descripField = "descrip_{$locale}";
        
        // Retourne le français si la langue n'est pas dispo
        return $this->$descripField ?? $this->descrip_fr;
    }

    /**
     * Accesseur pour le nom dans la langue courante
     */
    public function getDispoAttribute()
    {
        $locale = app()->getLocale();
        $dispoField = "dispo_{$locale}";
        
        // Retourne le français si la langue n'est pas dispo
        return $this->$dispoField ?? $this->dispo_fr;
    }

    use HasFactory;
}

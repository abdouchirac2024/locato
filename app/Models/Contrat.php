<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contrat extends Model
{
    protected $fillable = [
        'refContrat_fr',
        'fichierJoint',
        'dateSignature',
        'bailId',
        'typConId',
        'logId',
        'refContrat_en'
    ];
    
    // Relation avec les bailleurs
    public function bailleur()
    {
        return $this->belongsTo(Bailleur::class);
    }

     // Relation avec les typeContrats
    public function typeContrat()
    {
        return $this->belongsTo(TypeContrat::class);
    }

    // Relation avec les logements
    public function logement()
    {
        return $this->belongsTo(Logement::class);
    }
    
    /**
     * Accesseur pour le nom dans la langue courante
     */
    public function getContenuAttribute()
    {
        $locale = app()->getLocale();
        $refContratield = "refContrat_{$locale}";
        
        // Retourne le français si la langue n'est pas dispo
        return $this->$refContratield ?? $this->refContrat_fr;
    }

    use HasFactory;
}



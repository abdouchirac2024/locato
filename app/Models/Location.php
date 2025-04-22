<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'dateDeb',
        'dateFin',
        'caution',
        'statut_fr',
        'locaId',
        'bailId',
        'logId',
        'statut_en'
    ];

    // Relation avec les images
    public function locataire()
    {
        return $this->belongsTo(Locataire::class);
    }

       // Relation avec les bailleur
    public function bailleur()
    {
        return $this->belongsTo(Bailleur::class);
    }

       // Relation avec les logements
    public function logement()
    {
        return $this->belongsTo(Logement::class);
    }

    /**
     * Accesseur pour le nom dans la langue courante
     */
    public function getStatutAttribute()
    {
        $locale = app()->getLocale();
        $statutField = "statut_{$locale}";
        
        // Retourne le français si la langue n'est pas dispo
        return $this->$statutField ?? $this->statut_fr;
    }
    use HasFactory;
}

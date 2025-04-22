<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Avis extends Model
{
    protected $fillable = [
        'coment_fr',
        'note',
        'visible',
        'locaId',
        'logId',
        'coment_en',
    ];
    protected $casts = [
        'dateEnv'
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
     * Accesseur pour le nom dans la langue courante
     */
    public function getComentAttribute()
    {
        $locale = app()->getLocale();
        $comentField = "coment_{$locale}";
        
        // Retourne le français si la langue n'est pas dispo
        return $this->$comentField ?? $this->coment_fr;
    }

    use HasFactory;
}

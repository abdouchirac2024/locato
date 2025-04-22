<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'msg_fr',
        'result',
        'locaId',
        'bailId',
        'msg_en'
    ];
    protected $casts = [
        'dateEnv',
    ];
     // Relation avec les locataires
     public function locataire()
     {
         return $this->belongsTo(Locataire::class);
     }
 
        // Relation avec les bailleurs
     public function bailleur()
     {
         return $this->belongsTo(Bailleur::class);
     }

        /**
     * Accesseur pour le nom dans la langue courante
     */
    public function getContenuAttribute()
    {
        $locale = app()->getLocale();
        $contenuField = "contenu_{$locale}";
        
        // Retourne le français si la langue n'est pas dispo
        return $this->$contenuField ?? $this->contenu_fr;
    }
     
    use HasFactory;
}

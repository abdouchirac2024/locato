<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visite extends Model
{
    protected $fillable = [
        'dateVisite',
        'heureVisite',
        'confirmation',
        'payId',
        'logId',
        'motifRejet_fr',
        'motifRejet_en'
    ];

      // Relation avec les logements
    public function logement()
    {
        return $this->belongsTo(Logement::class);
    }

      // Relation avec le paiment
    public function payement()
    {
        return $this->belongsTo(Payement::class);
    }

    /**
     * Accesseur pour le nom dans la langue courante
     */
    public function getMotifRejetAttribute()
    {
        $locale = app()->getLocale();
        $motifRejetField = "motifRejet_{$locale}";
        
        // Retourne le français si la langue n'est pas dispo
        return $this->$motifRejetField ?? $this->motifRejet_fr;
    }
    use HasFactory;
}

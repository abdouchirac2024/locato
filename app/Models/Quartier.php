<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quartier extends Model
{
    protected $fillable = [
        'nomQuartier',
        'villeId',
    ];
    use HasFactory;

       // Relation avec les villes
       public function ville()
       {
           return $this->belongsTo(Ville::class);
       }
    use HasFactory;
}

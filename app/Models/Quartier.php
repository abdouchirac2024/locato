<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quartier extends Model
{
    use HasFactory;
    protected $fillable = [
        'nomQuartier',
        'villeId',
    ];
    use HasFactory;

    
       public function ville()
{
    return $this->belongsTo(Ville::class, 'villeId');
}

    use HasFactory;
}

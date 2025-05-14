<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payement extends Model
{
    protected $fillable = [
        'montant',
        'reference',
        'operateur',
       
        'locaId',
    ];
// Relation avec les locataires
public function locataire()
{
    return $this->belongsTo(Locataire::class);
}

    use HasFactory;
}

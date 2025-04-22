<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Annonce extends Model
{
    protected $fillable = [
        'bailId',
        'logId',
    ];

    protected $casts = [
        'dateEnv'
    ];
    
         // Relation avec les bailleurs
    public function bailleur()
    {
        return $this->belongsTo(Bailleur::class);
    }

       // Relation avec les logements
    public function logement()
    {
        return $this->belongsTo(Logement::class);
    }
    use HasFactory;
}

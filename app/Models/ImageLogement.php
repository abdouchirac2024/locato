<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImageLogement extends Model
{
    protected $fillable = [
        'urlImage',
        'taille',
        'logId',
    ];

    // Relation avec les logements
    public function logement()
    {
        return $this->belongsTo(Logement::class);
    }

    use HasFactory;
}


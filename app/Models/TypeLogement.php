<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeLogement extends Model
{
    protected $fillable = [
        'Standing',
    ];
    
    // Relation avec les logements
    public function logement(): HasMany
    {
        return $this->hasMany(Logement::class);
    }
    use HasFactory;
}

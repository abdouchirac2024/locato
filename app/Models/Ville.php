<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ville extends Model
{
    protected $fillable = [
        'nomVille',
    ];
    
    // Relation avec la ville
    public function quartier(): HasMany
    {
        return $this->hasMany(Quartier::class);
    }
    use HasFactory;
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Locataire extends Model
{
    protected $fillable = [
        'preference',
        'nrbvist',
    ];

    // Relation avec le message
    public function message(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    // Relation avec la notification
    public function notification(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    // Relation avec la location
    public function location(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    // Relation avec l'Avis
    public function avis(): HasMany
    {
        return $this->hasMany(Avis::class);
    }

    // Relation avec le paiement
    public function payement(): HasMany
    {
        return $this->hasMany(Payement::class);
    }
    use HasFactory;
}

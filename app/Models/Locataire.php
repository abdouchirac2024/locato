<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stichoza\GoogleTranslate\GoogleTranslate;
class Locataire extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'preference',
        // "status_fr",
        // "status_en",
        'nrbvist'
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

    public function user()
    {
        return $this->belongsTo(User::class);
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
}

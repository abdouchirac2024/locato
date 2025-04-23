<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stichoza\GoogleTranslate\GoogleTranslate;

class Bailleur extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'verif',
        'numFiscal',
        'description_fr',
        'description_en',
        'nbrLog',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($bailleur) {
            if ($bailleur->description_fr && empty($bailleur->description_en)) {
                $translator = new GoogleTranslate('en');
                $translator->setSource('fr');
                $bailleur->description_en = $translator->translate($bailleur->description_fr);
            }
        });

        static::updating(function ($bailleur) {
            if ($bailleur->isDirty('description_fr')) {
                $translator = new GoogleTranslate('en');
                $translator->setSource('fr');
                $bailleur->description_en = $translator->translate($bailleur->description_fr);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function logement(): HasMany
    {
        return $this->hasMany(Logement::class);
    }

    public function contrat(): HasMany
    {
        return $this->hasMany(Contrat::class);
    }

    public function notification(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function location(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public function message(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function annonce(): HasMany
    {
        return $this->hasMany(Annonce::class);
    }

    /**
     * Accesseur pour la description dans la langue courante
     */
    public function getDescriptionAttribute()
    {
        $locale = app()->getLocale();
        $descriptionField = "description_{$locale}";

        return $this->$descriptionField ?? $this->description_fr;
    }
}

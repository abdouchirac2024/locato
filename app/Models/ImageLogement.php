<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stichoza\GoogleTranslate\GoogleTranslate;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImageLogement extends Model
{
    use HasFactory;

    protected $fillable = [
        'logId', // <-- AJOUTER CETTE LIGNE
        'urlImage',
        'taille',
        'is_principale',
        'titre',
        'titre_en',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($image) {
            $translator = new GoogleTranslate('en');
            $translator->setSource('fr');

            if ($image->titre && !$image->titre_en) {
                $image->titre_en = $translator->translate($image->titre);
            }
        });
    }

    // Relation

    public function logement(): BelongsTo
    {
        return $this->belongsTo(Logement::class, 'logId');
    }
}

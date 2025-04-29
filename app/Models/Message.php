<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stichoza\GoogleTranslate\GoogleTranslate;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'contenu_fr',
        'lecture',
        'locaId',
        'bailId',
        'contenu_en',
    ];

    protected $casts = [
        'dateEnv' => 'datetime',
        'dateLect' => 'datetime',
    ];

    // Relation avec les locataires
    public function locataire()
    {
        return $this->belongsTo(Locataire::class, 'locaId');
    }

    // Relation avec les bailleurs
    public function bailleur()
    {
        return $this->belongsTo(Bailleur::class, 'bailId');
    }

    // Méthode boot() pour gérer la traduction
    protected static function boot()
    {
        parent::boot();

        // Traduction automatique de contenu_fr à contenu_en si nécessaire
        static::creating(function ($message) {
            if (empty($message->contenu_en) && !empty($message->contenu_fr)) {
                $translator = new GoogleTranslate('en'); // Traduction vers l'anglais
                $translator->setSource('fr');
                $message->contenu_en = $translator->translate($message->contenu_fr);
            }
        });

        static::updating(function ($message) {
            if ($message->isDirty('contenu_fr')) {
                $translator = new GoogleTranslate('en');
                $translator->setSource('fr');
                $message->contenu_en = $translator->translate($message->contenu_fr);
            }
        });
    }

    /**
     * Accesseur pour le contenu dans la langue courante
     */
    public function getContenuAttribute()
    {
        $locale = app()->getLocale();
        $contenuField = "contenu_{$locale}";  // Dynamique : 'contenu_fr' ou 'contenu_en'

        // Retourne le contenu dans la langue actuelle, sinon retourne en français par défaut
        return $this->$contenuField ?? $this->contenu_fr;
    }
}

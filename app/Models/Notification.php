<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stichoza\GoogleTranslate\GoogleTranslate;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'msg_fr',
        'result',
        'locaId',
        'bailId',
        'msg_en',
        'result_en', // Add result_en to the fillable fields
    ];

    protected $casts = [
        'dateEnv' => 'datetime',
    ];

    // Relations with locataires
    public function locataire()
    {
        return $this->belongsTo(Locataire::class);
    }

    // Relations with bailleurs
    public function bailleur()
    {
        return $this->belongsTo(Bailleur::class);
    }

    // Boot method for handling translation
    protected static function boot()
    {
        parent::boot();

        // Automatically translate the result field if not set
        static::creating(function ($notification) {
            if (empty($notification->result_en) && !empty($notification->result)) {
                $translator = new GoogleTranslate('en'); // Translate to English
                $translator->setSource('fr'); // Source language: French
                $notification->result_en = $translator->translate($notification->result); // Translate and set the English result
            }
        });

        static::updating(function ($notification) {
            if ($notification->isDirty('result')) {
                $translator = new GoogleTranslate('en'); // Translate to English
                $translator->setSource('fr'); // Source language: French
                $notification->result_en = $translator->translate($notification->result); // Translate and update the English result
            }
        });
    }

    /**
     * Accessor for the message in the current language
     */
    public function getMsgAttribute()
    {
        $locale = app()->getLocale();
        $msgField = "msg_{$locale}";  // Dynamically select msg_fr or msg_en

        // Return the message in the current language, default to French if not available
        return $this->$msgField ?? $this->msg_fr;
    }
}

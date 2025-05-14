<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quartier extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomQuartier',
        'villeId',
        'del_yn',
        'created_by'
    ];

    protected $attributes = [
        'del_yn' => 'N'
    ];

    public function ville(): BelongsTo
    {
        return $this->belongsTo(Ville::class, 'villeId');
    }

    // Relation avec l'utilisateur qui a créé le quartier
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scope pour ne récupérer que les quartiers non supprimés
    public function scopeActive($query)
    {
        return $query->where('del_yn', 'N');
    }

    // Méthode pour le soft delete
    public function softDelete()
    {
        $this->update(['del_yn' => 'Y']);
    }
}

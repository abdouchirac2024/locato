<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ville extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomVille',
        'del_yn',
        'created_by'
    ];

    protected $attributes = [
        'del_yn' => 'N'
    ];
    
    // Relation avec les quartiers
    public function quartiers(): HasMany
    {
        return $this->hasMany(Quartier::class);
    }

    // Relation avec l'utilisateur qui a créé la ville
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scope pour ne récupérer que les villes non supprimées
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

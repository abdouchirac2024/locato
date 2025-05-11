<?php

namespace App\Models;

use App\Models\Bailleur;
use App\Models\Locataire;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'prenom',
        'matricule',
        'telephone',
        'photoProfile',
        'cni',
        'quartier_id',
        'role',
        'verification_code',
        'email_verified_at',
        'status'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'verification_code'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function quartier()
    {
        return $this->belongsTo(Quartier::class);
    }

    public function locataire()
    {
        return $this->hasOne(Locataire::class);
    }

    public function bailleur()
    {
        return $this->hasOne(Bailleur::class);
    }

    public function isAdmin()
    {
        return $this->role === 'ADMIN';
    }

    public function isBailleur()
    {
        return $this->role === 'Bailleur';
    }

    public function isBailleurVerified()
    {
        return $this->isBailleur() && $this->bailleur->statut_fr === 'verifie';
    }

    public function isLocataire()
    {
        return $this->role === 'Locataire';
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\VerifyEmailNotification($this->verification_code));
    }


    public function sendPasswordResetNotification($token)
{
    $this->notify(new \App\Notifications\ResetPasswordNotification($token));
}

    // Méthode pour activer un utilisateur
    public function activate()
    {
        $this->update(['status' => 'active']);
    }

    // Méthode pour désactiver un utilisateur
    public function deactivate()
    {
        $this->update(['status' => 'inactive']);
    }

    // Vérifier si l'utilisateur est actif
    public function isActive()
    {
        return $this->status === 'active';
    }
}

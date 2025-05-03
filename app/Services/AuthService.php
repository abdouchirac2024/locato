<?php

namespace App\Services;

use App\Mail\VerificationCodeMail;
use App\Mail\WelcomeMail;
use App\Mail\BailleurVerifiedMail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthService
{
    public function register(array $data): User
    {
        // Générer un matricule unique
        $matricule = strtoupper(Str::random(7));

        // Créer l'utilisateur
        $user = User::create([
            'name' => $data['name'],
            'prenom' => $data['prenom'] ?? null,
            'email' => $data['email'] ?? null,
            'telephone' => $data['telephone'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'quartier_id' => $data['quartier_id'] ?? null,
            'matricule' => $matricule,
            'verification_code' => rand(1000, 9999),
        ]);

        // Créer le profil spécifique selon le rôle
        if ($user->isLocataire()) {
            $user->locataire()->create([
                'preference' => $data['preference'],
            ]);
        } elseif ($user->isBailleur()) {
            $user->bailleur()->create([
                'numFiscal' => $data['numFiscal'],
                'description_fr' => $data['description'],
                'nbrLog' => 0,
            ]);
        }

        // Envoyer le code de vérification par email si email fourni
        if ($user->email) {
            try {
                \Log::info('Tentative d\'envoi d\'email de vérification à: ' . $user->email);
                Mail::to($user->email)->send(new VerificationCodeMail($user->verification_code));
                \Log::info('Email de vérification envoyé avec succès à: ' . $user->email);
            } catch (\Exception $e) {
                \Log::error('Erreur lors de l\'envoi de l\'email de vérification à ' . $user->email . ': ' . $e->getMessage());
            }
        } else {
            \Log::warning('Pas d\'email fourni pour l\'utilisateur: ' . $user->name);
        }

        return $user;
    }

    public function verifyEmail(User $user, string $code): bool
    {
        if ($user->verification_code === $code) {
            $user->update([
                'email_verified_at' => now(),
                'verification_code' => null,
            ]);

            // Envoyer un email de bienvenue
            if ($user->email) {
                try {
                    \Log::info('Tentative d\'envoi d\'email de bienvenue à: ' . $user->email);
                    Mail::to($user->email)->send(new WelcomeMail($user));
                    \Log::info('Email de bienvenue envoyé avec succès à: ' . $user->email);
                } catch (\Exception $e) {
                    \Log::error('Erreur lors de l\'envoi de l\'email de bienvenue à ' . $user->email . ': ' . $e->getMessage());
                }
            }

            return true;
        }

        return false;
    }

    public function login(string $login, string $password): ?User
    {
        $user = User::where('email', $login)
            ->orWhere('telephone', $login)
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw new \Exception("Identifiants incorrects");
        }

        // Vérifier si c'est un bailleur
        if ($user->isBailleur()) {
            if (!$user->bailleur) {
                throw new \Exception("Votre compte bailleur n'est pas encore configuré. Veuillez contacter l'administrateur.");
            }
            
            if (!$user->bailleur->verif || $user->bailleur->statut_fr !== 'verifie') {
                throw new \Exception("Votre compte bailleur est en attente de validation par l'administrateur. Veuillez patienter.");
            }
        }

        return $user;
    }

    public function verifyBailleur(User $user): bool
    {
        if (!$user->isBailleur()) {
            return false;
        }

        // Mettre à jour tous les champs du bailleur
        $bailleur = $user->bailleur;
        if (!$bailleur) {
            return false;
        }

        $bailleur->verif = true;
        $bailleur->statut_fr = 'verifie';
        $bailleur->statut_en = 'verified';
        $bailleur->save();

        // Mettre à jour la date de vérification de l'utilisateur
        $user->email_verified_at = now();
        $user->save();

        // Envoyer l'email de notification
        if ($user->email) {
            try {
                \Log::info('Tentative d\'envoi d\'email de vérification bailleur', [
                    'email' => $user->email,
                    'user_id' => $user->id
                ]);
                
                Mail::to($user->email)->send(new BailleurVerifiedMail($user));
                
                \Log::info('Email de vérification bailleur envoyé avec succès', [
                    'email' => $user->email,
                    'user_id' => $user->id
                ]);
            } catch (\Exception $e) {
                \Log::error('Erreur lors de l\'envoi de l\'email de vérification bailleur', [
                    'email' => $user->email,
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return true;
    }

    public function updateProfile(User $user, array $data): User
    {
        $user->update([
            'name' => $data['name'] ?? $user->name,
            'prenom' => $data['prenom'] ?? $user->prenom,
            'email' => $data['email'] ?? $user->email,
            'telephone' => $data['telephone'] ?? $user->telephone,
            'quartier_id' => $data['quartier_id'] ?? $user->quartier_id,
        ]);

        // Mettre à jour la photo de profil si fournie
        if (isset($data['photoProfile'])) {
            $path = $data['photoProfile']->store('profiles', 'public');
            $user->update(['photoProfile' => $path]);
        }

        // Mettre à jour la CNI si fournie
        if (isset($data['cni'])) {
            $path = $data['cni']->store('cni', 'public');
            $user->update(['cni' => $path]);
        }

        // Mettre à jour les données spécifiques au rôle
        if ($user->isLocataire() && isset($data['preference'])) {
            $user->locataire()->update(['preference' => $data['preference']]);
        }

        if ($user->isBailleur()) {
            $updateData = [];
            if (isset($data['numFiscal'])) {
                $updateData['numFiscal'] = $data['numFiscal'];
            }
            if (isset($data['description'])) {
                $updateData['description_fr'] = $data['description'];
            }
            if (!empty($updateData)) {
                $user->bailleur()->update($updateData);
            }
        }

        return $user->fresh();
    }
}

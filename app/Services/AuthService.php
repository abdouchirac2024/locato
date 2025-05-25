<?php

namespace App\Services;

use App\Mail\VerificationCodeMail;
use App\Mail\WelcomeMail;
use App\Mail\BailleurVerifiedMail;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;

class AuthService
{
    public function register(array $data): User
    {
        $matricule = strtoupper(Str::random(7));

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
            'status' => 'active',
        ]);

        if ($user->isLocataire()) {
            $user->locataire()->create([
                'preference' => $data['preference'] ?? null,
            ]);
        } elseif ($user->isBailleur()) {
            $user->bailleur()->create([
                'numFiscal' => $data['numFiscal'] ?? null,
                'description_fr' => $data['description'] ?? null,
                'nbrLog' => 0,
                'verif' => false,
                'statut_fr' => 'en_attente',
            ]);
        }

        if ($user->email) {
            try {
                Mail::to($user->email)->send(new VerificationCodeMail($user->verification_code));
            } catch (\Exception $e) {
                Log::error('Erreur envoi email vérification: ' . $e->getMessage());
            }
        }

        return $user;
    }

    public function verifyEmail(User $user, string $code): bool
    {
        if ((string)$user->verification_code === (string)$code) {
            $user->update([
                'email_verified_at' => now(),
                'verification_code' => null,
                'status' => 'active',
            ]);

            if ($user->email) {
                try {
                    Mail::to($user->email)->send(new WelcomeMail($user));
                } catch (\Exception $e) {
                    Log::error('Erreur envoi email bienvenue: ' . $e->getMessage());
                }
            }

            return true;
        }

        return false;
    }

    public function login(string $login, string $password): User
    {
        $user = User::where('email', $login)->orWhere('telephone', $login)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw new \Exception("Identifiants incorrects.");
        }

        if ($user->status !== 'active') {
            throw new \Exception("Votre compte est inactif. Veuillez contacter l'administrateur.");
        }

        if ($user->isBailleur() && (!$user->bailleur || !$user->bailleur->verif)) {
            throw new \Exception("Votre compte bailleur n'est pas encore vérifié par l'administrateur.");
        }

        return $user;
    }

    public function updateProfile(User $user, array $data): User
    {
        Log::info('AuthService: Updating profile', ['user_id' => $user->id, 'data_keys' => array_keys($data)]);

        $updateData = [];

        if (isset($data['name'])) $updateData['name'] = $data['name'];
        if (array_key_exists('prenom', $data)) $updateData['prenom'] = $data['prenom'];
        if (isset($data['email'])) $updateData['email'] = $data['email'];
        if (isset($data['telephone'])) $updateData['telephone'] = $data['telephone'];
        if (array_key_exists('quartier_id', $data)) $updateData['quartier_id'] = $data['quartier_id'];

        // Logic for photoProfile upload
        if (isset($data['photoProfile'])) {
            // Ensure the uploaded file is a valid UploadedFile instance and is valid
            if ($data['photoProfile'] instanceof UploadedFile && $data['photoProfile']->isValid()) {
                // Delete old profile photo if it exists
                if ($user->photoProfile && Storage::disk('public')->exists($user->photoProfile)) {
                    Storage::disk('public')->delete($user->photoProfile);
                    Log::debug('Old profile photo deleted', ['path' => $user->photoProfile]);
                }

                // Store the new profile photo
                $path = $data['photoProfile']->store('profiles', 'public');

                // Check if the store operation was successful
                if ($path) {
                    $updateData['photoProfile'] = $path;
                    Log::debug('New profile photo stored', ['path' => $path]);
                } else {
                    Log::error('Failed to store new profile photo (store method returned false/null)', ['user_id' => $user->id, 'original_name' => $data['photoProfile']->getClientOriginalName()]);
                    // You might want to throw an exception here or return an error message
                    // For now, we log and continue, which means the photoProfile field won't be updated.
                }
            } else {
                Log::warning('Invalid or no profile photo uploaded (not valid or not UploadedFile)', [
                    'user_id' => $user->id,
                    'file_isset' => isset($data['photoProfile']),
                    'is_uploaded_file' => isset($data['photoProfile']) ? ($data['photoProfile'] instanceof UploadedFile) : false,
                    'is_valid' => (isset($data['photoProfile']) && $data['photoProfile'] instanceof UploadedFile) ? $data['photoProfile']->isValid() : 'N/A'
                ]);
                // Skip updating photoProfile if file is invalid
            }
        }

        if (isset($data['cni'])) {
             if ($data['cni'] instanceof UploadedFile && $data['cni']->isValid()) {
                if ($user->cni && Storage::disk('public')->exists($user->cni)) {
                    Storage::disk('public')->delete($user->cni);
                }
                $path = $data['cni']->store('cni_files', 'public');
                 if ($path) {
                    $updateData['cni'] = $path;
                 } else {
                     Log::error('Failed to store CNI file', ['user_id' => $user->id]);
                 }
             } else {
                 Log::warning('Invalid or no CNI file uploaded', ['user_id' => $user->id]);
             }
        }


        if (!empty($updateData)) {
            $user->update($updateData);
        }

        if ($user->isLocataire() && array_key_exists('preference', $data)) {
            $user->locataire()->update(['preference' => $data['preference']]);
        }

        if ($user->isBailleur()) {
            $bailleurUpdateData = [];
            if (array_key_exists('numFiscal', $data)) $bailleurUpdateData['numFiscal'] = $data['numFiscal'];
            if (array_key_exists('description', $data)) $bailleurUpdateData['description_fr'] = $data['description'];
            if (!empty($bailleurUpdateData)) {
                $user->bailleur()->update($bailleurUpdateData);
            }
        }

        return $user->fresh(['locataire', 'bailleur', 'quartier']);
    }

    public function updateUserRole(int $userIdToUpdate, string $newRole): User
    {
        Log::info('AuthService: Admin attempting to update user role.', [
            'admin_id' => Auth::id(),
            'user_to_update_id' => $userIdToUpdate,
            'new_role' => $newRole
        ]);

        $userToUpdate = User::findOrFail($userIdToUpdate);

        $validRoles = [User::ROLE_ADMIN, User::ROLE_BAILLEUR, User::ROLE_LOCATAIRE];
        if (!in_array($newRole, $validRoles)) {
            throw new \InvalidArgumentException("Le rôle '{$newRole}' n'est pas valide.");
        }

        if ($userToUpdate->role !== $newRole) {
            $userToUpdate->role = $newRole;
            $userToUpdate->save();

            if ($newRole === User::ROLE_BAILLEUR && !$userToUpdate->bailleur) {
                $userToUpdate->bailleur()->create([]);
            } elseif ($newRole === User::ROLE_LOCATAIRE && !$userToUpdate->locataire) {
                $userToUpdate->locataire()->create([]);
            }

            Log::info('User role updated successfully.', ['user_id' => $userToUpdate->id, 'new_role' => $newRole]);
        } else {
            Log::info('User role is already set to the target role.', ['user_id' => $userToUpdate->id, 'role' => $newRole]);
        }

        return $userToUpdate->fresh(['locataire', 'bailleur']);
    }

    public function verifyBailleur(User $bailleur): bool
    {
        if (!$bailleur->isBailleur() || !$bailleur->bailleur) {
            throw new \Exception("L'utilisateur n'est pas un bailleur ou son profil est incomplet.");
        }

        $bailleur->bailleur->update(['verif' => true, 'statut_fr' => 'validé']);

        try {
            Mail::to($bailleur->email)->send(new BailleurVerifiedMail($bailleur));
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'envoi de l'email de vérification du bailleur : " . $e->getMessage());
        }

        return true;
    }
}
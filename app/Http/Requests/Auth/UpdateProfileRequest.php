<?php
namespace App\Http\Requests\Auth; // Assurez-vous que le namespace est correct

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette requête.
     * L'utilisateur doit être authentifié pour mettre à jour son propre profil.
     */
    public function authorize(): bool
    {
        return Auth::check(); // Vérifie simplement que l'utilisateur est connecté
    }

    /**
     * Obtenir les règles de validation qui s'appliquent à la requête.
     */
    public function rules(): array
    {
        $userId = Auth::id(); // ID de l'utilisateur connecté

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'], // Rendre 'name' requis si présent
            'prenom' => ['sometimes', 'nullable', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required', // Rendre 'email' requis si présent
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId), // Unique, sauf pour l'utilisateur actuel
            ],
            'telephone' => [
                'sometimes',
                'required', // Rendre 'telephone' requis si présent
                'string',
                'max:20', // Ajustez max si besoin
                Rule::unique('users')->ignore($userId),
            ],
            'photoProfile' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'], // Image, max 2MB
            'cni' => ['sometimes', 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'], // Fichier PDF ou image pour CNI
            'quartier_id' => ['sometimes', 'nullable', 'integer', 'exists:quartiers,id'],

            // Champs spécifiques au rôle (le rôle lui-même ne peut pas être changé ici)
            // Si l'utilisateur est Locataire
            'preference' => [
                Rule::requiredIf(fn () => Auth::user()?->isLocataire() && $this->has('preference')),
                'nullable',
                'string'
            ],
            // Si l'utilisateur est Bailleur
            'numFiscal' => [
                Rule::requiredIf(fn () => Auth::user()?->isBailleur() && $this->has('numFiscal')),
                'nullable',
                'string',
                'max:255'
            ],
            'description' => [ // Pour la description_fr du bailleur
                Rule::requiredIf(fn () => Auth::user()?->isBailleur() && $this->has('description')),
                'nullable',
                'string'
            ],
            // Ne pas permettre la modification du mot de passe ici, utiliser une route dédiée.
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom est requis.',
            'email.required' => 'L\'adresse e-mail est requise.',
            'email.email' => 'Veuillez fournir une adresse e-mail valide.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'telephone.required' => 'Le numéro de téléphone est requis.',
            'telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'photoProfile.image' => 'Le fichier de la photo de profil doit être une image.',
            'photoProfile.mimes' => 'Formats d\'image acceptés pour la photo: jpeg, png, jpg, gif, webp.',
            'photoProfile.max' => 'La photo de profil ne doit pas dépasser 2 Mo.',
            'cni.file' => 'Le fichier CNI doit être un fichier valide.',
            'cni.mimes' => 'Formats acceptés pour la CNI: pdf, jpg, jpeg, png.',
            'cni.max' => 'Le fichier CNI ne doit pas dépasser 2 Mo.',
            'quartier_id.exists' => 'Le quartier sélectionné n\'est pas valide.',
        ];
    }
}
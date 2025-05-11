<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette requête
     */
    public function authorize(): bool
    {
        return true; // Tout le monde peut faire une demande de réinitialisation
    }

    /**
     * Règles de validation pour la réinitialisation du mot de passe
     */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'], // Token de réinitialisation
            'email' => ['required', 'email', 'exists:users,email'], // Email de l'utilisateur
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase() // Doit contenir majuscules et minuscules
                    ->numbers() // Doit contenir des chiffres
                    ->symbols() // Doit contenir des symboles
                    ->uncompromised(), // Vérifie que le mot de passe n'a pas été compromis
            ],
        ];
    }

    /**
     * Messages d'erreur personnalisés
     */
    public function messages(): array
    {
        return [
            'token.required' => 'Le token de réinitialisation est requis',
            'email.required' => 'L\'adresse email est requise',
            'email.email' => 'L\'adresse email doit être valide',
            'email.exists' => 'Aucun utilisateur trouvé avec cette adresse email',
            'password.required' => 'Le nouveau mot de passe est requis',
            'password.confirmed' => 'Les mots de passe ne correspondent pas',
            'password.min' => 'Le mot de passe doit contenir au moins :min caractères',
        ];
    }

    /**
     * Préparation des données avant validation
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'email' => strtolower($this->email), // Normalise l'email en minuscules
        ]);
    }
}

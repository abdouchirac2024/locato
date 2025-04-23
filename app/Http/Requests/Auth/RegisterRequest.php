<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'prenom' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:users,email'],
            'telephone' => ['required', 'string', 'unique:users,telephone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::in(['Locataire', 'Bailleur'])],
            'quartier_id' => ['nullable', 'exists:quartiers,id'],
            
            // Champs spécifiques pour locataire
            'preference' => ['required_if:role,Locataire', 'nullable', 'string'],
            
            // Champs spécifiques pour bailleur
            'numFiscal' => ['required_if:role,Bailleur', 'nullable', 'string'],
            'description' => ['required_if:role,Bailleur', 'nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'role.in' => 'Le rôle doit être soit Locataire soit Bailleur',
            'preference.required_if' => 'Le champ préférence est requis pour les locataires',
            'numFiscal.required_if' => 'Le numéro fiscal est requis pour les bailleurs',
            'description.required_if' => 'La description est requise pour les bailleurs',
        ];
    }
}

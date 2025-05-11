<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = Auth::id();

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'prenom' => ['nullable', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'email',
                Rule::unique('users')->ignore($userId),
            ],
            'telephone' => [
                'sometimes',
                'string',
                Rule::unique('users')->ignore($userId),
            ],
            'photoProfile' => ['nullable', 'image', 'max:2048'],
            'cni' => ['nullable', 'file', 'mimes:pdf,jpg,png', 'max:2048'],
            'quartier_id' => ['nullable', 'exists:quartiers,id'],
            
            // Champs spécifiques pour locataire
            'preference' => ['sometimes_if:role,Locataire', 'nullable', 'string'],
            
            // Champs spécifiques pour bailleur
            'numFiscal' => ['sometimes_if:role,Bailleur', 'nullable', 'string'],
            'description' => ['sometimes_if:role,Bailleur', 'nullable', 'string'],
        ];
    }
}

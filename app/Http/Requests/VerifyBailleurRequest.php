<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyBailleurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'L\'identifiant de l\'utilisateur est requis',
            'user_id.exists' => 'L\'utilisateur spécifié n\'existe pas',
        ];
    }
} 
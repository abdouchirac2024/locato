<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'digits:4'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Le code de vérification est requis',
            'code.digits' => 'Le code doit contenir exactement 4 chiffres',
        ];
    }
}

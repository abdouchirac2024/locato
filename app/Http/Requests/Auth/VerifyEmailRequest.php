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
<<<<<<< HEAD
=======
            'email' => ['required', 'email', 'exists:users,email'],
>>>>>>> 172c567c215f6076d8e6aab0684aa69c300f9117
            'code' => ['required', 'string', 'digits:4'],
        ];
    }

    public function messages(): array
    {
        return [
<<<<<<< HEAD
=======
            'email.required' => 'L\'email est requis',
            'email.email' => 'L\'email doit être une adresse email valide',
            'email.exists' => 'Aucun utilisateur trouvé avec cet email',
>>>>>>> 172c567c215f6076d8e6aab0684aa69c300f9117
            'code.required' => 'Le code de vérification est requis',
            'code.digits' => 'Le code doit contenir exactement 4 chiffres',
        ];
    }
}

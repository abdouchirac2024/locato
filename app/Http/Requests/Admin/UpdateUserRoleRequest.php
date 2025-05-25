<?php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\User; // Importer le modèle User pour les constantes de rôle

class UpdateUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Seul un Admin authentifié peut changer un rôle
        return Auth::check() && Auth::user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            // 'user_id_to_update' => ['required', 'integer', 'exists:users,id'], // On utilisera le paramètre de route
            'role' => ['required', 'string', Rule::in([User::ROLE_ADMIN, User::ROLE_BAILLEUR, User::ROLE_LOCATAIRE])],
        ];
    }

    public function messages(): array
    {
        return [
            'role.required' => 'Le nouveau rôle est obligatoire.',
            'role.in' => 'Le rôle sélectionné n\'est pas valide. Les rôles valides sont : ADMIN, Bailleur, Locataire.',
        ];
    }
}
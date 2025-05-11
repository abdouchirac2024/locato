<?php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateTypeLogementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()?->isAdmin();
    }

    public function rules(): array
    {
        $libelleCol = 'libelle_logement';
        $standingCol = 'standing';
        $typeLogementId = $this->route('types_logement'); // Nom du paramètre de route

        return [
            $libelleCol => [
                'sometimes', 'required', 'string', 'max:255',
                 Rule::unique('type_logements', $libelleCol)->ignore($typeLogementId)
             ],
             $standingCol => [
                'sometimes', 'nullable', 'string', 'max:255',
                 // Vérifie l'unicité seulement si une valeur non nulle est fournie
                 Rule::unique('type_logements', $standingCol)->ignore($typeLogementId)->whereNotNull($standingCol)
             ],
        ];
    }
     public function messages(): array
     {
         $libelleCol = 'libelle_logement';
         $standingCol = 'standing';
         return [
              $libelleCol.'.required' => 'Le libellé (français) ne peut pas être vide.',
              $libelleCol.'.unique' => 'Ce libellé est déjà utilisé.',
              $standingCol.'.unique' => 'Ce standing est déjà utilisé.',
               // ...
         ];
     }
}

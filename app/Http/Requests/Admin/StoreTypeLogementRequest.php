<?php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreTypeLogementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()?->isAdmin();
    }

    public function rules(): array
    {
        // Noms exacts des colonnes sources
        $libelleCol = 'libelle_logement';
        $standingCol = 'standing';
        return [
            $libelleCol => ['required', 'string', 'max:255', 'unique:type_logements,'.$libelleCol],
            $standingCol => ['nullable', 'string', 'max:255', 'unique:type_logements,'.$standingCol],
        ];
    }
     public function messages(): array
     {
          $libelleCol = 'libelle_logement';
          $standingCol = 'standing';
          return [
              $libelleCol.'.required' => 'Le libellé (français) est obligatoire.',
              $libelleCol.'.unique' => 'Ce libellé existe déjà.',
              $standingCol.'.unique' => 'Ce standing existe déjà.',
              // ...
          ];
     }
}

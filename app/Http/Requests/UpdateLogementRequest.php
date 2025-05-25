<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateLogementRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Vérifie si connecté + Bailleur. Le service vérifiera la propriété.
        return Auth::check() && Auth::user()->isBailleur();
    }

    public function rules(): array
    {
         // L'ID du logement est récupéré depuis la route pour la règle unique
         $logementId = $this->route('logement'); // 'logement' est le nom du paramètre dans apiResource

        return [
            // 'sometimes' -> valide seulement si présent
             'libelle' => ['sometimes', 'nullable', 'string', 'max:255'],
             // 'libelle_fr' => ['sometimes', 'required', 'string', 'max:255'], // Si traduction
             'reference' => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('logements', 'reference')->ignore($logementId)],
             'latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
             'longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
             'nbrpieces' => ['sometimes', 'integer', 'min:1'],
             'superficie' => ['sometimes', 'nullable', 'integer', 'min:1'],
             'nbr_salles_bain' => ['sometimes', 'nullable', 'integer', 'min:0'],
             'nbr_chambres' => ['sometimes', 'nullable', 'integer', 'min:0'],
             'climatisation' => ['sometimes', 'boolean'],
             'meuble' => ['sometimes', 'boolean'],
             'adresse' => ['sometimes', 'nullable', 'string', 'max:500'],
             'prix' => ['sometimes', 'numeric', 'min:0'],
             'nbrMois' => ['sometimes', 'integer', 'min:1'],
             'descrip_fr' => ['sometimes', 'string', 'max:65535'], // FR n'est pas requis en update
             'contPrep' => ['sometimes', 'boolean'],
             'forage' => ['sometimes', 'boolean'],
             'parking' => ['sometimes', 'boolean'],
             'gardien' => ['sometimes', 'boolean'],
             'dispo_fr' => ['sometimes', Rule::in(['LIBRE', 'OCCUPE'])],
             'typLogId' => ['sometimes', 'integer', 'exists:type_logements,id'],
             'quartierId' => ['sometimes', 'integer', 'exists:quartiers,id'],
        ];
    }
     // messages() peuvent être ajoutés si nécessaire
}

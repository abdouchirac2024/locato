<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreLogementRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Seul un Bailleur connecté peut créer
        return Auth::check() && Auth::user()->isBailleur();
    }

    public function rules(): array
    {
        // Valide les champs fournis dans la requête
        return [
            'libelle' => ['nullable', 'string', 'max:255'], // Champ FR si libelle_en n'est pas géré
            // 'libelle_fr' => ['required', 'string', 'max:255'], // Si vous aviez libelle_fr/en
            'reference' => ['nullable', 'string', 'max:255', 'unique:logements,reference'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'nbrpieces' => ['required', 'integer', 'min:1'],
            'superficie' => ['nullable', 'integer', 'min:1'],
            'nbr_salles_bain' => ['nullable', 'integer', 'min:0'],
            'nbr_chambres' => ['nullable', 'integer', 'min:0'],
            'climatisation' => ['sometimes', 'boolean'],
            'meuble' => ['sometimes', 'boolean'],
            'adresse' => ['nullable', 'string', 'max:500'],
            'prix' => ['required', 'numeric', 'min:0'],
            'nbrMois' => ['required', 'integer', 'min:1'],
            'descrip_fr' => ['required', 'string', 'max:65535'], // Description FR requise
            'contPrep' => ['sometimes', 'boolean'],
            'forage' => ['sometimes', 'boolean'],
            'parking' => ['sometimes', 'boolean'],
            'gardien' => ['sometimes', 'boolean'],
            'dispo_fr' => ['required', Rule::in(['LIBRE', 'OCCUPE'])], // Dispo FR requise
            'typLogId' => ['required', 'integer', 'exists:type_logements,id'],
            'quartierId' => ['required', 'integer', 'exists:quartiers,id'],

            // --- AJOUT DES CHAMPS ---
            'validation_status' => ['nullable', Rule::in(['en_attente', 'approuve', 'rejete'])],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
            // --- FIN AJOUT ---
        ];
    }

    public function messages(): array
    {
        return [
            'typLogId.required' => 'Le type de logement est obligatoire.',
            'typLogId.exists' => 'Le type de logement sélectionné est invalide.',
            'quartierId.required' => 'Le quartier est obligatoire.',
            'quartierId.exists' => 'Le quartier sélectionné est invalide.',
            'descrip_fr.required' => 'La description en français est obligatoire.',
            'dispo_fr.required' => 'La disponibilité en français est obligatoire.',
            // ... autres messages personnalisés ...
        ];
    }

    protected function prepareForValidation()
    {
        $booleans = ['contPrep', 'forage', 'parking', 'gardien', 'climatisation', 'meuble'];
        foreach ($booleans as $field) {
            if ($this->has($field)) {
                $this->merge([
                    $field => filter_var($this->input($field), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
                ]);
            }
        }
    }
}

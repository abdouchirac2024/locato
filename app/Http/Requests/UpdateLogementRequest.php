<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
// Vous pourriez avoir besoin d'une Policy ici pour une autorisation plus fine
// use App\Policies\LogementPolicy;

class UpdateLogementRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette requête.
     * Vérifie si l'utilisateur est connecté. L'autorisation de modifier
     * *ce logement spécifique* sera faite dans le LogementService.
     * Pour une meilleure approche, utilisez une Policy.
     */
    public function authorize(): bool
    {
        // Option 1: Simple vérification de connexion (le service gère la propriété)
         return Auth::check() && Auth::user()->isBailleur();

        // Option 2: Utilisation d'une Policy (meilleure pratique)
        // Assurez-vous d'avoir créé LogementPolicy et enregistré dans AuthServiceProvider
        // $logement = $this->route('logement'); // Récupère le modèle Logement depuis la route model binding
        // return $this->user()->can('update', $logement);
    }

    /**
     * Obtenir les règles de validation qui s'appliquent à la requête de mise à jour.
     */
    public function rules(): array
    {
        // 'sometimes' = la règle s'applique seulement si le champ est présent dans la requête.
        // 'nullable' = le champ peut être présent mais vide/null.
        return [
            'libelle' => ['sometimes', 'nullable', 'string', 'max:255'],
            'nbrMois' => ['sometimes', 'integer', 'min:0'],
            'prix' => ['sometimes', 'numeric', 'min:0'],
            'nbrpieces' => ['sometimes', 'integer', 'min:1'],
            'descrip_fr' => ['sometimes', 'nullable', 'string', 'max:65535'],
            'contPrep' => ['sometimes', 'boolean'],
            'forage' => ['sometimes', 'boolean'],
            'parking' => ['sometimes', 'boolean'],
            'gardien' => ['sometimes', 'boolean'],
            'dispo_fr' => ['sometimes', Rule::in(['LIBRE', 'OCCUPE'])],
            'typLogId' => ['sometimes', 'integer', 'exists:type_logements,id'],
            'quartierId' => ['sometimes', 'integer', 'exists:quartiers,id'],
             // Validation pour les images si elles sont mises à jour
            // 'images' => ['sometimes', 'nullable', 'array', 'max:5'],
            // 'images.*' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048']
        ];
    }

    /**
     * Messages d'erreur personnalisés.
     */
     public function messages(): array
     {
         // Mêmes messages que pour StoreLogementRequest peuvent s'appliquer
         return [
            'nbrMois.min' => 'Le nombre de mois d\'avance doit être au moins :min.',
            'nbrpieces.min' => 'Il doit y avoir au moins :min pièce.',
            'dispo_fr.in' => 'La disponibilité doit être soit LIBRE, soit OCCUPE.',
            'typLogId.exists' => 'Le type de logement sélectionné n\'est pas valide.',
            'quartierId.exists' => 'Le quartier sélectionné n\'est pas valide.',
            // ...
        ];
     }

    /**
     * Prépare les données pour la validation.
     */
    protected function prepareForValidation()
    {
        $booleans = ['contPrep', 'forage', 'parking', 'gardien'];
        foreach ($booleans as $field) {
            if ($this->has($field)) {
                $this->merge([
                    $field => filter_var($this->input($field), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
                ]);
            }
        }
    }
}

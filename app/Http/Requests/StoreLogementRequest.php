<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth; // Pour vérifier le rôle dans authorize

class StoreLogementRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur authentifié est autorisé à faire cette requête.
     * Ici, on vérifie s'il est connecté ET s'il a le rôle Bailleur et est vérifié.
     */
    public function authorize(): bool
    {
        // Vérifie si l'utilisateur est connecté, est un bailleur et est vérifié
        return Auth::check() && 
               Auth::user()->isBailleur() && 
               Auth::user()->bailleur->verif && 
               Auth::user()->bailleur->statut_fr === 'verifie';
    }

    /**
     * Obtenir les règles de validation qui s'appliquent à la requête.
     */
    public function rules(): array
    {
        // bailId sera ajouté par le service, pas besoin ici.
        return [
            'libelle' => ['nullable', 'string', 'max:255'],
            'nbrMois' => ['required', 'integer', 'min:0'], // 0 mois possible ? Sinon min:1
            'prix' => ['required', 'numeric', 'min:0'],
            'nbrpieces' => ['required', 'integer', 'min:1'],
            'descrip_fr' => ['required', 'string', 'max:65535'], // Limite TEXT mysql
            'contPrep' => ['sometimes', 'boolean'], // sometimes = optionnel, mais si présent doit être boolean
            'forage' => ['sometimes', 'boolean'],
            'parking' => ['sometimes', 'boolean'],
            'gardien' => ['sometimes', 'boolean'],
            'dispo_fr' => ['required', Rule::in(['LIBRE', 'OCCUPE'])], // Valeurs autorisées
            'typLogId' => ['required', 'integer', 'exists:type_logements,id'], // Doit exister dans la table type_logements
            'quartierId' => ['required', 'integer', 'exists:quartiers,id'],     // Doit exister dans la table quartiers
            // Ajoutez ici la validation pour les images si vous les uploadez en même temps
            // 'images' => ['nullable', 'array', 'max:5'], // Ex: max 5 images
            // 'images.*' => ['image', 'mimes:jpeg,png,jpg,gif', 'max:2048'] // Valide chaque image dans le tableau
        ];
    }

    /**
     * Messages d'erreur personnalisés pour les règles de validation.
     */
    public function messages(): array
    {
        return [
            'nbrMois.required' => 'Le nombre de mois d\'avance est requis.',
            'nbrMois.min' => 'Le nombre de mois d\'avance doit être au moins :min.',
            'prix.required' => 'Le prix du loyer est requis.',
            'prix.numeric' => 'Le prix doit être un nombre.',
            'nbrpieces.required' => 'Le nombre de pièces est requis.',
            'nbrpieces.min' => 'Il doit y avoir au moins :min pièce.',
            'descrip_fr.required' => 'La description est obligatoire.',
            'dispo_fr.required' => 'La disponibilité est obligatoire.',
            'dispo_fr.in' => 'La disponibilité doit être soit LIBRE, soit OCCUPE.',
            'typLogId.required' => 'Le type de logement est obligatoire.',
            'typLogId.exists' => 'Le type de logement sélectionné n\'est pas valide.',
            'quartierId.required' => 'Le quartier est obligatoire.',
            'quartierId.exists' => 'Le quartier sélectionné n\'est pas valide.',
            // 'images.*.image' => 'Chaque fichier doit être une image.',
            // 'images.*.mimes' => 'Formats d\'image acceptés : jpeg, png, jpg, gif.',
            // 'images.*.max' => 'Chaque image ne doit pas dépasser 2 Mo.',
        ];
    }

     /**
     * Prépare les données pour la validation.
     * Utile pour convertir des valeurs avant validation (ex: 'true' -> true)
     */
    protected function prepareForValidation()
    {
        // Convertit les champs booléens potentiels venant de formulaires web/mobile
        // (qui envoient souvent des strings 'true'/'false'/'1'/'0') en vrais booléens.
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

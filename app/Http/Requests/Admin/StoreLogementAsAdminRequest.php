<?php
namespace App\Http\Requests\Admin; // Namespace spécifique Admin

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreLogementAsAdminRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur (Admin) est autorisé à faire cette requête.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()?->isAdmin();
    }

    /**
     * Règles de validation pour la création de logement par un Admin.
     */
    public function rules(): array
    {
        // Combine les règles de StoreLogementRequest et ajoute bailId
        return [
            // Champs du logement (similaires à StoreLogementRequest)
            'libelle' => ['nullable', 'string', 'max:255'],
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
            'descrip_fr' => ['required', 'string', 'max:65535'],
            'contPrep' => ['sometimes', 'boolean'],
            'forage' => ['sometimes', 'boolean'],
            'parking' => ['sometimes', 'boolean'],
            'gardien' => ['sometimes', 'boolean'],
            'dispo_fr' => ['required', Rule::in(['LIBRE', 'OCCUPE'])],
            'typLogId' => ['required', 'integer', 'exists:type_logements,id'],
            'quartierId' => ['required', 'integer', 'exists:quartiers,id'],

            // --- AJOUT SPÉCIFIQUE ADMIN : ID du Bailleur ---
            'bailId' => ['required', 'integer', 'exists:bailleurs,id'], // L'admin DOIT spécifier un bailleur valide

            // Validation des images (identique)
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'], // 5MB max
        ];
    }

     public function messages(): array
     {
         // Fusionne les messages existants si besoin et ajoute celui pour bailId
         return array_merge(parent::messages(), [
             'bailId.required' => 'L\'ID du bailleur propriétaire est obligatoire.',
             'bailId.exists' => 'Le bailleur sélectionné n\'existe pas.',
             // ... ajouter/modifier messages pour images si nécessaire ...
             'images.array' => 'Le champ images doit être une liste.',
             'images.max' => 'Vous ne pouvez pas télécharger plus de :max images.',
             'images.*.image' => 'Chaque fichier doit être une image valide.',
             'images.*.mimes' => 'Formats d\'image acceptés : :values.',
             'images.*.max' => 'Chaque image ne doit pas dépasser :max kilobytes (5MB).',
         ]);
     }

      protected function prepareForValidation()
      {
          // Garder la conversion des booléens
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

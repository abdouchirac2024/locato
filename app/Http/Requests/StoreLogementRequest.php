<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
<<<<<<< HEAD
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth; // Pour vérifier le rôle dans authorize

class StoreLogementRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur authentifié est autorisé à faire cette requête.
     * Ici, on vérifie s'il est connecté ET s'il a le rôle Bailleur.
     */
    public function authorize(): bool
    {
        // Renvoie true si l'utilisateur est connecté et est un bailleur
        return Auth::check() && Auth::user()->isBailleur();
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
=======
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreLogementRequest extends FormRequest
{
    // ===== VERSION develop =====
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
    // ===== VERSION abdou =====
    /*
    public function authorize(): bool
    {
        // Seul un Bailleur connecté peut créer
        return Auth::check() && Auth::user()->isBailleur();
    }
    */

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
>>>>>>> 172c567c215f6076d8e6aab0684aa69c300f9117
        foreach ($booleans as $field) {
            if ($this->has($field)) {
                $this->merge([
                    $field => filter_var($this->input($field), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
                ]);
            }
        }
    }
}

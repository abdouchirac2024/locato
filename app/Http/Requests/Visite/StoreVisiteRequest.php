<?php
namespace App\Http\Requests\Visite; // Namespace pour les requêtes de Visite

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Logement; // Pour vérifier si le logement existe et est approuvé
use App\Models\Visite; // Pour les constantes de statut
use Illuminate\Validation\Rule;

class StoreVisiteRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur (Locataire) est autorisé à faire cette requête.
     */
    public function authorize(): bool
    {
        // Seul un locataire connecté peut demander une visite
        return Auth::check() && Auth::user()?->isLocataire();
    }

    /**
     * Obtenir les règles de validation qui s'appliquent à la requête.
     */
    public function rules(): array
    {
        return [
            'logId' => [
                'required',
                'integer',
                // S'assurer que le logement existe et est approuvé
                Rule::exists('logements', 'id')->where(function ($query) {
                    $query->where('validation_status', Logement::STATUS_APPROUVE)
                          ->whereNull('deleted_at'); // Et non soft-deleted
                }),
            ],
            'dateVisite' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'heureVisite' => ['required', 'date_format:H:i'], // Format HH:MM
            'commentaire_locataire' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'logId.required' => 'L\'identifiant du logement est obligatoire.',
            'logId.exists' => 'Le logement spécifié n\'existe pas ou n\'est pas disponible pour des visites.',
            'dateVisite.required' => 'La date de visite est obligatoire.',
            'dateVisite.date_format' => 'La date de visite doit être au format AAAA-MM-JJ.',
            'dateVisite.after_or_equal' => 'La date de visite ne peut pas être dans le passé.',
            'heureVisite.required' => 'L\'heure de visite est obligatoire.',
            'heureVisite.date_format' => 'L\'heure de visite doit être au format HH:MM (ex: 14:30).',
            'commentaire_locataire.max' => 'Le commentaire ne doit pas dépasser 1000 caractères.',
        ];
    }
}
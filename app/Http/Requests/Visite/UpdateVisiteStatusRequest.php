<?php
namespace App\Http\Requests\Visite;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Visite; // Pour les constantes de statut

class UpdateVisiteStatusRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur (Bailleur) est autorisé à faire cette requête.
     * La vérification de propriété de la visite (via le logement) se fait dans le service.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()?->isBailleur();
    }

    /**
     * Obtenir les règles de validation.
     */
    public function rules(): array
    {
        $newStatusFr = $this->input('new_status_fr');

        return [
            'new_status_fr' => [
                'required',
                'string',
                Rule::in([ // Statuts que le bailleur peut définir
                    Visite::STATUT_PROGRAMMEE,
                    Visite::STATUT_ANNULEE_BAILLEUR,
                    Visite::STATUT_REPORTEE_BAILLEUR,
                    Visite::STATUT_EFFECTUEE,
                    Visite::STATUT_CONTRE_PROPOSITION,
                ]),
            ],
            'commentaire_bailleur' => ['nullable', 'string', 'max:1000'],
            'date_proposee_bailleur' => [
                Rule::requiredIf(fn() => $newStatusFr === Visite::STATUT_REPORTEE_BAILLEUR || $newStatusFr === Visite::STATUT_CONTRE_PROPOSITION),
                'nullable', // Permet de ne pas l'envoyer si pas reporté/contre-proposition
                'date_format:Y-m-d',
                'after_or_equal:today'
            ],
            'heure_proposee_bailleur' => [
                Rule::requiredIf(fn() => $newStatusFr === Visite::STATUT_REPORTEE_BAILLEUR || $newStatusFr === Visite::STATUT_CONTRE_PROPOSITION),
                'nullable',
                'date_format:H:i'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'new_status_fr.required' => 'Le nouveau statut est obligatoire.',
            'new_status_fr.in' => 'Le statut fourni n\'est pas valide.',
            'commentaire_bailleur.max' => 'Le commentaire ne doit pas dépasser 1000 caractères.',
            'date_proposee_bailleur.required_if' => 'Une date proposée est requise pour ce statut.',
            'date_proposee_bailleur.date_format' => 'La date proposée doit être au format AAAA-MM-JJ.',
            'date_proposee_bailleur.after_or_equal' => 'La date proposée ne peut pas être dans le passé.',
            'heure_proposee_bailleur.required_if' => 'Une heure proposée est requise pour ce statut.',
            'heure_proposee_bailleur.date_format' => 'L\'heure proposée doit être au format HH:MM.',
        ];
    }
}
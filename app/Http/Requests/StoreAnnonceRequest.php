<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnnonceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
        'titre_fr' => ['required', 'string', 'max:255'],
        'titre_en' => ['nullable', 'string', 'max:255'],
        'contenu_fr' => ['nullable', 'string', 'max:255'],
        'contenu_en' =>['nullable', 'string', 'max:255'],
        'date_publication' =>['nullable', 'date'],
//        'date_expiration'  =>['nullable', 'string', 'max:255'],
        'is_active' =>['nullable', 'boolean'],
        'logId' =>['required', 'integer'],
        'bailId' =>['required', 'integer'],
        'create_by'=> ['nullable', 'string', 'max:255'],
        ];
    }
}

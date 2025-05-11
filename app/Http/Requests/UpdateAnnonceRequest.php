<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAnnonceRequest extends FormRequest
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
            'titre_fr' => ['sometimes', 'string', 'max:255'],
            'titre_en' => ['sometimes', 'string', 'max:255'],
            'contenu_fr' => ['sometimes', 'string', 'max:255'],
            'contenu_en' =>['sometimes', 'string', 'max:255'],
            'date_publication' =>['sometimes', 'date'],
    //        'date_expiration'  =>['sometimes', 'string', 'max:255'],
            'is_active' =>['sometimes', 'boolean'],
            'logId' =>['sometimes', 'integer'],
            'bailId' =>['sometimes', 'integer'],
            'create_by'=>['sometimes', 'string'],
            ];
    }
}

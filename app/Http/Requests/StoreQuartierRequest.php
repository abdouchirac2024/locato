<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuartierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nomQuartier' => 'required|string|max:255',
            'villeId' => 'required|exists:villes,id',
        ];
    }
}

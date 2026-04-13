<?php

namespace App\Http\Requests\Joueur;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RepondreConvocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'statut' => ['required', Rule::in(['confirme', 'refuse', 'en_attente'])],
        ];
    }
}

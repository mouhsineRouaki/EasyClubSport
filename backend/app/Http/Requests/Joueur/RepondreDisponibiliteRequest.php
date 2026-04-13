<?php

namespace App\Http\Requests\Joueur;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RepondreDisponibiliteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reponse' => ['required', Rule::in(['present', 'absent', 'incertain'])],
            'commentaire' => ['nullable', 'string'],
        ];
    }
}

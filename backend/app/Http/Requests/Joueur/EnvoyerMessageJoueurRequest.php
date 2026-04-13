<?php

namespace App\Http\Requests\Joueur;

use Illuminate\Foundation\Http\FormRequest;

class EnvoyerMessageJoueurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contenu' => ['required', 'string'],
        ];
    }
}

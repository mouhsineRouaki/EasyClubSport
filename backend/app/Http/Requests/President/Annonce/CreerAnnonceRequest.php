<?php

namespace App\Http\Requests\President\Annonce;

use Illuminate\Foundation\Http\FormRequest;

class CreerAnnonceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titre' => ['required', 'string', 'max:255'],
            'contenu' => ['required', 'string'],
            'est_active' => ['nullable', 'boolean'],
        ];
    }
}

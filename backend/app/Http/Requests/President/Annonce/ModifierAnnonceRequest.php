<?php

namespace App\Http\Requests\President\Annonce;

use Illuminate\Foundation\Http\FormRequest;

class ModifierAnnonceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titre' => ['sometimes', 'required', 'string', 'max:255'],
            'contenu' => ['sometimes', 'required', 'string'],
            'image' => ['sometimes', 'nullable', 'image', 'max:5120'],
            'est_active' => ['sometimes', 'boolean'],
        ];
    }
}

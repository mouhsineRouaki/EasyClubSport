<?php

namespace App\Http\Requests\President\Equipe;

use Illuminate\Foundation\Http\FormRequest;

class ModifierEquipeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:255'],
            'categorie' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'statut' => ['nullable', 'in:active,inactive'],
            'description' => ['nullable', 'string'],
        ];
    }
}


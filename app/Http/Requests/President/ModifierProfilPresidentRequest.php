<?php

namespace App\Http\Requests\President;

use Illuminate\Foundation\Http\FormRequest;

class ModifierProfilPresidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $utilisateur = $this->user();

        return [
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:20'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$utilisateur?->id],
        ];
    }
}

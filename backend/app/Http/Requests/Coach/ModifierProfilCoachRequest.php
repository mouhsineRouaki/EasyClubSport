<?php

namespace App\Http\Requests\Coach;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ModifierProfilCoachRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => ['nullable', 'string', 'max:255'],
            'prenom' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user()?->id)],
            'telephone' => ['nullable', 'string', 'max:30'],
            'adresse' => ['nullable', 'string', 'max:500'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'statut' => ['nullable', Rule::in(['actif', 'blesse', 'suspendu', 'inactif'])],
        ];
    }
}
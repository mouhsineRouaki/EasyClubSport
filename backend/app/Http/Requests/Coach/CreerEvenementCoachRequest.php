<?php

namespace App\Http\Requests\Coach;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreerEvenementCoachRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titre' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['match', 'entrainement', 'reunion'])],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
            'lieu' => ['nullable', 'string', 'max:255'],
            'adversaire' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'statut' => ['nullable', Rule::in(['planifie', 'termine', 'annule'])],
        ];
    }
}
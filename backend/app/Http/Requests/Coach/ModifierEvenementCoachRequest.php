<?php

namespace App\Http\Requests\Coach;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ModifierEvenementCoachRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titre' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', Rule::in(['match', 'entrainement', 'reunion'])],
            'date_debut' => ['sometimes', 'date'],
            'date_fin' => ['nullable', 'date'],
            'lieu' => ['nullable', 'string', 'max:255'],
            'adversaire' => ['nullable', 'string', 'max:255'],
            'adversaire_equipe_id' => [
                'sometimes',
                'nullable',
                'required_if:type,match',
                'integer',
                Rule::notIn([(int) ($this->route('equipe')?->id ?? 0)]),
                Rule::exists('equipes', 'id'),
            ],
            'description' => ['nullable', 'string'],
            'statut' => ['nullable', Rule::in(['planifie', 'termine', 'annule'])],
        ];
    }
}

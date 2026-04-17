<?php

namespace App\Http\Requests\President\Evenement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ModifierEvenementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titre' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['sometimes', 'required', Rule::in(['match', 'entrainement', 'reunion'])],
            'date_debut' => ['sometimes', 'required', 'date'],
            'date_fin' => ['sometimes', 'nullable', 'date', 'after_or_equal:date_debut'],
            'lieu' => ['sometimes', 'nullable', 'string', 'max:255'],
            'adversaire' => ['sometimes', 'nullable', 'string', 'max:255'],
            'adversaire_equipe_id' => [
                'sometimes',
                'nullable',
                'required_if:type,match',
                'integer',
                Rule::notIn([(int) ($this->route('equipe')?->id ?? 0)]),
                Rule::exists('equipes', 'id'),
            ],
            'description' => ['sometimes', 'nullable', 'string'],
            'statut' => ['sometimes', 'required', Rule::in(['planifie', 'termine', 'annule'])],
        ];
    }
}


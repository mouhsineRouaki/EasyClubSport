<?php

namespace App\Http\Requests\Coach;

use Illuminate\Foundation\Http\FormRequest;

class EnregistrerStatistiquesMatchCoachRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'joueurs' => ['required', 'array'],
            'joueurs.*.utilisateur_id' => ['required', 'integer', 'exists:users,id'],
            'joueurs.*.buts' => ['nullable', 'integer', 'min:0'],
            'joueurs.*.passes_decisives' => ['nullable', 'integer', 'min:0'],
            'joueurs.*.cartons_jaunes' => ['nullable', 'integer', 'min:0'],
            'joueurs.*.cartons_rouges' => ['nullable', 'integer', 'min:0'],
            'joueurs.*.minutes_jouees' => ['nullable', 'integer', 'min:0'],
        ];
    }
}

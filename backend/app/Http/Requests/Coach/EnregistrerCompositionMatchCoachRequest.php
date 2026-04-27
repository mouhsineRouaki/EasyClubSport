<?php

namespace App\Http\Requests\Coach;

use Illuminate\Foundation\Http\FormRequest;

class EnregistrerCompositionMatchCoachRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'formation' => ['nullable', 'string', 'max:60'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'est_validee' => ['nullable', 'boolean'],
            'titulaires' => ['nullable', 'array'],
            'titulaires.*.utilisateur_id' => ['required', 'integer', 'exists:users,id'],
            'titulaires.*.position_joueur' => ['nullable', 'string', 'max:80'],
            'remplacants' => ['nullable', 'array'],
            'remplacants.*.utilisateur_id' => ['required', 'integer', 'exists:users,id'],
            'remplacants.*.position_joueur' => ['nullable', 'string', 'max:80'],
        ];
    }
}

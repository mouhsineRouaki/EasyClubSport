<?php

namespace App\Http\Requests\Coach;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreerConvocationCoachRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'utilisateur_ids' => ['required', 'array', 'min:1'],
            'utilisateur_ids.*' => ['integer', 'exists:users,id'],
            'statut' => ['nullable', Rule::in(['convoque', 'confirme', 'refuse', 'en_attente'])],
        ];
    }
}
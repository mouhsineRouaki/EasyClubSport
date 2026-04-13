<?php

namespace App\Http\Requests\Coach;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ModifierConvocationCoachRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'statut' => ['required', Rule::in(['convoque', 'confirme', 'refuse', 'en_attente'])],
        ];
    }
}
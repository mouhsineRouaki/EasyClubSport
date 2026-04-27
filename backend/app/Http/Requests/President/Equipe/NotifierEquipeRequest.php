<?php

namespace App\Http\Requests\President\Equipe;

use Illuminate\Foundation\Http\FormRequest;

class NotifierEquipeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'utilisateur_ids' => ['required', 'array', 'min:1'],
            'utilisateur_ids.*' => ['required', 'integer', 'exists:users,id'],
            'message' => ['nullable', 'string', 'max:1000'],
        ];
    }
}

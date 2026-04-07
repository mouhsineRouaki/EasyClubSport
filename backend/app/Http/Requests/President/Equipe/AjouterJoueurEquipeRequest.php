<?php

namespace App\Http\Requests\President\Equipe;

use Illuminate\Foundation\Http\FormRequest;

class AjouterJoueurEquipeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'utilisateur_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}


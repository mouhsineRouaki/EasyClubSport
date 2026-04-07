<?php

namespace App\Http\Requests\President\Equipe;

use Illuminate\Foundation\Http\FormRequest;

class AssignerCoachEquipeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'coach_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}


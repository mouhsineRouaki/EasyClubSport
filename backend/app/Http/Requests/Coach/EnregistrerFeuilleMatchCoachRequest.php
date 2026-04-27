<?php

namespace App\Http\Requests\Coach;

use Illuminate\Foundation\Http\FormRequest;

class EnregistrerFeuilleMatchCoachRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'score_equipe' => ['nullable', 'integer', 'min:0'],
            'score_adversaire' => ['nullable', 'integer', 'min:0'],
            'resume_match' => ['nullable', 'string'],
        ];
    }
}

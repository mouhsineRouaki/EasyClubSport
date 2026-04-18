<?php

namespace App\Http\Requests\Joueur;

use Illuminate\Foundation\Http\FormRequest;

class RejoindreEquipeJoueurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code_invitation' => ['required', 'string', 'min:4', 'max:20'],
        ];
    }
}

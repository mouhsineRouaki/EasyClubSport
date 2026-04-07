<?php

namespace App\Http\Requests\President\Messagerie;

use Illuminate\Foundation\Http\FormRequest;

class CreerCanalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type_canal' => ['nullable', 'in:equipe'],
        ];
    }
}

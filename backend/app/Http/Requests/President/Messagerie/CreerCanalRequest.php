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
            'nom' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'description' => ['nullable', 'string'],
            'type_canal' => ['nullable', 'in:equipe,prive'],
            'utilisateur_ids' => ['nullable', 'array'],
            'utilisateur_ids.*' => ['integer', 'exists:users,id'],
        ];
    }
}

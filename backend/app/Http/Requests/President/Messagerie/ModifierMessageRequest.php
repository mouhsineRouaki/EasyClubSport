<?php

namespace App\Http\Requests\President\Messagerie;

use Illuminate\Foundation\Http\FormRequest;

class ModifierMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contenu' => ['required', 'string'],
        ];
    }
}

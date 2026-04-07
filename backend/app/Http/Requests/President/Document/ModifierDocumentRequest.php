<?php

namespace App\Http\Requests\President\Document;

use Illuminate\Foundation\Http\FormRequest;

class ModifierDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => ['sometimes', 'required', 'string', 'max:255'],
            'type_document' => ['sometimes', 'required', 'string', 'max:255'],
            'fichier' => ['sometimes', 'file', 'max:10240'],
        ];
    }
}

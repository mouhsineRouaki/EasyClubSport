<?php

namespace App\Http\Requests\President\Document;

use Illuminate\Foundation\Http\FormRequest;

class CreerDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'utilisateur_id' => ['required', 'integer', 'exists:users,id'],
            'nom' => ['required', 'string', 'max:255'],
            'type_document' => ['required', 'string', 'max:255'],
            'fichier' => ['required', 'file', 'max:10240'],
        ];
    }
}

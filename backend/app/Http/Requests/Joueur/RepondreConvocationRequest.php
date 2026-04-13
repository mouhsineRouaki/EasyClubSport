<?php

namespace App\Http\Requests\Joueur;

use Illuminate\Foundation\Http\FormRequest;

class RepondreConvocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}

<?php

namespace App\Http\Resources\Common;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiErrorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'status' => false,
            'message' => $this['message'] ?? 'Une erreur est survenue.',
            'data' => $this['data'] ?? null,
        ];
    }
}

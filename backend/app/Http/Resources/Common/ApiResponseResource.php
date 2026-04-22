<?php

namespace App\Http\Resources\Common;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiResponseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'status' => $this['status'] ?? true,
            'message' => $this['message'] ?? null,
            'data' => $this['data'] ?? null,
        ];
    }
}

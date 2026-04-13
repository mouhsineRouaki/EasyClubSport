<?php

namespace App\Http\Resources\Coach;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MessageCoachCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [];
    }
}
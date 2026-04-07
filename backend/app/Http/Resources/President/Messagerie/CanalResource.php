<?php

namespace App\Http\Resources\President\Messagerie;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CanalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $canal = $this->resource['canal'];

        return [
            'status' => true,
            'message' => $this->resource['message'],
            'data' => [
                'canal' => [
                    'id' => $canal->id,
                    'equipe_id' => $canal->equipe_id,
                    'nom' => $canal->nom,
                    'type_canal' => $canal->type_canal,
                    'description' => $canal->description,
                    'equipe' => $canal->equipe ? [
                        'id' => $canal->equipe->id,
                        'nom' => $canal->equipe->nom,
                    ] : null,
                    'club' => $canal->equipe?->club ? [
                        'id' => $canal->equipe->club->id,
                        'nom' => $canal->equipe->club->nom,
                    ] : null,
                    'participants_total' => $canal->relationLoaded('utilisateurs') ? $canal->utilisateurs->count() : null,
                    'created_at' => $canal->created_at,
                    'updated_at' => $canal->updated_at,
                ],
            ],
        ];
    }
}

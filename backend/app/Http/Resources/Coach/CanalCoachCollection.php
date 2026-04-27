<?php

namespace App\Http\Resources\Coach;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CanalCoachCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'status' => true,
            'message' => 'Liste des canaux du coach recuperee avec succes.',
            'data' => [
                'canaux' => $this->collection->map(function ($canal) {
                    return [
                        'id' => $canal->id,
                        'nom' => $canal->nom,
                        'image' => $canal->image,
                        'image_url' => $canal->image ? asset('storage/'.$canal->image) : null,
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
                    ];
                })->values(),
            ],
        ];
    }
}

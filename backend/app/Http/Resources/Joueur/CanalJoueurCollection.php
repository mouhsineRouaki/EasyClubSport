<?php

namespace App\Http\Resources\Joueur;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CanalJoueurCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'status' => true,
            'message' => 'Liste des canaux du joueur recuperee avec succes.',
            'data' => [
                'canaux' => $this->collection->map(function ($canal) {
                    return [
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
                    ];
                })->values(),
            ],
        ];
    }
}

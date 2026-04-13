<?php

namespace App\Http\Resources\Coach;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EquipeCoachCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'status' => true,
            'message' => 'Liste des equipes du coach recuperee avec succes.',
            'data' => [
                'equipes' => $this->collection->map(function ($equipe) {
                    return [
                        'id' => $equipe->id,
                        'nom' => $equipe->nom,
                        'categorie' => $equipe->categorie,
                        'logo' => $equipe->logo,
                        'logo_url' => $equipe->logo ? asset('storage/'.$equipe->logo) : null,
                        'statut' => $equipe->statut,
                        'description' => $equipe->description,
                        'joueurs_total' => $equipe->joueurs_total ?? 0,
                        'club' => $equipe->club ? [
                            'id' => $equipe->club->id,
                            'nom' => $equipe->club->nom,
                            'ville' => $equipe->club->ville,
                        ] : null,
                    ];
                })->values(),
            ],
        ];
    }
}
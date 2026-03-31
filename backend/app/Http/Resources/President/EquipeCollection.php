<?php

namespace App\Http\Resources\President;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EquipeCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'status' => true,
            'message' => 'Liste des equipes recuperee avec succes.',
            'data' => [
                'equipes' => $this->collection->map(function ($equipe) {
                    return [
                        'id' => $equipe->id,
                        'club_id' => $equipe->club_id,
                        'coach_id' => $equipe->coach_id,
                        'coach' => $equipe->coach ? [
                            'id' => $equipe->coach->id,
                            'name' => $equipe->coach->name,
                            'nom' => $equipe->coach->nom,
                            'prenom' => $equipe->coach->prenom,
                            'email' => $equipe->coach->email,
                        ] : null,
                        'nom' => $equipe->nom,
                        'categorie' => $equipe->categorie,
                        'logo' => $equipe->logo,
                        'logo_url' => $equipe->logo ? asset('storage/'.$equipe->logo) : null,
                        'code_invitation' => $equipe->code_invitation,
                        'statut' => $equipe->statut,
                        'description' => $equipe->description,
                        'created_at' => $equipe->created_at,
                        'updated_at' => $equipe->updated_at,
                    ];
                })->values(),
            ],
        ];
    }
}

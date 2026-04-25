<?php

namespace App\Http\Resources\President\Equipe;

use App\Http\Resources\Concerns\WithPaginationMeta;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class JoueurDisponibleCollection extends ResourceCollection
{
    use WithPaginationMeta;

    public function toArray(Request $request): array
    {
        return [
            'status' => true,
            'message' => 'Liste des joueurs disponibles recuperee avec succes.',
            'data' => [
                'joueurs' => $this->collection->map(function ($joueur) {
                    return [
                        'id' => $joueur->id,
                        'name' => $joueur->name,
                        'nom' => $joueur->nom,
                        'prenom' => $joueur->prenom,
                        'email' => $joueur->email,
                        'telephone' => $joueur->telephone,
                        'adresse' => $joueur->adresse,
                        'photo' => $joueur->photo,
                        'photo_url' => $joueur->photo ? asset('storage/'.$joueur->photo) : null,
                        'role' => $joueur->role,
                        'statut' => $joueur->statut,
                    ];
                })->values(),
                'pagination' => $this->paginationMeta(),
            ],
        ];
    }
}

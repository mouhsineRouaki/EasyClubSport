<?php

namespace App\Http\Resources\Joueur;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EquipeJoueurResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $equipe = $this['equipe'];

        return [
            'status' => true,
            'message' => $this['message'],
            'data' => [
                'equipe' => $equipe ? [
                    'id' => $equipe->id,
                    'nom' => $equipe->nom,
                    'categorie' => $equipe->categorie,
                    'logo' => $equipe->logo,
                    'logo_url' => $equipe->logo ? asset('storage/'.$equipe->logo) : null,
                    'statut' => $equipe->statut,
                    'description' => $equipe->description,
                    'club' => $equipe->club ? [
                        'id' => $equipe->club->id,
                        'nom' => $equipe->club->nom,
                        'ville' => $equipe->club->ville,
                    ] : null,
                    'coach' => $equipe->coach ? [
                        'id' => $equipe->coach->id,
                        'nom' => trim(($equipe->coach->prenom ?? '').' '.($equipe->coach->nom ?? '')),
                        'email' => $equipe->coach->email,
                    ] : null,
                ] : null,
            ],
        ];
    }
}

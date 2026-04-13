<?php

namespace App\Http\Resources\Joueur;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfilJoueurResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $utilisateur = $this['utilisateur'];
        $equipe = $this['equipe'];

        return [
            'status' => true,
            'message' => $this['message'],
            'data' => [
                'utilisateur' => [
                    'id' => $utilisateur->id,
                    'name' => $utilisateur->name,
                    'nom' => $utilisateur->nom,
                    'prenom' => $utilisateur->prenom,
                    'email' => $utilisateur->email,
                    'telephone' => $utilisateur->telephone,
                    'adresse' => $utilisateur->adresse,
                    'photo' => $utilisateur->photo,
                    'photo_url' => $utilisateur->photo ? asset('storage/'.$utilisateur->photo) : null,
                    'role' => $utilisateur->role,
                    'statut' => $utilisateur->statut,
                ],
                'equipe' => $equipe ? [
                    'id' => $equipe->id,
                    'nom' => $equipe->nom,
                    'categorie' => $equipe->categorie,
                    'logo' => $equipe->logo,
                    'logo_url' => $equipe->logo ? asset('storage/'.$equipe->logo) : null,
                    'club' => $equipe->club ? [
                        'id' => $equipe->club->id,
                        'nom' => $equipe->club->nom,
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

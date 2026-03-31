<?php

namespace App\Http\Resources\President;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfilPresidentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $utilisateur = $this->resource['utilisateur'];

        return [
            'status' => true,
            'message' => $this->resource['message'],
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
            ],
        ];
    }
}

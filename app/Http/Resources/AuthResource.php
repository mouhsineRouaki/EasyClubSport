<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'status' => true,
            'message' => $this['message'],
            'data' => [
                'token' => $this['token'],
                'utilisateur' => [
                    'id' => $this['utilisateur']->id,
                    'name' => $this['utilisateur']->name,
                    'nom' => $this['utilisateur']->nom,
                    'prenom' => $this['utilisateur']->prenom,
                    'email' => $this['utilisateur']->email,
                    'telephone' => $this['utilisateur']->telephone,
                    'adresse' => $this['utilisateur']->adresse,
                    'role' => $this['utilisateur']->role,
                    'statut' => $this['utilisateur']->statut,
                ],
            ],
        ];
    }
}

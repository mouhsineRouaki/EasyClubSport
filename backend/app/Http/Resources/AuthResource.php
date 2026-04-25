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
                    'photo' => $this['utilisateur']->photo,
                    'photo_url' => $this['utilisateur']->photo ? asset('storage/'.$this['utilisateur']->photo) : null,
                    'numero_joueur' => $this['utilisateur']->numero_joueur,
                    'poste_principal' => $this['utilisateur']->poste_principal,
                    'poste_secondaire' => $this['utilisateur']->poste_secondaire,
                    'pied_fort' => $this['utilisateur']->pied_fort,
                    'note_globale' => $this['utilisateur']->note_globale,
                    'attaque' => $this['utilisateur']->attaque,
                    'defense' => $this['utilisateur']->defense,
                    'vitesse' => $this['utilisateur']->vitesse,
                    'passe' => $this['utilisateur']->passe,
                    'dribble' => $this['utilisateur']->dribble,
                    'physique' => $this['utilisateur']->physique,
                    'role' => $this['utilisateur']->role,
                    'statut' => $this['utilisateur']->statut,
                ],
            ],
        ];
    }
}

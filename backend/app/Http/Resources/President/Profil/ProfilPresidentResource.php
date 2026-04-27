<?php

namespace App\Http\Resources\President\Profil;

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
                    'numero_joueur' => $utilisateur->numero_joueur,
                    'poste_principal' => $utilisateur->poste_principal,
                    'poste_secondaire' => $utilisateur->poste_secondaire,
                    'pied_fort' => $utilisateur->pied_fort,
                    'note_globale' => $utilisateur->note_globale,
                    'attaque' => $utilisateur->attaque,
                    'defense' => $utilisateur->defense,
                    'vitesse' => $utilisateur->vitesse,
                    'passe' => $utilisateur->passe,
                    'dribble' => $utilisateur->dribble,
                    'physique' => $utilisateur->physique,
                    'role' => $utilisateur->role,
                    'statut' => $utilisateur->statut,
                ],
            ],
        ];
    }
}

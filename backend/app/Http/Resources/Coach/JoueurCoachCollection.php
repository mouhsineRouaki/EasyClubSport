<?php

namespace App\Http\Resources\Coach;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class JoueurCoachCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'status' => true,
            'message' => 'Liste des joueurs recuperee avec succes.',
            'data' => [
                'joueurs' => $this->collection->map(function ($membreEquipe) {
                    $joueur = $membreEquipe->utilisateur;

                    return [
                        'id' => $joueur?->id,
                        'name' => $joueur?->name,
                        'prenom' => $joueur?->prenom,
                        'nom_famille' => $joueur?->nom,
                        'nom' => trim(($joueur->prenom ?? '').' '.($joueur->nom ?? '')),
                        'email' => $joueur?->email,
                        'telephone' => $joueur?->telephone,
                        'adresse' => $joueur?->adresse,
                        'photo' => $joueur?->photo,
                        'photo_url' => $joueur?->photo ? asset('storage/'.$joueur->photo) : null,
                        'statut' => $joueur?->statut,
                        'numero_joueur' => $joueur?->numero_joueur,
                        'poste_principal' => $joueur?->poste_principal,
                        'poste_secondaire' => $joueur?->poste_secondaire,
                        'pied_fort' => $joueur?->pied_fort,
                        'note_globale' => $joueur?->note_globale,
                        'attaque' => $joueur?->attaque,
                        'defense' => $joueur?->defense,
                        'vitesse' => $joueur?->vitesse,
                        'passe' => $joueur?->passe,
                        'dribble' => $joueur?->dribble,
                        'physique' => $joueur?->physique,
                        'stats' => [
                            'attaque' => $joueur?->attaque,
                            'defense' => $joueur?->defense,
                            'vitesse' => $joueur?->vitesse,
                            'passe' => $joueur?->passe,
                            'dribble' => $joueur?->dribble,
                            'physique' => $joueur?->physique,
                        ],
                        'date_affectation' => $membreEquipe->date_affectation,
                    ];
                })->values(),
            ],
        ];
    }
}

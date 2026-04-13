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
                        'nom' => trim(($joueur->prenom ?? '').' '.($joueur->nom ?? '')),
                        'email' => $joueur?->email,
                        'telephone' => $joueur?->telephone,
                        'photo_url' => $joueur?->photo ? asset('storage/'.$joueur->photo) : null,
                        'statut' => $joueur?->statut,
                        'date_affectation' => $membreEquipe->date_affectation,
                    ];
                })->values(),
            ],
        ];
    }
}
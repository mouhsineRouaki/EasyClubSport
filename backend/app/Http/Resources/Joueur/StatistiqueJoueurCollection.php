<?php

namespace App\Http\Resources\Joueur;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class StatistiqueJoueurCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'status' => true,
            'message' => 'Statistiques du joueur recuperees avec succes.',
            'data' => [
                'resume' => [
                    'matchs_total' => $this->collection->count(),
                    'buts_total' => $this->collection->sum('buts'),
                    'passes_decisives_total' => $this->collection->sum('passes_decisives'),
                    'cartons_jaunes_total' => $this->collection->sum('cartons_jaunes'),
                    'cartons_rouges_total' => $this->collection->sum('cartons_rouges'),
                    'minutes_jouees_total' => $this->collection->sum('minutes_jouees'),
                ],
                'statistiques' => $this->collection->map(function ($statistique) {
                    return [
                        'id' => $statistique->id,
                        'score_equipe' => $statistique->score_equipe,
                        'score_adversaire' => $statistique->score_adversaire,
                        'buts' => $statistique->buts,
                        'passes_decisives' => $statistique->passes_decisives,
                        'cartons_jaunes' => $statistique->cartons_jaunes,
                        'cartons_rouges' => $statistique->cartons_rouges,
                        'minutes_jouees' => $statistique->minutes_jouees,
                        'feuille_match' => $statistique->feuilleMatch ? [
                            'id' => $statistique->feuilleMatch->id,
                            'formation' => $statistique->feuilleMatch->formation,
                            'est_validee' => $statistique->feuilleMatch->est_validee,
                        ] : null,
                        'evenement' => $statistique->feuilleMatch?->evenement ? [
                            'id' => $statistique->feuilleMatch->evenement->id,
                            'titre' => $statistique->feuilleMatch->evenement->titre,
                            'date_debut' => $statistique->feuilleMatch->evenement->date_debut,
                        ] : null,
                    ];
                })->values(),
            ],
        ];
    }
}

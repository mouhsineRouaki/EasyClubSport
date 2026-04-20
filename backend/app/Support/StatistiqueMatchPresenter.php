<?php

namespace App\Support;

use App\Models\Evenement;
use App\Models\StatistiqueMatch;

class StatistiqueMatchPresenter
{
    public static function depuisEvenement(Evenement $evenement): array
    {
        $feuilleMatch = $evenement->feuilleMatch;

        if (! $feuilleMatch) {
            return [
                'resume' => [
                    'joueurs_total' => 0,
                    'buts_total' => 0,
                    'passes_decisives_total' => 0,
                    'cartons_jaunes_total' => 0,
                    'cartons_rouges_total' => 0,
                    'minutes_jouees_total' => 0,
                ],
                'joueurs' => [],
            ];
        }

        $statistiques = $feuilleMatch->statistiquesMatchs ?? collect();

        return [
            'resume' => [
                'joueurs_total' => $statistiques->count(),
                'buts_total' => $statistiques->sum('buts'),
                'passes_decisives_total' => $statistiques->sum('passes_decisives'),
                'cartons_jaunes_total' => $statistiques->sum('cartons_jaunes'),
                'cartons_rouges_total' => $statistiques->sum('cartons_rouges'),
                'minutes_jouees_total' => $statistiques->sum('minutes_jouees'),
            ],
            'joueurs' => $statistiques
                ->map(fn (StatistiqueMatch $statistique) => [
                    'id' => $statistique->id,
                    'utilisateur_id' => $statistique->utilisateur_id,
                    'joueur' => $statistique->utilisateur ? [
                        'id' => $statistique->utilisateur->id,
                        'nom' => $statistique->utilisateur->nom,
                        'prenom' => $statistique->utilisateur->prenom,
                        'name' => $statistique->utilisateur->name,
                        'email' => $statistique->utilisateur->email,
                        'photo' => $statistique->utilisateur->photo,
                        'photo_url' => $statistique->utilisateur->photo ? asset('storage/'.$statistique->utilisateur->photo) : null,
                    ] : null,
                    'score_equipe' => $statistique->score_equipe,
                    'score_adversaire' => $statistique->score_adversaire,
                    'buts' => $statistique->buts,
                    'passes_decisives' => $statistique->passes_decisives,
                    'cartons_jaunes' => $statistique->cartons_jaunes,
                    'cartons_rouges' => $statistique->cartons_rouges,
                    'minutes_jouees' => $statistique->minutes_jouees,
                ])
                ->values()
                ->all(),
        ];
    }
}

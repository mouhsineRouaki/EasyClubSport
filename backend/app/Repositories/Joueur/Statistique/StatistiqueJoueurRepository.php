<?php

namespace App\Repositories\Joueur\Statistique;

use App\Models\StatistiqueMatch;
use App\Models\User;

class StatistiqueJoueurRepository
{
    public function listerStatistiques(User $utilisateur)
    {
        return StatistiqueMatch::query()
            ->where('utilisateur_id', $utilisateur->id)
            ->with(['feuilleMatch.evenement.equipe.club'])
            ->orderByDesc('id')
            ->get();
    }
}

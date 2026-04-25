<?php

namespace App\Policies;

use App\Models\Canal;
use App\Models\Equipe;
use App\Models\User;

class CanalPolicy
{
    public function voirListe(User $utilisateur): bool
    {
        return $utilisateur->isPresident()
            || $utilisateur->isCoach()
            || $utilisateur->isJoueur();
    }

    public function creer(User $utilisateur, Equipe $equipe): bool
    {
        return $utilisateur->presidesClub($equipe->club);
    }

    public function voir(User $utilisateur, Canal $canal): bool
    {
        return $utilisateur->presidesClub($canal->equipe?->club)
            || $utilisateur->coachesEquipe($canal->equipe)
            || $utilisateur->belongsToCanal($canal);
    }

    public function gerer(User $utilisateur, Canal $canal): bool
    {
        return $this->voir($utilisateur, $canal);
    }
}

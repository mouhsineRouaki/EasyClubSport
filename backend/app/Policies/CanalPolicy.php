<?php

namespace App\Policies;

use App\Models\Canal;
use App\Models\Equipe;
use App\Models\User;

class CanalPolicy
{
    public function voirListe(User $utilisateur): bool
    {
        return $utilisateur->role === 'president';
    }

    public function creer(User $utilisateur, Equipe $equipe): bool
    {
        return $utilisateur->role === 'president'
            && (int) $equipe->club?->president_id === (int) $utilisateur->id;
    }

    public function voir(User $utilisateur, Canal $canal): bool
    {
        return $utilisateur->role === 'president'
            && (int) $canal->equipe?->club?->president_id === (int) $utilisateur->id;
    }

    public function gerer(User $utilisateur, Canal $canal): bool
    {
        return $this->voir($utilisateur, $canal);
    }
}
